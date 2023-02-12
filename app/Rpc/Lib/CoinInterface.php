<?php

namespace App\Rpc\Lib;

/**
 * Interface CoinInterface
 * @package App\Lib
 */
interface CoinInterface{

    /**
     * 获取币种id
     * @param string $coin_type
     * @return bool|mixed
     */
    public function get_coin_id(string $coin_type);

    /**
     * @param string $coin_type
     * @return array|bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_info(string $coin_type);

    /**
     * 获取token信息
     * @param $coin_id
     * @param $chain_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_token_info($coin_id, $chain_id);

    /**
     * 获取公链信息
     * @param string $chain_name
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_chain_info(string $chain_name);

    /**
     * @param string $chain_name
     * @return mixed
     */
    public function chain_exists(string $chain_name);

    /**
     * 获取所有币种名称
     * @return mixed
     */
    public function get_all_coin_name();

    /**
     * 获取币种的所有token信息
     * @param string $coin_name
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_tokens(string $coin_name);

    /**
     * 根据币种id 获取币种名称
     * @param int $coin_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_name(int $coin_id);

    /**
     * 获取币种最新价格
     * @param string $coin_type
     * @param string $price_type
     * @return string
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_last_price(string $coin_type, string $price_type);
}
