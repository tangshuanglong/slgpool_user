<?php


namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Lib\MyValidator;
use App\Model\Data\CoinData;
use App\Model\Data\ConfigData;
use App\Model\Data\UserData;
use App\Model\Data\WalletDwData;
use App\Model\Entity\WalletPayOrders;
use App\Rpc\Lib\CoinInterface;

use Swoft\Bean\Annotation\Mapping\Inject;
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
use App\Model\Data\PaginationData;
use function Swlib\Http\str;

/**
 * Class AddressController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/pay")
 * @Middlewares({
 *      @Middleware(AuthMiddleware::class)
 *     })
 */
class PayController
{

    /**
     * @Reference(pool="system.pool")
     * @var CoinInterface
     */
    private $coinService;

    /**
     * @Inject()
     * @var MyValidator
     */
    private $myValidator;

    /**
     * @Inject()
     *
     * @var MyCommon
     */
    private $myCommon;

    /**
     * 老板钱包
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get(Request $request)
    {
        //TODO 多账户轮训 目前一种方式只有一个支付账号。可能需要多张银行卡来平均一天的收款，减少风险
        $params = $request->params;
        if (empty($params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($params, 'AddressValidator', ['pay_type']);
        $pay_list = DB::table("wallet_pay_method")->where(['pay_type' => $params['pay_type']])->firstArray();
        if (empty($pay_list)) {
            return MyQuit::returnMessage(MyCode::COIN_NOT_EXISTS, '支付方式不存在');
        }
        $data = $pay_list;
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 充值
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function post(Request $request)
    {
        $params = $request->post();
        if (empty($params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($params, 'PayValidator', ['pay_method_id', 'price']);
        //验证资金密码
        if ($request->user_info['trade_pwd'] === '') {
            return MyQuit::returnMessage(MyCode::TRADE_PWD_NOT_SET, '请设置资金密码');
        }
        if ($request->user_info['real_name_cert'] == 0) {
            return MyQuit::returnMessage(MyCode::NO_CERTIFICATION, '请实名认证');
        }
        if ($request->user_info['mobile'] === '') {
            return MyQuit::returnMessage(MyCode::NO_BIND_MOBILE, '未绑定手机号');
        }

        $isTrue = WalletPayOrders::where(["status" => 0, 'uid' => $request->uid])->exists();
        if (!empty($isTrue)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '有一笔待确认订单');
        }
        $order = WalletPayOrders::new();
        $order->setMethodId($params["pay_method_id"]);
        $order->setAccountName('');
        $order->setOutTradeOrderNo('');
        if (!empty($params['account_number'])) {
            $order->setAccountNumber($params['account_number']);
        } else {
            $order->setAccountNumber("");
        }
        $order->setPrice($params['price']);
        $order->setStatus(0);
        $order->setUid($request->uid);
        $order->setOrderId(MyCommon::generate_order_number($request->uid));
        $order->setCreateTime(time());
        $order->setUpdateTime(time());
        if (!$order->save()) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '订单创建失败');
        }
        $data = [
            'id' => $order->getId(),
        ];
        //发送审核短信
        [$config_json] = ConfigData::getConfigValue('system', 'sms_mobile');
        $config = json_decode($config_json, true);
        $config_data = [
            'area_code' => $config[0],
            'mobile' => $config[1],
        ];
        $send_data = ['充值'];
        $this->myCommon->push_notice_queue($config_data['mobile'], $config_data['area_code'], 'audit_temp_id', '', $send_data);
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 充值记录
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function list(Request $request)
    {
        $params = $request->params;
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $where = [
            ["wpo.uid", '=', $request->uid],
        ];

        if (isset($params["status"]) && $params["status"] !== "") {
            $where[] = ['wpo.status', '=', $params['status']];
        }
        if (isset($params['month']) && isset($params['year']) && $params["year"] != "" && $params["month"] != "") {
            if (isset($params['month']) && isset($params['year']) && $params["year"] != "" && $params["month"] != "") {
                $array = MyCommon::frist_and_last($params['year'], $params['month']);
                $where[] = ['wpo.create_time', '>=', $array['firsttime']];
                $where[] = ['wpo.create_time', '<=', $array['lasttime']];
            }
        }

        $data = PaginationData::table('wallet_pay_orders as wpo')
            ->select('wpo.id', 'wpo.method_id', 'wpo.order_id', 'wpo.out_trade_order_no', 'wpo.status', 'wpo.account_name', 'wpo.price', 'wpo.create_time', 'wpo.update_time', 'wpm.pay_name')
            ->leftJoin('wallet_pay_method as wpm', 'wpm.id', '=', 'wpo.method_id')
            ->where($where)
            ->forPage($page, $size)
            ->orderBy('wpo.id', 'desc')
            ->get();
        foreach ($data['data'] as &$val) {
            $val['create_time'] = date("Y-m-d H:i:s", $val['create_time']);
            $val['update_time'] = date("Y-m-d H:i:s", $val['update_time']);
            unset($val);
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 充值详情
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function details(Request $request)
    {
        $params = $request->params;
        validate($params, 'PayValidator', ['id']);
        $where = [
            'wpo.uid' => $request->uid,
            'wpo.id'  => $params['id'],
        ];
        $data = DB::table('wallet_pay_orders as wpo')
            ->select('wpo.id', 'wpo.method_id', 'wpo.order_id', 'wpo.out_trade_order_no', 'wpo.status', 'wpo.account_name', 'wpo.price', 'wpo.create_time', 'wpo.update_time', 'wpm.pay_name')
            ->leftJoin('wallet_pay_method as wpm', 'wpm.id', '=', 'wpo.method_id')
            ->where($where)
            ->first();
        if (empty($data)) {
            return MyQuit::returnMessage(MyCode::ORDER_NOT_EXISTS, "订单未找到");
        }
        $data['create_time'] = date("Y-m-d H:i:s", $data['create_time']);
        $data['update_time'] = date("Y-m-d H:i:s", $data['update_time']);

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
