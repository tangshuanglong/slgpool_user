<?php

namespace App\Model\Data;

use App\Lib\MyCommon;
use App\Model\Entity\InviteLog;
use App\Model\Data\RewardLogData;
use Swoft\Bean\BeanFactory;
use Swoft\Db\DB;

/**
 * Class RebateLevelData
 * @package App\Model\Data
 */
class InviteLogData
{
    /**
     * 获取推荐总数
     * @param int $uid
     * @return int
     */
    public static function get_total_people(int $uid)
    {
        return InviteLog::where(['uid' => $uid])->count();
    }

    /**
     * 获取推荐记录
     * @param int $invited_uid
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_invite(int $invited_uid)
    {
        return DB::table('invite_log')->where(['invited_uid' => $invited_uid, 'status' => 1])->firstArray();
    }

    /**
     * 购买返佣
     * @param int $invited_uid
     * @param string $amount
     * @param string $unit
     * @param string $coin_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function rebate(int $invited_uid, string $amount, $unit, $coin_type)
    {
        // 一级(拿5%)
        $invite_data_one = self::get_invite($invited_uid);
        if (!empty($invite_data_one)) {
            if ($unit == 'cny') {//返usdt
                $reward_log_coin_type = 'usdt';
                $usdt_cny = CoinData::get_coin_last_price('usdt', 'cny');
                $amount = bcdiv($amount, $usdt_cny, 8);
            } elseif ($unit == 'fil') {//返fil
                $reward_log_coin_type = 'fil';
            } else {
                $reward_log_coin_type = 'usdt';
                $usdt_cny = CoinData::get_coin_last_price('usdt', 'cny');
                $amount = bcdiv($amount, $usdt_cny, 8);
            }
            $invite_commission = bcmul($amount, 0.05, 8);
            //添加返佣记录表
            $insert_reward_res = RewardLogData::insertData(
                $invite_data_one['uid'], $invite_data_one['invited_uid'], $invite_data_one['invited_account'], $reward_log_coin_type, $invite_commission, $amount);
            if (!$insert_reward_res) {
                MyCommon::write_log('insert reward log error', config('log_path'));
                return false;
            }
            /**@var WalletDwData $walletDwData */
            $walletDwData = BeanFactory::getBean('WalletDwData');
            $coin_info = CoinData::get_coin_info_by_coin_type($reward_log_coin_type);
            //给邀请人追加佣金
            $wallet_res = $walletDwData->append_wallet_free($invite_data_one['uid'], $invite_commission, $coin_info['id'], 'recommend_reward');
            if ($wallet_res === false) {
                MyCommon::write_log('insert append_wallet_free queue error', config('log_path'));
                return false;
            }
            // 二级(拿2.5%)
            $invite_data_two = self::get_invite($invite_data_one['uid']);
            if (!empty($invite_data_two)) {
                $invite_commission = bcmul($amount, 0.025, 8);
                //添加返佣记录表
                $insert_reward_res = RewardLogData::insertData(
                    $invite_data_two['uid'], $invite_data_two['invited_uid'], $invite_data_two['invited_account'], $reward_log_coin_type, $invite_commission, $amount);
                if (!$insert_reward_res) {
                    MyCommon::write_log('insert reward log error', config('log_path'));
                    return false;
                }
                //给邀请人追加佣金
                $wallet_res = $walletDwData->append_wallet_free($invite_data_two['uid'], $invite_commission, $coin_info['id'], 'recommend_reward');
                if ($wallet_res === false) {
                    MyCommon::write_log('insert append_wallet_free queue error', config('log_path'));
                    return false;
                }
                // 三级(拿1.25%)
                $invite_data_three = self::get_invite($invite_data_two['uid']);
                if (!empty($invite_data_three)) {
                    $invite_commission = bcmul($amount, 0.0125, 8);
                    //添加返佣记录表
                    $insert_reward_res = RewardLogData::insertData(
                        $invite_data_three['uid'], $invite_data_three['invited_uid'], $invite_data_three['invited_account'], $reward_log_coin_type, $invite_commission, $amount);
                    if (!$insert_reward_res) {
                        MyCommon::write_log('insert reward log error', config('log_path'));
                        return false;
                    }
                    //给邀请人追加佣金
                    $wallet_res = $walletDwData->append_wallet_free($invite_data_three['uid'], $invite_commission, $coin_info['id'], 'recommend_reward');
                    if ($wallet_res === false) {
                        MyCommon::write_log('insert append_wallet_free queue error', config('log_path'));
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * 挖矿返佣
     * @param int $order_id
     * @param int $invited_uid
     * @param string $amount
     * @param string $unit
     * @param string $coin_type 币种类型
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function rebate_for_mining(int $order_id, int $invited_uid, string $amount, string $unit, string $coin_type)
    {
        //如果有推荐人，添加邀请佣金记录
        $invite_data = self::get_invite($invited_uid);
        if (!empty($invite_data)) {
            $level = RebateLevelData::user_rebate_level($invite_data['uid'], $unit);
            // 计算邀请人得到的佣金
            $RebateLevelConfigData = RebateLevelConfigData::rebate_level_config_rate($level, $unit, $coin_type);
            $rebate_rate = $RebateLevelConfigData['rebate_mining'];
            $invite_commission = bcmul($amount, $rebate_rate, 8);
            //添加返佣记录表
            $insert_reward_res = RewardLogData::insertData(
                $invite_data['uid'], $invite_data['invited_uid'], $invite_data['invited_account'], $coin_type, $invite_commission, $amount);
            if (!$insert_reward_res) {
                MyCommon::write_log('insert reward log error', config('log_path'));
                return false;
            }
            //给邀请人追加佣金
            /**@var WalletDwData $walletDwData */
            $walletDwData = BeanFactory::getBean('WalletDwData');
            $coin_info = CoinData::get_coin_info_by_coin_type($coin_type);
            $wallet_res = $walletDwData->append_wallet_free($invite_data['uid'], $invite_commission, $coin_info['id'], 'recommend_reward');
            if ($wallet_res === false) {
                MyCommon::write_log('insert append_wallet_free queue error', config('log_path'));
                return false;
            }
        }
        return true;
    }

    /**
     * 购买返佣（旧）
     * @param int $invited_uid
     * @param string $amount
     * @param string $unit
     * @param string $coin_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function rebate_old(int $invited_uid, string $amount, $unit, $coin_type)
    {
        //如果有推荐人，添加邀请佣金记录
        $invite_data = self::get_invite($invited_uid);
        if (!empty($invite_data)) {
            if ($unit == 'cny') {//返usdt
                $reward_log_coin_type = 'usdt';
                $usdt_cny = CoinData::get_coin_last_price('usdt', 'cny');
                $amount = bcdiv($amount, $usdt_cny, 8);
            } elseif ($unit == 'fil') {//返fil
                $reward_log_coin_type = 'fil';
            } else {
                $reward_log_coin_type = 'usdt';
                $usdt_cny = CoinData::get_coin_last_price('usdt', 'cny');
                $amount = bcdiv($amount, $usdt_cny, 8);
            }
            $level = RebateLevelData::user_rebate_level($invite_data['uid'], $unit);
            // 计算邀请人得到的佣金
            $RebateLevelConfigData = RebateLevelConfigData::rebate_level_config_rate($level, $unit, $coin_type);
            $rebate_rate = $RebateLevelConfigData['rebate'];
            $invite_commission = bcmul($amount, $rebate_rate, 8);
            $reward_log = [
                'to_uid'       => $invite_data['uid'],
                'from_uid'     => $invited_uid,
                'from_account' => $invite_data['invited_account'],
                'coin_type'    => $reward_log_coin_type,
                'amount'       => $invite_commission,
                'buy_amount'   => $amount
            ];
            $insert_reward_res = RewardLog::insert($reward_log);
            if (!$insert_reward_res) {
                MyCommon::write_log('insert reward log error', config('log_path'));
                return false;
            }
            //给邀请人追加佣金
            /**@var WalletDwData $walletDwData */
            $walletDwData = BeanFactory::getBean('WalletDwData');
            $coin_info = CoinData::get_coin_info_by_coin_type($reward_log_coin_type);
            $wallet_res = $walletDwData->append_wallet_free($invite_data['uid'], $invite_commission, $coin_info['id'], 'recommend_reward');
            if ($wallet_res === false) {
                MyCommon::write_log('insert append_wallet_free queue error', config('log_path'));
                return false;
            }
        }
        return true;
    }

}
