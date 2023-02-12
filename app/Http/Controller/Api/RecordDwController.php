<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyQuit;
use App\Model\Data\PaginationData;
use App\Model\Data\TradeTypeData;
use App\Rpc\Lib\CoinInterface;
use Swoft\Bean\BeanFactory;
use Swoft\Db\DB;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class RecordController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/record_dw")
 * @Middlewares({
 *      @Middleware(AuthMiddleware::class)
 *     })
 */
class RecordDwController
{

    /**
     * @Reference(pool="system.pool")
     * @var CoinInterface
     */
    private $coinService;

    /**
     * 充币记录
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function deposit(Request $request)
    {
        $params = $request->get();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $where = [
            't1.uid' => $request->uid
        ];
        if (isset($params['coin_name'])) {
            $where['t1.coin_name'] = strtolower($params['coin_name']);
        }
        $data = PaginationData::table('coin_deposit_log as t1')
            ->select('t1.id', 't1.coin_name', 't1.address', 't1.tx_hash', 't1.amount', 't1.created_at', 't1.updated_at',
                't1.status', 't2.chain_name', 't3.explorer_url', 't3.explorer_url_parameter_address', 't3.explorer_url_parameter_tx')
            ->leftJoin('chain as t2', 't1.chain_id', '=', 't2.id')
            ->leftJoin('coin_token as t3', 't1.token_id', '=', 't3.id')
            ->where($where)
            ->forPage($page, $size)
            ->orderBy('t1.id', 'desc')
            ->get();
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['coin_name'] = strtoupper($val['coin_name']);
            $data['data'][$key]['explorer_url'] = $val['explorer_url'] . $val['explorer_url_parameter_address'] . $val['explorer_url_parameter_tx'] . $val['tx_hash'];
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 提币记录
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function withdraw(Request $request)
    {
        $params = $request->get();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $where = [
            't1.uid' => $request->uid
        ];
        if (isset($params['coin_name'])) {
            $where['t1.coin_name'] = strtolower($params['coin_name']);
        }

        $data = PaginationData::table("coin_withdraw_log as t1")
            ->select('t1.id', 't1.coin_name', 't1.coin_address', 't1.trade_handling_fee', 't1.tx_hash', 't1.coin_amount', 't1.memo',
                't1.created_at', 't1.updated_at', 't1.status', 't1.remark', 't2.chain_name', 't3.explorer_url', 't3.explorer_url_parameter_address', 't3.explorer_url_parameter_tx')
            ->leftJoin('chain as t2', 't1.chain_id', '=', 't2.id')
            ->leftJoin('coin_token as t3', 't1.token_id', '=', 't3.id')
            ->where($where)
            ->forPage($page, $size)
            ->orderBy('t1.id', 'desc')
            ->get();
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['coin_name'] = strtoupper($val['coin_name']);
            $data['data'][$key]['explorer_url'] = $val['explorer_url'] . $val['explorer_url_parameter_address'] . $val['explorer_url_parameter_tx'] . $val['tx_hash'];
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 币币账户：划转、其他记录
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function financial(Request $request)
    {
        $params = $request->params;
        validate($params, 'UserValidator', ['record_type']);
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $where = [
            'uid' => $request->uid,
            ['create_time', '>=', date("Y-m-d H:i:s", strtotime("-3 month"))],
            ['create_time', '<', date("Y-m-d H:i:s")],
        ];
        if (isset($params['coin_name']) && $params['coin_name'] != "") {
            $coin_id = $this->coinService->get_coin_id($params['coin_name']);
            $where['trade_coin_id'] = $coin_id;
        }
        $where_in = [];
        $all = false;
        if ($params['record_type'] === "all") {
            $all = true;
        } elseif ($params['record_type'] === 'transfer') {
            $where_in = [6, 7];
        } elseif ($params['record_type'] === 'other') {
            $where_in = [8];
        }

        $data = PaginationData::table('user_amount_log_dw')->select('id', 'trade_type_id', 'trade_coin_type', 'trade_coin_amount', 'create_time')
            ->where($where)->whereIn('trade_type_id', $where_in, "and", $all)->forPage($page, $size)
            ->orderBy('id', 'desc')->get();

        $trade_type_list = DB::table('trade_type_dw')->where(['show_type' => 1])->get()->toArray();;

        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['trade_coin_type'] = strtoupper($val['trade_coin_type']);
            $data['data'][$key]['trade_type_name'] = (function () use ($val, $trade_type_list): string {
                $key = array_search($val["trade_type_id"], array_column($trade_type_list, 'id'));
                return $trade_type_list[$key]["type_name_cn"];
            })();
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 交易类型列表【暂时不用】
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function trade_type_list()
    {
        $data = DB::table('trade_type_dw')->where(['show_type' => 1])->get()->toArray();
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
