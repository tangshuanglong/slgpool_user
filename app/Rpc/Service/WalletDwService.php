<?php

namespace App\Rpc\Service;

use App\Model\Data\WalletData;
use App\Model\Data\WalletDwData;
use App\Rpc\Lib\WalletDwInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class WalletLoanService
 * @package App\Rpc\Service
 * @Service()
 */
class WalletDwService implements WalletDwInterface
{

    /**
     * @Inject()
     * @var WalletDwData
     */
    private $walletDwData;

    /**
     * @Inject()
     * @var WalletData
     */
    private $walletData;

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
        return $this->walletDwData->append_wallet_frozen($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletDwData->return_wallet_frozen($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletDwData->append_wallet_free($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletDwData->deduct_wallet_free($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletDwData->deduct_wallet_frozen($uid, $amount, $coin_id, $trade_type);
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
     * 币币账户划转到挖矿账户
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function dw_to_mining(int $uid, string $amount, int $coin_id)
    {
        return $this->walletDwData->dw_to_mining($uid, $amount, $coin_id);
    }

    /**
     * 获取用户可用余额
     * @param int $uid
     * @param int $coin_id
     * @return mixed
     */
    public function get_wallet_free(int $uid, int $coin_id)
    {
        return $this->walletDwData->get_wallet_free($uid, $coin_id);
    }

    /**
     * 直接扣除质押金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function deduct_wallet_pledge(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletDwData->deduct_wallet_pledge($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 直接追加质押金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function append_wallet_pledge(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletDwData->append_wallet_pledge($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 扣除可用金额，追加到抵押金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function deduct_wallet_free_to_pledge(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletDwData->deduct_wallet_free_to_pledge($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 扣除抵押金额，返回到可用金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function return_wallet_pledge(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletDwData->return_wallet_pledge($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 获取用户体验余额
     * @param int $uid
     * @param int $coin_id
     * @return mixed
     */
    public function get_wallet_experience(int $uid, int $coin_id)
    {
        return $this->walletDwData->get_wallet_experience($uid, $coin_id);
    }

    /**
     * 直接追加体验金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function append_wallet_experience(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletDwData->append_wallet_experience($uid, $amount, $coin_id, $trade_type);
    }

    /**
     * 扣除体验金额，返回到可用金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function return_wallet_experience(int $uid, string $amount, int $coin_id, string $trade_type)
    {
        return $this->walletDwData->return_wallet_experience($uid, $amount, $coin_id, $trade_type);
    }

}
