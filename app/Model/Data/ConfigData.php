<?php

namespace App\Model\Data;

use App\Lib\MyCode;
use App\Lib\MyQuit;
use App\Model\Entity\InviteLog;
use App\Model\Entity\RebateLevel;
use App\Model\Entity\RewardLog;
use Swoft\Bean\BeanFactory;
use Swoft\Db\DB;
use Swoft\Redis\Redis;
use Swoft\Stdlib\Helper\JsonHelper;

/**
 * Class RebateLevelData
 * @package App\Model\Data
 */
class ConfigData
{

    const CONFIG_REDIS_KEY = 'config:group';

    /**
     * @param string $group
     * @param string $name
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function getConfigValue(string $group, string $name)
    {
        $newKey = self::CONFIG_REDIS_KEY . ':' . $group;
        $field = $newKey . ':' . $name;
        $config = json_decode(Redis::hGet($newKey, $field), true);
        if (!$config) {
            $config = DB::table('config')->where(['group' => $group, 'name' => $name])->firstArray();
        }

        if (!$config) {
            return [null, 404, '配置不存在'];
        }

        if ($config['cancel_flag']) {
            return [null, 403, '配置已撤销'];
        }

        $now = time();
        if (!$now > $config['start_time'] && !$now < $config['end_time']) {
            return [null, 403, '配置未生效或已过期'];
        }

        return [$config['value'], 200, 'success'];
    }

    //根据group获取配置表信息
    /**
     * @param string $group
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function config_info_group(string $group)
    {
        $data = self::get_redisConfigGroup($group);
        if(empty($data)){
            return self::set_redisConfig($group);
        }
        return $data;
    }

    //根据组id获取该组的所有数据
    public static function get_redisConfigGroup($group)
    {
        $key = 'config:group:'.$group;
        $res = Redis::hGetAll($key);
        $data = [];
        if($res){
            foreach($res as $value){
                $data[] = json_decode($value, true);
                unset($value);
            }
        }
        unset($res);
        return $data;
    }

    /**
     * 根据name和group获取redis配置数据
    /**
     * @param string $name
     * @param string $group
     * @return mixed
     */
    public static function get_redisConfigName(string $name, string $group)
    {
        $key = 'config:group:'.$group;
        $field = $key.':'.$name;
        return json_decode(Redis::hGet($key, $field), true);
    }

    /**
     * 设置配置数据到redis
     * @param string $group
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function set_redisConfig(string $group = '')
    {
        $key = 'config:group';
        $where = [
            'cancel_flag' => 0
        ];
        if ($group) {
            $where['group'] = $group;
        }
        $data = DB::table('config')->where($where)->get()->toArray();
        if(!empty($data)){
            foreach($data as $value){
                $new_key = $key.':'.$value['group'];
                $field = $new_key.':'.$value['name'];
                Redis::hSet($new_key, $field, json_encode($value));
            }
        }
        return $data;
    }

    //根据name和对应的组id获取对应的信息
    /**
     * @param $name
     * @param $group
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public static function config_info(string $name, string $group)
    {
        $data = self::get_redisConfigName($name, $group);
        if(empty($data)){
            self::set_redisConfig($group);
        }
        return self::get_redisConfigName($name, $group);
    }

    //删除缓存
    public static function del_redisConfig(string $group)
    {
        $key = 'config:group:'.$group;
        return Redis::del($key);
    }
}
