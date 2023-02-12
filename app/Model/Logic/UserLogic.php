<?php

namespace App\Model\Logic;

use App\Model\Data\WalletDwData;
use App\Rpc\Lib\CoinInterface;
use App\Rpc\Lib\KlineInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\DB;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * Class UserLogic
 * @package App\Model\Logic
 * @Bean()
 */
class UserLogic
{

    /**
     * @Reference(pool="system.pool")
     * @var KlineInterface
     */
    private $klineService;

    /**
     * @Inject()
     * @var WalletDwData
     */
    private $walletDwData;

    /**
     * @Reference(pool="system.pool")
     * @var CoinInterface
     */
    private $coinService;

    /**
     * 位数
     * @var int
     */
    private $scale = 8;

    /**
     * 获取单钱包资产信息
     * @param $uid
     * @param $assets_type
     * @param $all_coin
     * @return array
     */
    public function getAssets($uid, $assets_type, $all_coin)
    {
        $class = 'wallet' . ucfirst($assets_type) . 'Data';
        $user_wallets = $this->$class->get_wallets($uid);

        $wallets = [];
        foreach ($user_wallets as $value) {
            $wallets[$value['coin_type']] = $value;
        }
        //FIL待释放币
        $unreleased_coin_amount = DB::table('power_income_total')->where([['uid', '=', $uid], ['total_unreleased', '>', 0]])->sum('total_unreleased');

        $cny_usdt_price = $this->klineService->get_last_close_price('usdt', 'cny');
        // $btc_usdt_price = $this->klineService->get_last_close_price("btc", "usdt");
        $data = [
            // 'total_price_btc' => '0.00000000',
            'total_price_cny' => '0.00',
            'total_price_usdt' => '0.00000000',
            'total_free_amount_cny' => '0.00',
            'list' => [],
        ];
        $i = 0;
        foreach ($all_coin as $value) {
            //默认初始值
            $data['list'][$i] = [
                'coin_id' => $value['id'],
                'coin_type' => strtoupper($value['coin_name_en']),
                'coin_icon' => $value['coin_icon'],
                'free_amount' => '0.00000000',
                'frozen_amount' => '0.00000000',
                'pledge_coin_amount' => '0.00000000',
                'experience_coin_amount' => '0.00000000',
                'unreleased_coin_amount' => '0.00000000',
                'total_amount' => '0.00000000',
                // 'total_price_btc' => '0.00000000',
                'total_price_cny' => '0.00000000',
                'deposit_status' => $value['charge_status'],
                'withdraw_status' => $value['get_cash_status'],
                'exchange_enable' => $value['exchange_enable'],
            ];
            //如果有该币种
            $value['coin_name_en'] = strtolower($value['coin_name_en']);
            if (array_key_exists($value['coin_name_en'], $wallets)) {
                if ($value['coin_name_en'] === 'cny') {
                    $coin_usdt_price = bcdiv(1, $cny_usdt_price, $this->scale);
                } else {
                    $coin_usdt_price = ($value['coin_name_en'] === 'usdt' || $value['coin_name_en'] === 'bsf') ? 1 : $this->klineService->get_last_close_price($value['coin_name_en'], 'usdt');
                }
                $temp = $wallets[$value['coin_name_en']];
                //计算总资产
                if($value['coin_name_en'] === 'fil'){
                    $total_amount = bcadd($temp['free_coin_amount'], $temp['frozen_coin_amount'] + $temp['pledge_coin_amount'] + $temp['experience_coin_amount'] + $unreleased_coin_amount, $this->scale);
                }else{
                    $total_amount = bcadd($temp['free_coin_amount'], $temp['frozen_coin_amount'] + $temp['pledge_coin_amount'] + $temp['experience_coin_amount'], $this->scale);
                }

                $data['list'][$i]['free_amount'] = bcadd($temp['free_coin_amount'], 0, $this->scale);
                $data['list'][$i]['frozen_amount'] = bcadd($temp['frozen_coin_amount'], 0, $this->scale);
                $data['list'][$i]['pledge_coin_amount'] = bcadd($temp['pledge_coin_amount'], 0, $this->scale);
                $data['list'][$i]['experience_coin_amount'] = bcadd($temp['experience_coin_amount'], 0, $this->scale);
                $data['list'][$i]['unreleased_coin_amount'] = bcadd($unreleased_coin_amount, 0, $this->scale);

                $data['list'][$i]['total_amount'] = $total_amount;
                $total_price_usdt = bcmul($total_amount, $coin_usdt_price, $this->scale);
                //计算可用余额 cny
                $total_free_price_usdt = bcmul($temp['free_coin_amount'], $coin_usdt_price, $this->scale);
                //如果是cny 不使用换算为usdt后的金额，直接使用钱包金额
                if ($value['coin_name_en'] === 'cny') {
                    $total_free_amount_cny = bcadd($temp['free_coin_amount'], 0, $this->scale);
                    $data['list'][$i]['total_price_cny'] = bcadd($total_amount, 0 ,$this->scale);
                }else{
                    $total_free_amount_cny = bcmul($total_free_price_usdt, $cny_usdt_price, $this->scale);
                    $data['list'][$i]['total_price_cny'] = bcmul($total_price_usdt, $cny_usdt_price, $this->scale);
                }
                //计算总估值 单位为btc、usdt
                // $data['list'][$i]['total_price_btc'] = bcdiv($total_price_usdt, $btc_usdt_price, $this->scale);
                $data['list'][$i]['total_price_usdt'] = $total_price_usdt;

                // $data['total_price_btc'] = bcadd($data['total_price_btc'], $data['list'][$i]['total_price_btc'], $this->scale);
                $data['total_price_cny'] = bcadd($data['total_price_cny'], $data['list'][$i]['total_price_cny'], $this->scale);
                $data['total_price_usdt'] = bcadd($data['total_price_usdt'], $total_price_usdt, $this->scale);
                //计算总可用余额
                $data['total_free_amount_cny'] = bcadd($data['total_free_amount_cny'], $total_free_amount_cny, $this->scale);
            }
            $i++;
        }
        return $data;
    }

}
