<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Model\Data\PaginationData;
use App\Model\Entity\ArticleToUser;
use App\Rpc\Lib\KlineInterface;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Redis\Redis;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class ArticleController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/reward_log")
 * @Middlewares({
 * })
 */
class RewardLogController
{
    /**
     * 排行榜缓存key
     * @var string
     */
    private $rewardRankKey;

    /**
     * 排行榜缓存过期时间
     * @var false|int
     */
    private $expTime;

    public function __construct()
    {
        $this->expTime = strtotime(date('Y-m-d', strtotime('+1 day'))) - time();
        $this->rewardRankKey = 'user:reward:top';
    }

    /**
     * 用户邀请排行榜列表接口
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function reward_top_twenty(Request $request)
    {
        $rewardRankTwentyCache = Redis::hGet($this->rewardRankKey, 'twenty');
        $data = json_decode($rewardRankTwentyCache, true);
        if (!$data) {
            $data = DB::table("user_basical_info as ubi")
                ->selectRaw('bt_ubi.id, bt_ubi.mobile, bt_ubi.email, IFNULL(sum(bt_rl.amount), 0) as reward_amount')
                ->leftJoin('reward_log as rl', 'rl.to_uid', '=', 'ubi.id')
                ->groupBy('ubi.id')
                ->orderByDesc('reward_amount')
                ->limit(20)
                ->get()
                ->toArray();
            $sort = 0;
            foreach ($data as $key => $item) {
                if ($item['mobile']) {
                    $data[$key]['account'] = (new MyCommon())->phoneCipher($item['mobile'], 3, 5);
                } else {
                    $data[$key]['account'] = (new MyCommon())->phoneCipher($item['email'], 3, 5);
                }
                $data[$key]['sort'] = ++$sort;
                unset($data[$key]['mobile']);
                unset($data[$key]['email']);
            }
            Redis::hSet($this->rewardRankKey, 'twenty', json_encode($data));
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 用户邀请排行榜前三名接口
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function reward_top_three(Request $request)
    {
        $rewardRankThreeCache = Redis::hGet($this->rewardRankKey, 'three');
        $data = json_decode($rewardRankThreeCache, true);
        if (!$data) {
            $data = DB::table("user_basical_info as ubi")
                ->selectRaw('bt_ubi.id, bt_ubi.mobile, bt_ubi.email, IFNULL(sum(bt_rl.amount), 0) as reward_amount')
                ->leftJoin('reward_log as rl', 'rl.to_uid', '=', 'ubi.id')
                ->groupBy('ubi.id')
                ->orderByDesc('reward_amount')
                ->limit(3)
                ->get()
                ->toArray();
            $sort = 0;
            foreach ($data as $key => $item) {
                if ($item['mobile']) {
                    $data[$key]['account'] = (new MyCommon())->phoneCipher($item['mobile'], 3, 5);
                } else {
                    $data[$key]['account'] = (new MyCommon())->phoneCipher($item['email'], 3, 5);
                }
                $data[$key]['sort'] = ++$sort;
                unset($data[$key]['mobile']);
                unset($data[$key]['email']);
            }
            Redis::hSet($this->rewardRankKey, 'three', json_encode($data));
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
