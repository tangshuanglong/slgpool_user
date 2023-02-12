<?php

namespace App\Rpc\Service;

use App\Lib\MyCommon;
use App\Model\Data\InviteLogData;
use App\Model\Data\PaginationData;
use App\Model\Data\RebateLevelConfigData;
use App\Model\Data\RebateLevelData;
use App\Model\Entity\InviteLog;
use Swoft\Db\DB;
use Swoft\Rpc\Server\Annotation\Mapping\Service;
use App\Rpc\Lib\InviteLogInterface;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * Class InviteLogService
 * @package App\Rpc\Service
 * @Service()
 */
class InviteLogService implements InviteLogInterface
{

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @param int $invited_uid
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_invite(int $invited_uid)
    {
        return InviteLogData::get_invite($invited_uid);
    }

    /**
     * 插入推荐记录
     * @param array $data
     * @return bool
     */
    public function insert_invite_log(array $data)
    {
        return InviteLog::insert($data);
    }

    /**
     * 获取推荐人的返佣比例
     * @param int $invited_uid
     * @return string
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_rebate_rate(int $invited_uid)
    {
        $invite_data = InviteLogData::get_invite($invited_uid);
        if (!$invite_data) {
            return '0';
        }
        //查询推荐人的等级
        $level = RebateLevelData::user_rebate_level($invite_data['uid']);
        return RebateLevelConfigData::rebate_rate($level);
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
    public function rebate(int $invited_uid, string $amount, string $unit, string $coin_type)
    {
        return InviteLogData::rebate($invited_uid, $amount, $unit, $coin_type);
    }

    /**
     * 推荐挖矿产出返佣
     * @param int $order_id
     * @param int $invited_uid
     * @param string $amount
     * @param string $unit
     * @param string $coin_type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function rebate_for_mining(int $order_id, int $invited_uid, string $amount, string $unit, string $coin_type)
    {
        return InviteLogData::rebate_for_mining($order_id, $invited_uid, $amount, $unit, $coin_type);
    }

    /**
     * @param int $uid
     * @param $status
     * @param $page
     * @param $size
     * @return mixed
     */
    public function get_invite_info(int $uid, $status, $page, $size)
    {
        // TODO: Implement get_invite_info() method.
    }
}
