<?php

namespace App\Model\Data;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyRabbitMq;
use App\Lib\MyRedisHelper;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\BeanFactory;
use Swoft\Db\DB;

/**
 * Class WalletData
 * @package Swoft\Devtool\Model\Data
 * @Bean("WalletLoanData")
 */
class WalletLoanData{

    /**
     * @var string
     */
    private $table_name = 'user_wallet_loan';

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @Inject()
     * @var MyRabbitMq
     */
    private $myRabbitMq;

    /**
     * @param int $uid
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallets(int $uid)
    {
        return DB::table($this->table_name)->where(['uid' => $uid])->get()->toArray();
    }

    /**
     * 获取用户钱包信息
     * @param $uid
     * @param $coin_id
     * @return object|\Swoft\Db\Eloquent\Model|\Swoft\Db\Query\Builder|null
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet($uid, $coin_id)
    {
        //$table_name = $this->table_prefix.$this->myCommon->get_hash_id($uid, config('wallet_table_num'));
        return DB::table($this->table_name)->where(['uid' => $uid, 'coin_id' => $coin_id])->first();
    }

    /**
     * 获取用户可用余额
     * @param $uid
     * @param $coin_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet_free($uid, $coin_id)
    {
        //$table_name = $this->table_prefix.$this->myCommon->get_hash_id($uid, config('wallet_table_num'));
        $data = DB::table($this->table_name)->select('free_coin_amount')->where(['uid' => $uid, 'coin_id' => $coin_id])->first();
        if ($data) {
            return $data['free_coin_amount'];
        }
        return 0;
    }

    /**
     * 获取用户冻结余额
     * @param $uid
     * @param $coin_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet_frozen($uid, $coin_id)
    {
        //$table_name = $this->table_prefix.$this->myCommon->get_hash_id($uid, config('wallet_table_num'));
        $data = DB::table($this->table_name)->select('frozen_coin_amount')->where(['uid' => $uid, 'coin_id' => $coin_id])->first();
        if ($data) {
            return $data['frozen_coin_amount'];
        }
        return 0;
    }

    /**
     * 插入队列
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param $trade_type
     * @param $method
     * @param $lock_token
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    private function push_queue($uid, $amount, $coin_id, $trade_type, $method, $lock_token)
    {
        $trade_type_info = TradeTypeData::get_trade_type_info($trade_type, 'loan');
        if (empty($trade_type_info)) {
            return 500;
        }
        $data = [
            'method' => $method,
            'lock_token' => $lock_token,
            'unique_id' => $this->myCommon->get_unique_id($uid.'_'.$coin_id),
            'data' => [
                'uid' => $uid,
                'amount' => $amount,
                'coin_id' => $coin_id,
                'trade_type_id' => $trade_type_info['id'],
            ],

        ];
        $key = config('redis_key.user_loan_wallet_queue').'_'.$this->myCommon->get_hash_id($uid, config('loan_wallet_worker_num'));
        return $this->myRabbitMq->push($key, $data);
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
    public function append_wallet_frozen($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        $user_free = $this->get_wallet_free($uid, $coin_id);
        if ($user_free < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
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
    public function return_wallet_frozen($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        $user_frozen = $this->get_wallet_frozen($uid, $coin_id);
        if ($user_frozen < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
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
    public function append_wallet_free($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
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
    public function deduct_wallet_free($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        $user_free = $this->get_wallet_free($uid, $coin_id);
        if ($user_free < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
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
    public function deduct_wallet_frozen($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        $user_frozen = $this->get_wallet_frozen($uid, $coin_id);
        if ($user_frozen < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }

    /**
     * 借贷账户划转到充提账户
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function loan_to_dw($uid, $amount, $coin_id)
    {
        //扣除loan可用金额
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

    /**
     * 直接追加质押金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_pledge($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
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
    public function deduct_wallet_pledge($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_loan_wallet_lock').$uid.'_'.$coin_id);
        if ($lock_res === false) {
            return 500;
        }
        $user_wallet = $this->get_wallet($uid, $coin_id);
        if ($user_wallet['pledge_coin_amount'] < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }









}
