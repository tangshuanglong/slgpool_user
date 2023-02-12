<?php

namespace App\Model\Data;

use App\lib\MyCode;
use App\Model\Logic\WalletLogic;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoole\Exception;

/**
 * Class WalletDwData
 * @package Swoft\Devtool\Model\Data
 * @Bean("WalletDwData")
 */
class WalletDwData extends WalletLogic
{

    /**
     * @var string
     */
    protected $table_name = 'user_wallet_dw';

    protected $wallet_log = '/logs/dw_wallet_error';

    protected $wallet_type = 'dw';

    /**
     * 充提账户划转到挖矿账户
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function dw_to_mining($uid, $amount, $coin_id)
    {
        //扣除dw可用金额
        $res = $this->deduct_wallet_free($uid, $amount, $coin_id, __FUNCTION__);
        if ($res !== true) {
            return false;
        }
        //添加mining可用金额
        /** @var WalletMiningData $walletMiningData * */
        $walletMiningData = BeanFactory::getBean("WalletMiningData");
        $loan_res = $walletMiningData->append_wallet_free($uid, $amount, $coin_id, __FUNCTION__);
        if ($loan_res !== true) {
            return $loan_res;
        }
    }


}
