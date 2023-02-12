<?php declare(strict_types=1);


namespace App\Model\Data;

use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Redis\Redis;
use Swoft\Stdlib\Helper\JsonHelper;

class CoinData
{

    /**
     * 获取所有可用的币种名称列表
     * @param string $type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function coin_names(string $type)
    {
        $where[] = ['show_flag', '=', 1];
        switch ($type) {
            case 'transfer':
                $where[] = ['transfer_enable', '=', 1];
                break;
            case 'withdraw':
                $where[] = ['get_cash_status', '=', 1];
                break;
            case 'deposit':
                $where[] = ['charge_status', '=', 1];
                break;
            case 'exchange':
                $where[] = ['exchange_enable', '=', 1];
                break;
        }
        return DB::table('coin')->select('id', 'coin_name_en')->where($where)->get()->toArray();
    }

    /**
     * 所有币种信息
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function loan_coins()
    {
        return DB::table('coin')->select('id', 'coin_name_en as coin_name', 'loan_enable')->where(['show_flag' => 1])->get()->toArray();
    }

    /**
     * 根据币种ID获取币种信息
     * @param int $coin_id
     * @return object|\Swoft\Db\Eloquent\Model|\Swoft\Db\Query\Builder|null
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function loan_coin(int $coin_id)
    {
        return DB::table('coin')->select('id', 'coin_name_en')->where(['id' => $coin_id, 'show_flag' => 1, 'loan_enable' => 1])->first();
    }

    /**
     * 获取币种最新价格
     * @param string $coin_type
     * @param string $price_type
     * @return string
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_coin_last_price(string $coin_type, string $price_type)
    {
        $field = strtolower($coin_type . $price_type);
        $res = Redis::hGet(config('app.coin_last_price_key'), $field);
        if (!$res) {
            try {
                $table_name = 'kline_' . $field . '_86400';
                $data = DB::table($table_name)->orderByDesc('group_id')->limit(1)->firstArray();
                if (!$data) {
                    return '0';
                }
                return $data['close_price'];
            } catch (\Swoft\Db\Exception\DbException $e) {
                return '0';
            }
        }
        $data = JsonHelper::decode($res, true);
        return $data['close_price'];
    }

    /**
     * 获取k线的最新一条数据
     * @param string $coin_type
     * @param string $price_type
     * @return array|mixed|string
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_kline(string $coin_type, string $price_type)
    {
        $field = strtolower($coin_type . $price_type);
        $res = Redis::hGet(config('app.coin_last_price_key'), $field);
        if (!$res) {
            $table_name = 'kline_' . $field . '_86400';
            $data = DB::table($table_name)->orderByDesc('group_id')->limit(1)->firstArray();
            if (!$data) {
                return '0';
            }
            return $data;
        }
        $data = JsonHelper::decode($res, true);
        return $data;
    }

    /**
     * 获取昨日收盘价
     * @param string $coin_type
     * @param string $price_type
     * @return mixed|string
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_yesterday_close_price(string $coin_type, string $price_type)
    {
        $field = strtolower($coin_type . $price_type);
        $table_name = 'kline_' . $field . '_86400';
        $data = DB::table($table_name)->orderByDesc('group_id')->forPage(2, 1)->firstArray();
        if ($data) {
            return $data['close_price'];
        }
        return '0';
    }

    /**
     * 获取链部分信息
     * @param string $coin_type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function get_chains(string $coin_type)
    {
        return DB::table('coin_token as t1')
            ->select('t1.coin_name', 't1.display_name', 't2.chain_name')
            ->leftJoin('chain as t2', 't1.chain_id', '=', 't2.id')
            ->where(['t1.coin_name' => strtolower($coin_type), 't1.cancel_flag' => 0])
            ->get()->toArray();
    }

    /**
     * 设置所有货币存到redis（包含所有的货币币种）
     * @return array|bool
     * @throws DbException
     */
    public static function set_redis_coins()
    {
        $re = self::get_coin_all();
        if ($re) {
            foreach ($re as $row) {
                $value = JsonHelper::encode($row);
                $field = $row['coin_name_en'];
                Redis::hset(config('app.table_coin'), $field, $value);
            }
            return $re;
        }
        return [];
    }

    /**
     * 设置单条
     * @param string $coin_type
     * @return array
     * @throws DbException
     */
    public static function set_redis_coin(string $coin_type)
    {
        $field = strtolower($coin_type);
        $data = self::get_coin_info_by_coin_type($coin_type);
        if ($data) {
            $value = JsonHelper::encode($data);
            Redis::hset(config('app.table_coin'), $field, $value);
        }
        return $data;
    }

    /**
     * 获取所有货币信息
     * return array
     * @throws DbException
     */
    public static function get_coins()
    {
        $res = Redis::hgetall(config('app.table_coin'));
        if (!$res) {
            return self::set_redis_coins();
        }
        $coins = [];
        foreach ($res as $val) {
            $coins[] = JsonHelper::decode($val, true);
        }
        return $coins;
    }

    /**
     * 根据货币英文名获取一条redis信息
     * @param $coin_type
     * @return array|mixed
     * @throws DbException
     */
    public static function get_coin(string $coin_type)
    {
        $coin_type = strtolower($coin_type);
        $field = strtolower($coin_type);
        $res = Redis::hget(config('app.table_coin'), $field);
        if (!$res) {
            return self::set_redis_coin($coin_type);
        } else {
            return JsonHelper::decode($res, true);
        }
    }

    /**
     * 删除redis coin
     * @param $coin_type
     * @return string
     */
    public static function del_redis_coin(string $coin_type)
    {
        $field = strtolower($coin_type);
        return Redis::hDel(config('app.table_coin'), $field);
    }

    /**
     * 更新coin
     * @param $data
     */
    public static function update_redis_coin(array $data)
    {
        $field = $data['coin_name_en'];
        Redis::hset(config('table_coin'), $field, json_encode($data));
    }

    /**
     * 获取单条币种信息
     * @param $id
     * @return array
     * @throws DbException
     */
    public static function get_coin_info(int $id)
    {
        return DB::table('coin')
            ->leftJoin('coin_token', 'coin.id', '=', 'coin_token.coin_id')
            ->leftJoin('chain', 'chain.id', '=', 'coin_token.chain_id')
            ->where(array(
                ['show_flag', '!=', 0],
                ['coin.id', '=', $id]
            ))->firstArray();
    }

    /**
     * 获取单条币种信息
     * @param string $coin_type
     * @return array
     * @throws DbException
     */
    public static function get_coin_info_by_coin_type(string $coin_type)
    {
        return DB::table('coin')
            ->where(array(
                ['show_flag', '!=', 0],
                ['coin_name_en', '=', strtolower($coin_type)]
            ))->firstArray();
    }

    /**
     * 获取所有coin
     * @return array|bool
     * @throws DbException
     */
    public static function get_coin_all()
    {
        return DB::table('coin')
            ->where('show_flag', '!=', '0')
            ->get()->toArray();
    }

}
