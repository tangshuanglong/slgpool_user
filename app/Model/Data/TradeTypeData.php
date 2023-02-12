<?php

namespace App\Model\Data;

use Swoft\Db\DB;

/**
 * Class TradeTypeData
 * @package App\Model\Data
 */
class TradeTypeData{

    /**
     * 获取交易类型信息
     * @param string $trade_name_en
     * @param string $type
     * @return object|\Swoft\Db\Eloquent\Model|\Swoft\Db\Query\Builder|null
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_trade_type_info(string $trade_name_en, $type = 'dw')
    {
        return DB::table('trade_type_'.$type)->where(['type_name_en' => $trade_name_en])->first();
    }
}
