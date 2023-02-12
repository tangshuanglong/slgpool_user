<?php

namespace App\Model\Data;

use App\lib\MyCode;
use App\Model\Logic\WalletLogic;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
/**
 * Class WalletData
 * @package Swoft\Devtool\Model\Data
 * @Bean("WalletMiningData")
 */
class WalletMiningData extends WalletLogic {

    /**
     * @var string
     */
    protected $table_name = 'user_wallet_mining';

    protected $wallet_log = '/logs/mining_wallet_error';

    protected $wallet_type = 'mining';

    /**
     * 借贷账户划转到充提账户
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function mining_to_dw($uid, $amount, $coin_id)
    {
        //扣除mining可用金额
        $res = $this->deduct_wallet_free($uid, $amount, $coin_id, __FUNCTION__);
        if ($res !== true) {
            return $res;
        }
        //添加dw可用金额
        /**@var WalletDwData $walletDwData */
        $walletDwData = BeanFactory::getBean("WalletDwData");
        $dw_res = $walletDwData->append_wallet_free($uid, $amount, $coin_id, __FUNCTION__);
        if ($dw_res !== true) {
            return $dw_res;
        }
        return true;
    }









}
