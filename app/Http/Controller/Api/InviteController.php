<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Model\Data\InviteLogData;
use App\Model\Data\PaginationData;
use App\Model\Data\RebateLevelConfigData;
use App\Model\Data\RebateLevelData;
use App\Model\Entity\RewardLog;
use App\Rpc\Lib\InviteLogInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class InviteController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/invite")
 * @Middlewares({
 *      @Middleware(AuthMiddleware::class)
 *     })
 */
class InviteController
{

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * 邀请统计
     * @param Request $request
     * @return array
     * @RequestMapping(method={RequestMethod::GET})
     * @throws \Swoft\Db\Exception\DbException
     */
    public function invite_statistic(Request $request)
    {
        $data['count'] = InviteLogData::get_total_people($request->uid);
        $data['total_rebate'] = bcadd(RewardLog::where(['to_uid' => $request->uid, 'status' => 1])->sum('amount'), 0, 8);
        $data['invite_code'] = $request->user_info['invite_id'];
        $data['invite_url'] = config('web_domain').'/register?inviteCode=' . $data['invite_code'];
        //获取用户返佣等级和返佣比例
        $user_rebate_level = RebateLevelData::user_rebate_level_get($request->uid);
        if (!$user_rebate_level) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '用户不存在邀请等级');
        }
        $data['rebate_level'] = $user_rebate_level;
        $data['rebate_rule'] = '';
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 邀请记录
     * @param Request $request
     * @return array
     * @RequestMapping(method={RequestMethod::GET})
     * @throws \Swoft\Db\Exception\DbException
     */
    public function invite_record(Request $request)
    {
        $params = $request->get();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $status = '';
        if (isset($params['status'])) {
            if (!in_array($params['status'], [0, 1])) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
            }
            $status = $params['status'];
        }
        $where = [
            'uid' => $request->uid,
        ];
        if ($status) {
            $where['status'] = $status;
        }
        $data = PaginationData::table('invite_log')->select('invited_account', 'status', 'create_time')
            ->where($where)
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get();
        if ($data) {
            foreach ($data['data'] as $key => $val) {
                $data['data'][$key]['invited_account'] = $this->myCommon->phoneCipher($val['invited_account'], 3, 5);
                $data['data'][$key]['create_time'] = date("Y-m-d H:i:s", $val['create_time']);
            }
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 返佣记录
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function rebate_record(Request $request)
    {
        $params = $request->get();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $where = [
            'to_uid' => $request->uid,
        ];
        if (isset($params['start_date'])) {
            validate($params, 'InviteValidator', ['start_date']);
            $where[] = ['created_at', '>', $params['start_date']];
        }
        if (isset($params['end_date'])) {
            validate($params, 'InviteValidator', ['end_date']);
            $where[] = ['created_at', '<=', $params['end_date']];
        }
        if (isset($params['start_date']) && isset($params['end_date'])) {
            if ($params['start_date'] > $params['end_date']) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '开始时间必须大于结束时间');
            }
        }
        $data = PaginationData::table('reward_log')
            ->select('from_account', 'coin_type', 'amount', 'buy_amount', 'created_at')
            ->where($where)
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get();
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['from_account'] = $this->myCommon->phoneCipher($val['from_account'], 3, 5);
            $data['data'][$key]['coin_type'] = strtoupper($val['coin_type']);
            unset($key, $val);
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
