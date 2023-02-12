<?php


namespace App\Model\Logic;


use App\Model\Data\PaginationData;
use Swoft\Db\DB;
use Swoft\Db\Query\Builder;


class ArticleLogic
{


    /**
     * @return array
     */
    public static function getArticle($page,$size,$user_id,$read = -1): array
    {

        $data = PaginationData::table("article as a")
            ->select('a.id', 'a.title', 'a.content', 'a.created_at', 'atu.user_id')
            ->leftJoin('article_to_user as atu', function (\Swoft\Db\Query\JoinClause $join) use ($user_id) {
                $join->on('atu.article_id', '=', 'a.id')
                    ->where('atu.user_id', '=', $user_id);
            })
            ->where([
                'a.type' => 'jpush',
                'a.status' => 1,
                'a.is_pushed' => 1
            ])
            ->forPage($page, $size)
            ->orderBy('a.order_num', 'desc')
            ->get();

        return $data;
    }
}
