<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyQuit;
use App\Lib\MyCommon;
use App\Model\Data\PaginationData;
use App\Model\Logic\ArticleLogic;
use App\Model\Entity\ArticleToUser;
use Swoft\Db\DB;
use Swoft\Db\Query\Builder as QueryBuilder;

use Swoft\Log\Helper\CLog;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;

/**
 * Class ArticleController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/article")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class)
 * })
 */
class ArticleController
{

    /**
     * 用户通知消息列表
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function list(Request $request)
    {
        $params = $request->params;
        $read = $params['read'] ?? -1;
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $user_id = $request->uid;
        $data = [];
        if ($read == -1) {//全部
            $data = PaginationData::table("article as a")
                ->select('a.id', 'a.title', 'a.summary', 'a.content', 'a.created_at', 'atu.user_id')
                ->leftJoin('article_to_user as atu', function (\Swoft\Db\Query\JoinClause $join) use ($user_id) {
                    $join->on('atu.article_id', '=', 'a.id')
                        ->where('atu.user_id', '=', $user_id);
                })
                ->where([
                    'a.type'      => 'jpush',
                    'a.status'    => 1,
                    'a.is_pushed' => 1
                ])
                ->forPage($page, $size)
                ->orderBy('a.order_num', 'desc')
                ->get();
        } elseif ($read == 1) {//已读
            $data = PaginationData::table("article as a")
                ->select('a.id', 'a.title', 'a.summary', 'a.content', 'a.created_at', 'atu.user_id')
                ->rightJoin('article_to_user as atu', function (\Swoft\Db\Query\JoinClause $join) use ($user_id) {
                    $join->on('atu.article_id', '=', 'a.id')
                        ->where('atu.user_id', '=', $user_id);
                })
                ->where([
                    'a.type'      => 'jpush',
                    'a.status'    => 1,
                    'a.is_pushed' => 1
                ])
                ->forPage($page, $size)
                ->orderBy('a.order_num', 'desc')
                ->get();
        } elseif ($read == 0) {//未读
            $data = PaginationData::table("article as a")
                ->select('a.id')
                ->rightJoin('article_to_user as atu', function (\Swoft\Db\Query\JoinClause $join) use ($user_id) {
                    $join->on('atu.article_id', '=', 'a.id')
                        ->where('atu.user_id', '=', $user_id);
                })
                ->where([
                    'a.type'      => 'jpush',
                    'a.status'    => 1,
                    'a.is_pushed' => 1
                ])->get();

            $ids = [];
            foreach ($data['data'] as $key => $item) {
                array_push($ids, $item['id']);
            }
            $data = PaginationData::table("article")
                ->select('id', 'title', 'summary', 'content', 'created_at')
                ->whereIn('id', $ids, "and", true)
                ->where([
                    'type'      => 'jpush',
                    'status'    => 1,
                    'is_pushed' => 1
                ])
                ->forPage($page, $size)
                ->orderBy('order_num', 'desc')
                ->get();
        }

        // 是否已读字段增加
        foreach ($data['data'] as $key => $item) {
            $data['data'][$key]['is_read'] = 0;
            if (isset($item['user_id']) && $item['user_id'] > 0) {
                $data['data'][$key]['is_read'] = 1;
            }
        }
        //添加剩余多少条消息未读
        $all_article_count = PaginationData::table("article")->where([
            'type'      => 'jpush',
            'status'    => 1,
            'is_pushed' => 1
        ])->count();

        $read_count = PaginationData::table("article_to_user")->where([
            'user_id' => $user_id
        ])->count();

        $data["not_read_count"] = $all_article_count - $read_count;

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 用户点击文章已读
     * @param Request $request
     * @param $id
     * @return array
     * @throws DbException
     * @RequestMapping(method={RequestMethod::GET}, route="{id}/read")
     */
    public function read(Request $request, int $id)
    {
        $userId = $request->uid;
        $article = DB::table('article')->where(['id' => $id])->firstArray();
        if (!$article || $article['type'] !== 'jpush') {
            return MyQuit::returnError(MyCode::SERVER_ERROR, '文章不存在或类型错误');
        }

        // 关联关系已存在直接返回，幂等接口
        if (DB::table('article_to_user')->where([
            'article_id' => $id,
            'user_id'    => $userId
        ])->firstArray()) {
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        }

        $articleToUser = ArticleToUser::new();
        $articleToUser->setUserId($userId);
        $articleToUser->setArticleId($id);
        $articleToUser->save();

        return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
    }

    /**
     * 用户点击全部已读
     * @param Request $request
     * @param $id
     * @return array
     * @throws DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function read_all(Request $request)
    {
        $userId = $request->uid;
        $articles = DB::table('article')->where(['type' => 'jpush', 'status' => 1, 'is_pushed' => 1])->get()->toArray();
        foreach ($articles as $article) {
            // 关联关系已存在直接返回，幂等接口
            if (DB::table('article_to_user')->where([
                'article_id' => $article['id'],
                'user_id'    => $userId
            ])->firstArray()) {
                continue;
            }

            $articleToUser = ArticleToUser::new();
            $articleToUser->setUserId($userId);
            $articleToUser->setArticleId($article['id']);
            $articleToUser->save();
        }

        return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
    }
}
