<?php

namespace App\Rpc\Service;

use App\Model\Data\WalletLoanData;
use App\Rpc\Lib\WalletLoanInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class WalletLoanService
 * @package App\Rpc\Service
 * @Service()
 */
class WalletLoanService implements WalletLoanInterface{

    /**
     * @Inject()
     * @var WalletLoanData
     */
    private $walletLoanData;

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
        return $this->walletLoanData->append_wallet_frozen($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletLoanData->return_wallet_frozen($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletLoanData->append_wallet_free($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletLoanData->deduct_wallet_free($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletLoanData->deduct_wallet_frozen($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletLoanData->deduct_wallet_pledge($uid, $amount, $coin_id, $trade_type);
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
        return $this->walletLoanData->append_wallet_pledge($uid, $amount, $coin_id, $trade_type);
    }


}
