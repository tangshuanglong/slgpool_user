<?php

namespace App\Model\Data;
use App\Lib\MyRedisHelper;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\DB;

/**
 * Class WalletAddress
 * @package App\Model\Data
 * @Bean("WalletAddress")
 */
class WalletAddress{

    /**
     * @var string
     */
    private $table_prefix = 'cold_wallet_';

    /**
     * 获取地址
     * @param $uid
     * @param string $chain_name
     * @return bool|mixed
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_address($uid, string $chain_name)
    {
        //加锁，redis分布式锁，该接口有资源争抢的情况
        $lock_key = 'user_deposit';
        $lock_res = MyRedisHelper::lock($lock_key);
        if ($lock_res === false) {
            return false;
        }
        $table_name = $this->table_prefix.$chain_name;
        $data = DB::table($table_name)->where(['uid' => $uid])->first();
        //如果没有地址，分配一个
        if (!$data) {
            $wallet_data = DB::table($table_name)->where(['uid' => null])->first();
            if (!$wallet_data) {
                return false;
            }
            $res = DB::table($table_name)->where(['id' => $wallet_data['id']])->update(['uid' => $uid]);
            if (!$res) {
                return false;
            }
            return $wallet_data['address'];
        }
        MyRedisHelper::unLock($lock_key, $lock_res);
        return $data['address'];
    }
}
