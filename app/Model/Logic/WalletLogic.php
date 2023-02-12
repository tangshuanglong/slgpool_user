<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Model\Logic;

use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Lib\MyRabbitMq;
use App\Lib\MyRedisHelper;
use App\Model\Data\TradeTypeData;
use App\Model\Data\WalletData;
use App\Model\Entity\UserAmountChangeLogMining;
use App\Model\Entity\UserAmountChangeLogDw;
use Swoft\Apollo\Config;
use Swoft\Apollo\Exception\ApolloException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\BeanFactory;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;

/**
 * Class ApolloLogic
 *
 * @since 2.0
 *
 */
abstract class WalletLogic
{
    /**
     * 表名
     * @var string
     */
    protected $table_name;

    /**
     * 钱包日志路径
     * @var
     */
    protected $wallet_log;

    /**
     * 钱包类型
     * @var
     */
    protected $wallet_type;

    /**
     * @Inject()
     * @var WalletData
     */
    private $walletData;

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
     * WalletLogic constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (empty($this->wallet_type)) {
            throw new \Exception('wallet_type不能为空');
        }
        if (empty($this->table_name)) {
            throw new \Exception('table_name不能为空');
        }
        if (empty($this->wallet_log)) {
            throw new \Exception('wallet_log不能为空');
        }
        $this->wallet_type = strtolower($this->wallet_type);
        /**@var MyCommon $myCommon */
        $this->myCommon = BeanFactory::getBean('MyCommon');
        /**@var MyRabbitMq $myRabbitMq */
        $this->myRabbitMq = BeanFactory::getBean('MyRabbitMq');
    }


    /**
     * 获取用户所有钱包
     * @param int $uid
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallets(int $uid)
    {
        return DB::table($this->table_name)->where(['uid' => $uid])->get()->toArray();
    }

    /**
     * 获取用户钱包
     * @param $uid
     * @param $coin_id
     * @return object|\Swoft\Db\Eloquent\Model|\Swoft\Db\Query\Builder|null
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet($uid, $coin_id)
    {
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
        $data = DB::table($this->table_name)->select('frozen_coin_amount')->where(['uid' => $uid, 'coin_id' => $coin_id])->first();
        if ($data) {
            return $data['frozen_coin_amount'];
        }
        return 0;
    }

    /**
     * 获取用户抵押余额
     * @param $uid
     * @param $coin_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet_pledge($uid, $coin_id)
    {
        $data = DB::table($this->table_name)->select('pledge_coin_amount')->where(['uid' => $uid, 'coin_id' => $coin_id])->first();
        if ($data) {
            return $data['pledge_coin_amount'];
        }
        return 0;
    }

    /**
     * 获取用户体验余额
     * @param $uid
     * @param $coin_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_wallet_experience($uid, $coin_id)
    {
        $data = DB::table($this->table_name)->select('experience_coin_amount')->where(['uid' => $uid, 'coin_id' => $coin_id])->first();
        if ($data) {
            return $data['experience_coin_amount'];
        }
        return 0;
    }

    /**
     * 扣除可用金额，追加冻结金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool|array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_frozen($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
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
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
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
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
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
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
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
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        $user_frozen = $this->get_wallet_frozen($uid, $coin_id);
        if ($user_frozen < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
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
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
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
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        $user_wallet = $this->get_wallet($uid, $coin_id);
        if ($user_wallet['pledge_coin_amount'] < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }

    /**
     * 扣除可用金额，追加到质押金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool|array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function deduct_wallet_free_to_pledge($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        $user_free = $this->get_wallet_free($uid, $coin_id);
        if ($user_free < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }

    /**
     * 扣除抵押金额,返还可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function return_wallet_pledge($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        $user_pledge = $this->get_wallet_pledge($uid, $coin_id);
        if ($user_pledge < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }

    /**
     * 直接追加体验金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function append_wallet_experience($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }

    /**
     * 扣除体验金额,返还可用金额
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param string $trade_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function return_wallet_experience($uid, $amount, $coin_id, string $trade_type)
    {
        $lock_res = MyRedisHelper::lock(config('redis_key.user_' . $this->wallet_type . '_wallet_lock') . $uid . '_' . $coin_id);
        if ($lock_res === false) {
            MyCommon::write_log('add lock error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        $user_amount = $this->get_wallet_experience($uid, $coin_id);
        if ($user_amount < $amount) {
            return false;
        }
        return $this->push_queue($uid, $amount, $coin_id, $trade_type, __FUNCTION__, $lock_res);
    }

    /**
     * 插入队列
     * @param $uid
     * @param $amount
     * @param $coin_id
     * @param $trade_type
     * @param $method
     * @param $lock_token
     * @return array|bool
     * @throws \Swoft\Db\Exception\DbException
     */
    private function push_queue($uid, $amount, $coin_id, $trade_type, $method, $lock_token)
    {
        if (empty($coin_id)) {
            throw new DbException('coin_id not empty');
        }
        $trade_type_info = TradeTypeData::get_trade_type_info($trade_type, $this->wallet_type);
        if (empty($trade_type_info)) {
            throw new DbException('trade type not exists');
        }
        // 插入余额变化记录中间表
        $date = date("Y-m-d H:i:s");
        $log_data = [
            'uid'           => $uid,
            'coin_id'       => $coin_id,
            'amount'        => $amount,
            'trade_type_id' => $trade_type_info['id'],
            'method'        => $method,
            'updated_at'    => $date,
            'created_at'    => $date,
        ];
        $log_id = DB::table('user_amount_change_log_' . $this->wallet_type)->insertGetId($log_data);
        if (!$log_id) {
            MyCommon::write_log('insert user amount change log error uid=' . $uid, $this->wallet_log);
            throw new DbException('wallet error');
        }
        $data = [
            'lock_token' => $lock_token,
            'log_id'     => $log_id,
        ];
        $key = config('redis_key.user_' . $this->wallet_type . '_wallet_queue') . '_' . $this->myCommon->get_hash_id($uid, config($this->wallet_type . '_wallet_worker_num'));
        $res = $this->myRabbitMq->push($key, $data);
        if (!$res) {
            $this->walletData->set_user_wallet_abnormal($uid);
            MyCommon::write_log('push rabbitMq queue error uid=' . $uid, $this->wallet_log);
        }
        return true;
    }

}
