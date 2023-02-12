<?php

namespace App\Rpc\Lib;

/**
 * Interface WalletDwInterface
 */
interface WalletDwInterface
{

    /**
     * 扣除可用金额，追加冻结金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_frozen(int $uid, string $amount, int $coin_id, string $trade_type);

    /**
     * 扣除冻结金额,返还可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function return_wallet_frozen(int $uid, string $amount, int $coin_id, string $trade_type);

    /**
     * 直接追加可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_free(int $uid, string $amount, int $coin_id, string $trade_type);

    /**
     * 直接扣除可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deduct_wallet_free(int $uid, string $amount, int $coin_id, string $trade_type);

    /**
     * 直接扣除冻结金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deduct_wallet_frozen(int $uid, string $amount, int $coin_id, string $trade_type);

    /**
     * 币币账户划转到挖矿账户
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function dw_to_mining(int $uid, string $amount, int $coin_id);

    /**
     * 扣除可用金额，追加到抵押金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function deduct_wallet_free_to_pledge(int $uid, string $amount, int $coin_id, string $trade_type);

    /**
     * 扣除抵押金额，返回到可用金额
     * @param int $uid
     * @param string $amount
     * @param int $coin_id
     * @param string $trade_type
     * @return bool
     */
    public function return_wallet_pledge(int $uid, string $amount, int $coin_id, string $trade_type);

}
