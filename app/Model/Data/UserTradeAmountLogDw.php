<?php

namespace App\Model\Data;

use App\Rpc\Lib\CoinInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\DB;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class UserTradeAmountLogDw
 * @package App\Model\Data
 * @Bean("UserTradeAmountLogDw")
 */
class UserTradeAmountLogDw{

    /**
     * @Reference(pool="system.pool")
     * @var CoinInterface
     */
    private $coinService;

    private $table_prefix = 'user_amount_log_dw_';

    /**
     * 根据开始时间和结束时间，在多个表里面获取数据
     * @param $uid
     * @param int $start_timestamp
     * @param int $end_timestamp
     * @param int $page
     * @param string $coin_name
     * @param array $trade_type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_record($uid, int $start_timestamp, int $end_timestamp, int $page, string $coin_name, array $trade_type): array
    {
//        $start_timestamp = strtotime($start_time);
//        $end_timestamp = strtotime($end_time);
        $where = [
            'uid' => $uid,
            ['create_time', '>=', $start_timestamp],
            ['create_time', '<', $end_timestamp]
        ];
        if ($coin_name != '') {
            $coin_id = $this->coinService->get_coin_id($coin_name);
            $where['coin_id'] = $coin_id;
        }
        //因为半年分次表，所以1月1号和7月1号会记录新表，
        $start_suffix = ceil(date('m', $start_timestamp) / 6);
        $start_year = date("Y", $start_timestamp);
        $end_suffix = ceil(date('m', $end_timestamp) / 6);
        $end_year = date("Y", $end_timestamp);
        if ($start_suffix === $end_suffix && $start_year === $end_year) {
            $table_name = $this->table_prefix.$start_year.'_'.$start_suffix;
            $offset = ($page-1) * config('page_num');
            $data = $this->get_log($table_name, $where, $trade_type, $offset, config('page_num'));
            foreach ($data as $key => $val) {
                $data[$key]['create_time'] = date("Y-m-d H:i:s", $val['create_time']);
                unset($key, $val);
            }
        }else{//该算法只满足时间跨一张表
            $start_year = date("Y", $start_timestamp);
            $start_table_name = $this->table_prefix.$start_year.'_'.$start_suffix;
            //开始时间的表的数据量的总数
            $start_count = DB::table($start_table_name)->where($where)->count();
            $end_year = date("Y", $end_timestamp);
            $end_table_name = $this->table_prefix.$end_year.'_'.$end_suffix;
            //结束时间的表的数据量的总数
            $end_count = DB::table($end_table_name)->where($where)->count();
            if ($end_count === 0 && $start_count === 0) {
                return [];
            }
            $end_total_page = ceil($end_count / config('page_num'));
            $last_page_num = $end_count - ($end_total_page - 1) * config('page_num');
            if ($page < $end_total_page) {
                $offset = ($page-1) * config('page_num');
                $data = $this->get_log($end_table_name, $where, $trade_type, $offset, config('page_num'));
            } elseif ($page === $end_total_page) {//临界点，去需要查两张表
                $end_offset = ($page-1) * config('page_num');
                $end_data = $this->get_log($end_table_name, $where, $trade_type, $end_offset, $last_page_num);
                $start_offset = 0;
                $start_page_num = config('page_num') - $last_page_num;
                $start_data = $this->get_log($start_table_name, $where, $trade_type, $start_offset, $start_page_num);
                $data = array_merge($end_data, $start_data);
            } else {
                //从第一页算起，加上$page === $end_total_page已经查询的config('page_num') - $last_page_num数量
                $start_offset = ($page - $end_total_page - 1) * config('page_num') + (config('page_num') - $last_page_num);
                var_dump($start_offset);
                $data = $this->get_log($start_table_name, $where, $trade_type, $start_offset, config('page_num'));
            }
            foreach ($data as $key => $val) {
                $data[$key]['create_time'] = date("Y-m-d H:i:s", $val['create_time']);
                unset($key, $val);
            }
        }
        return $data;
    }

    /**
     * @param string $table_name
     * @param array $where
     * @param array $where_in
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_log(string $table_name, array $where, array $where_in, int $offset, int $limit)
    {
        return DB::table($table_name)->select('trade_type_id',  'trade_coin_type', 'trade_coin_amount', 'create_time')
            ->where($where)->whereIn('trade_type_id', $where_in)->offset($offset)->limit($limit)->orderBy($table_name.'.create_time', 'desc')->get()->toArray();
    }
}
