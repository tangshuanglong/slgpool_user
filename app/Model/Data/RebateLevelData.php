<?php

namespace App\Model\Data;

use App\Model\Entity\RebateLevel;
use Swoft\Db\DB;

/**
 * Class RebateLevelData
 * @package App\Model\Data
 */
class RebateLevelData
{

    const TABLE_NAME = 'rebate_level';

    /**
     * 获取用户返佣等级
     * @param int $uid
     * @param string $unit
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function user_rebate_level(int $uid, string $unit)
    {
        $invite = RebateLevel::where(['uid' => $uid, 'unit' => $unit])->first();
        $level = 1;//默认1级
        if ($invite) {
            $level = $invite->getLevel();
        }
        return $level;
    }

    /**
     * 用户邀请返佣等级
     * @param int $uid
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function user_rebate_level_get(int $uid)
    {
        $rebateLevel = DB::table('rebate_level')
            ->select('uid', 'level', 'consume_total_amount', 'unit')
            ->where(['uid' => $uid])
            ->get()
            ->toArray();
        if ($rebateLevel) {
            if(count($rebateLevel) == 1){
                $rebateLevel_fil =  [
                    'uid'                  => $uid,
                    'level'                => 1,
                    'consume_total_amount' => 0,
                    'unit'                 => 'fil',
                ];
                array_push($rebateLevel, $rebateLevel_fil);
            }
            foreach ($rebateLevel as $k => $v) {
                $rebate_level_config = DB::table('rebate_level_config')->select('unit', 'coin_type', 'rebate', 'rebate_mining')->where(['level' => $v['level'], 'unit' => $v['unit']])->get()->toArray();
                foreach ($rebate_level_config as $k1 => &$v1) {
                    $rebate_level_config[$k1]['rebate'] = round($v1['rebate'] * 100, 2);
                    $rebate_level_config[$k1]['rebate_mining'] = round($v1['rebate_mining'] * 100, 2);
                }
                $rebateLevel[$k]['rebate_level_config'] = $rebate_level_config;
            }
        }
        if (!$rebateLevel) {
            $rebateLevel = [
                [
                    'uid'                  => $uid,
                    'level'                => 1,
                    'consume_total_amount' => 0,
                    'unit'                 => 'cny',
                ],
                [
                    'uid'                  => $uid,
                    'level'                => 1,
                    'consume_total_amount' => 0,
                    'unit'                 => 'fil',
                ]
            ];
            foreach($rebateLevel as $k => $v){
                    $rebate_level_config = DB::table('rebate_level_config')->select('unit', 'coin_type', 'rebate', 'rebate_mining')->where(['level' => $v['level'], 'unit' => $v['unit']])->get()->toArray();
                    foreach ($rebate_level_config as $k1 => &$v1) {
                        $rebate_level_config[$k1]['rebate'] = round($v1['rebate'] * 100, 2);
                        $rebate_level_config[$k1]['rebate_mining'] = round($v1['rebate_mining'] * 100, 2);
                    }
                    $rebateLevel[$k]['rebate_level_config'] = $rebate_level_config;
            }
        }
        //返佣比例
        return $rebateLevel;
    }

}
