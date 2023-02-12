<?php

namespace App\Model\Data;

use App\Model\Entity\RebateLevel;
use Swoft\Db\DB;
use Swoft\Redis\Redis;
use Swoft\Stdlib\Helper\JsonHelper;

/**
 * Class RebateLevelConfigData
 * @package App\Model\Data
 */
class RebateLevelConfigData
{
    const TABLE_NAME = 'rebate_level_config';
    const REDIS_KEY = 'table:rebate:level:config';

    /**
     * 获取返佣等级对应的返佣比例
     * @param int $level
     * @param string $unit
     * @param string $coin_type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function rebate_level_config_rate(int $level, string $unit, string $coin_type)
    {
        $data = self::get_config($level, $unit, $coin_type);
        if (!$data) {
            return ['rebate' => '0.005', 'rebate_mining' => '0.005'];
        }
        $res = JsonHelper::decode($data, true);
        return ['rebate' => $res['rebate'], 'rebate_mining' => $res['rebate_mining']];
    }

    /**
     * @param int $level
     * @param string $unit
     * @param string $coin_type
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_config(int $level, string $unit, string $coin_type)
    {
        $field = 'level:' . $level . '_' . $unit . '_' . $coin_type;
        $config = Redis::hGet(self::REDIS_KEY, $field);
        if (!$config) {
            self::set_rebate_level_config();
            $config = Redis::hGet(self::REDIS_KEY, $field);
        }
        return $config;
    }

    /**
     * 设置等级表到缓存
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    private static function set_rebate_level_config()
    {
        $data = DB::table(self::TABLE_NAME)->get()->toArray();
        foreach ($data as $val) {
            $field = 'level:' . $val['level'] . '_' . $val['unit'] . '_' . $val['coin_type'];
            Redis::hSet(self::REDIS_KEY, $field, JsonHelper::encode($val));
        }
        return true;
    }

}
