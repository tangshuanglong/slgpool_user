<?php

namespace App\Rpc\Service;

use App\Model\Data\WalletData;
use App\Model\Data\WalletMiningData;
use App\Rpc\Lib\WalletMiningInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class WalletMiningService
 * @package App\Rpc\Service
 * @Service()
 */
class WalletMiningService implements WalletMiningInterface{

    /**
     * @Inject()
     * @var WalletMiningData
     */
    private $walletMiningData;

    /**
     * @Inject()
     * @var WalletData
     */
    private $walletData;

    /**
     * 获取用户可用余额
     * @param int $uid
     * @param int $coin_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet_free(int $uid, int $coin_id)
    {
        $free = $this->walletMiningData->get_wallet_free($uid, $coin_id);
        return bcadd($free, 0, 8);
    }

    /**
     * 扣除可用金额，追加冻结金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_frozen(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->append_wallet_frozen($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 扣除冻结金额,返还可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function return_wallet_frozen(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->return_wallet_frozen($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 直接追加可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_free(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->append_wallet_free($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 直接扣除可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deduct_wallet_free(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->deduct_wallet_free($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 直接扣除冻结金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deduct_wallet_frozen(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->deduct_wallet_frozen($uid, $amount, $coin_id, $trade_type);
    }


    /**
     * 直接扣除质押金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deduct_wallet_pledge(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->deduct_wallet_pledge($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 直接追加质押金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_pledge(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletMiningData->append_wallet_pledge($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 用户钱包余额是否异常 true异常 false 正常
     * @param int $uid
     * @return bool
     */
    public function user_wallet_abnormal(int $uid)
    {
        return $this->walletData->user_wallet_abnormal($uid);
    }

    /**
     * 挖矿账户划转到币币账户
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function mining_to_dw(int $uid, string $amount, int $coin_id)
    {
        return $this->walletMiningData->mining_to_dw($uid, $amount, $coin_id);
    }


}
