<?php

namespace App\Model\Data;

use App\lib\MyCode;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Redis\Redis;

/**
 * Class WalletData
 * @package Swoft\Devtool\Model\Data
 * @Bean("WalletData")
 */
class WalletData{


    /**
     * 用户钱包余额是否异常 true异常 false 正常
     * @param int $uid
     * @return bool
     */
    public function user_wallet_abnormal(int $uid)
    {
        $field = 'abnormal_'.$uid;
        $res = Redis::hGet(config('redis_key.user_wallet_abnormal'), $field);
        if ($res) {
            return true;
        }
        return false;
    }

    /**
     * 设置用户钱包异常状态
     * @param int $uid
     * @return int
     */
    public function set_user_wallet_abnormal(int $uid)
    {
        $field = 'abnormal_'.$uid;
        return Redis::hSet(config('redis_key.user_wallet_abnormal'), $field, $uid);
    }

    /**
     * 删除用户钱包异常状态
     * @param int $uid
     * @return string
     */
    public function del_user_wallet_abnormal(int $uid)
    {
        $field = 'abnormal_'.$uid;
        return Redis::hDel(config('redis_key.user_wallet_abnormal'), $field);
    }



}
