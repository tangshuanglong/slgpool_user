<?php

namespace App\Model\Data;

use App\Model\Entity\RewardLog;
use Swoft\Db\DB;

/**
 * Class RewardLogData
 * @package App\Model\Data
 */
class RewardLogData
{

    /**
     * 添加数据
     * @return boolean
     * */
    public static function insertData($to_uid, $from_uid, $from_account, $coin_type, $amount, $buy_amount): bool
    {
        $reward_log = [
            'to_uid'       => $to_uid,
            'from_uid'     => $from_uid,
            'from_account' => $from_account,
            'coin_type'    => $coin_type,
            'amount'       => $amount,
            'buy_amount'   => $buy_amount,
            'status'       => 1
        ];
        $insert_reward_res = RewardLog::insert($reward_log);
        if (!$insert_reward_res) {
            return false;
        }
        return true;
    }

}
