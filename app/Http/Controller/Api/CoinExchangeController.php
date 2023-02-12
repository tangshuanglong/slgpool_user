<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Model\Data\ConfigData;
use App\Model\Data\PaginationData;
use App\Model\Data\TradeTypeData;
use App\Model\Data\WalletData;
use App\Model\Entity\CoinExchange;
use App\Rpc\Lib\CoinInterface;
use App\Rpc\Lib\WalletDwInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\BeanFactory;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Redis\Redis;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class CoinExchangeController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/coin_exchange")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class)
 * })
 */
class CoinExchangeController
{
    /**
     * @Reference(pool="system.pool")
     * @var CoinInterface
     */
    private $coinService;

    /**
     * @Reference(pool="user.pool")
     * @var WalletDwInterface
     */
    private $walletDwService;

    /**
     * @Inject()
     * @var WalletData
     */
    private $walletData;

    /**
     * 闪兑交易【暂时没用】
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function exchange(Request $request)
    {
        $params = $request->params;
        //验证参数
        validate($params, 'CoinExchangeValidator', ['coin_type', 'price_type', 'exchange_method', 'number']);
        $coinType = strtolower($params['coin_type']);
        $priceType = strtolower($params['price_type']);
        $exchangeMethod = strtolower($params['exchange_method']);
        $number = $params['number'];
        $userId = $request->uid;

        // 获取币种信息
        $coin = $this->coinService->get_coin_info($coinType);
        $priceId = $this->coinService->get_coin_id($priceType);
        if (!$coin || !$priceId) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '币种类型不存在');
        }

        // 根据是否开启交易判断
        if ($coin['exchange_enable'] === 2) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, $coinType . '币种类型不允许交易');
        }

        // 可交易的priceList 存储在config表中
        [$priceTypeListJson] = ConfigData::getConfigValue('system', 'price_type_list');
        $priceTypeList = json_decode($priceTypeListJson, true);
        if (!in_array($priceType, $priceTypeList)) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, $priceType . '币种类型不允许交易');
        }

        [$coinleft,$coinright] = get_left_right_coin($coinType,$priceType);

        // 获取当前价格
        $price = $this->coinService->get_coin_last_price($coinleft, $coinright);
        if (!$price) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '币种价格获取错误');
        }

        // 获取卖出差价，得到最终的price价格
        [$sellDiffPrice] = ConfigData::getConfigValue('system', 'sell_diff_price');
        if ($sellDiffPrice) {
            $price = bcmul($price, bcadd(1, $sellDiffPrice, 8), 8);
        }

        // 手续费比例获取并计算
        [$serviceChargeRatio] = ConfigData::getConfigValue('system', 'service_charge_ratio');
        $originAmount = $amount = bcmul($price, $number, 8); // 原金额
        $serviceCharge = bcmul($originAmount, $serviceChargeRatio, 8); // 手续费
        if ($serviceChargeRatio) {
            $amount = bcsub($originAmount, $serviceCharge, 8); // 扣除手续费后的金额
        }

        if ($this->walletData->user_wallet_abnormal($userId)) {
            return MyQuit::returnMessage(MyCode::WALLET_ABNORMAL, '钱包金额异常，请联系客服处理');
        }

        // 用户钱包扣款
        DB::beginTransaction();
        try {
            $coinExchange = CoinExchange::new();
            $coinExchange->setUid($userId);
            $coinExchange->setCoinType($coinType);
            $coinExchange->setNumber($number);
            $coinExchange->setPriceType($priceType);
            $coinExchange->setExchangeMethod($exchangeMethod);
            $coinExchange->setPrice($price);
            $coinExchange->setOriginAmount($originAmount);
            $coinExchange->setServiceCharge($serviceCharge);
            $coinExchange->setAmount($amount);
            $coinExchange->save();

            // 用户钱包扣款
            $deductRes = $this->walletDwService->deduct_wallet_free($userId, $number, $coin['id'], 'exchange_' . $exchangeMethod);
            if (!$deductRes) {
                MyCommon::write_log('闪兑时用户账户余额不足');
                throw new DbException('账户余额不足');
            }

            // 用户钱包充值
            $appendRes = $this->walletDwService->append_wallet_free($userId, $amount, $priceId, 'exchange_' . $exchangeMethod);
            if (!$appendRes) {
                MyCommon::write_log('闪兑时用户钱包充值错误');
                throw new DbException('闪兑时用户钱包充值错误');
            }
        } catch (DbException $e) {
            DB::rollBack();
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, $e->getMessage());
        }

        DB::commit();
        return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
    }

    /**
     * 闪兑交易记录【暂时没用】
     * @param Request $request
     * @return array
     * @throws DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function coin_exchange_list(Request $request)
    {
        $params = $request->params;
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;
        $userId = $request->uid;
        $coinType = $params['coin_type'] ?? '';
        $priceType = $params['price_type'] ?? '';
        $exchangeMethod = $params['exchange_method'] ?? '';
        $startTime = $params['start_time'] ?? '';
        $endTime = $params['end_time'] ?? '';
        $where = [];

        if ($userId) {
            $where[] = ['uid', '=', $userId];
        }
        if ($coinType) {
            $where[] = ['coin_type', '=', $coinType];
        }
        if ($priceType) {
            $where[] = ['price_type', '=', $priceType];
        }
        if ($exchangeMethod) {
            $where[] = ['exchange_method', '=', $exchangeMethod];
        }
        if ($startTime) {
            $where[] = ['created_at', '>', $startTime];
        }
        if ($endTime) {
            $where[] = ['created_at', '<', $endTime];
        }

        $data = PaginationData::table('coin_exchange')->where($where)->forPage($page, $size)->get();

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }
}
