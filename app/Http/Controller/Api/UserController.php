<?php

namespace App\Http\Controller\Api;

use App\Http\Middleware\AuthMiddleware;
use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Lib\MyValidator;
use App\Model\Data\ConfigData;
use App\Model\Data\PaginationData;
use App\Model\Data\UserData;
use App\Model\Data\WalletAddress;
use App\Model\Data\WalletData;
use App\Model\Data\WalletDwData;
use App\Model\Data\WalletMiningData;
use App\Model\Entity\ChiaOrder;
use App\Model\Entity\CoinWithdrawLog;
use App\Model\Entity\PowerOrder;
use App\Model\Logic\UserLogic;
use App\Rpc\Lib\CoinInterface;
use App\Rpc\Lib\KlineInterface;
use App\Rpc\Lib\VerifyInterface;
use App\Rpc\Lib\WalletDwInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Log\Helper\CLog;
use Swoft\Redis\Redis;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class User
 * @package App\Lib
 * @Controller(prefix="/v1/user")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class)
 * })
 */
class UserController
{

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @Reference(pool="system.pool")
     * @var CoinInterface
     */
    private $coinService;

    /**
     * @Reference(pool="system.pool")
     * @var VerifyInterface
     */
    private $verifyService;

    /**
     * @Reference(pool="system.pool")
     * @var KlineInterface
     */
    private $klineService;

    /**
     * @Inject()
     * @var WalletDwData
     */
    private $walletDwData;

    /**
     * @Inject()
     * @var WalletData
     */
    private $walletData;

    /**
     * @Reference(pool="user.pool")
     * @var WalletDwInterface
     */
    private $walletDwServer;

    /**
     * @Inject()
     * @var WalletMiningData
     */
    private $walletMiningData;

    /**
     * @Inject()
     * @var WalletAddress
     */
    private $walletAddress;

    /**
     * @Inject()
     * @var MyValidator
     */
    private $myValidator;

    /**
     * @Inject()
     * @var UserLogic
     */
    private $userLogic;

    /**
     * 我的钱包
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function assets(Request $request)
    {
        if (empty($request->params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($request->params, 'UserValidator', ['assets_type']);
        $assets_type = $request->params['assets_type'];
        $all_coin = $this->coinService->get_all_coin_name();
        if ($all_coin === false) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
        }
        $data = $this->userLogic->getAssets($request->uid, $assets_type, $all_coin);
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 获取账户总资产和可用余额
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function my_assets(Request $request)
    {
        $all_coin = $this->coinService->get_all_coin_name();
        if ($all_coin === false) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
        }
        $assets = $this->userLogic->getAssets($request->uid, 'dw', $all_coin);

        $yesterday_income = 0;
        $income_total = DB::table("power_income_total")->select("day", "yesterday_income")
            ->where(["uid" => $request->uid])->get()->toarray();
        foreach ($income_total as $k => $v) {
            if ($v['day'] < 541) {
                $yesterday_income += $v['yesterday_income'];
            }
        }
        $cny_usdt_price = $this->klineService->get_last_close_price('usdt', 'cny');
        $fil_usdt_price = $this->klineService->get_last_close_price('fil', 'usdt');
        $yester_usdt = $yesterday_income * $fil_usdt_price;
        $yester_cny = bcmul($yester_usdt, $cny_usdt_price, 2);

        $data = [
            'total_price_cny'      => $assets['total_price_cny'],
            'free_amount_cny'      => $assets['total_free_amount_cny'],
            'yesterday_income_cny' => $yester_cny,
        ];

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 充币
     * @param Request $request
     * @return array|mixed
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function deposit(Request $request)
    {
        $params = $request->params;
        if (!$params) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($params, 'UserValidator', ['coin_type', 'chain_name']);
        $params['coin_type'] = strtolower($params['coin_type']);
        if ($params['chain_name'] === '') {
            $params['chain_name'] = $params['coin_type'];
        }
        $params['chain_name'] = strtolower($params['chain_name']);
        $coin_info = $this->coinService->get_coin_info($params['coin_type']);
        if ($coin_info['id'] === false) {
            return MyQuit::returnMessage(MyCode::COIN_NOT_EXISTS, '币种不存在');
        }
        if ($coin_info['charge_status'] === 2) {
            return MyQuit::returnMessage(MyCode::DEPOSIT_NOT_OPEN, '该币种未开放充币');
        }
        //获取公链信息
        $chain_info = $this->coinService->get_chain_info($request->params['chain_name']);
        if (!$chain_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '公链不存在');
        }
        //获取coin token信息
        $token_info = $this->coinService->get_token_info($coin_info['id'], $chain_info['id']);
        if (!$token_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        $address = $this->walletAddress->get_address($request->uid, $params['chain_name']);
        if ($address === false) {
            return MyQuit::returnMessage(MyCode::ADDRESS_SHORTAGE, '钱包地址不足，请稍后重试！');
        }
        $data = [
            'address'      => $address,
            'is_graphene'  => $chain_info['is_graphene'],
            'confirmation' => $chain_info['confirmation_time'],
            'account_name' => $token_info['account_name'],
            'min_deposit'  => bcadd($token_info['min_deposit'], 0, 8),
        ];
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 提币信息
     * @param Request $request
     * @return array
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function withdraw_info(Request $request)
    {
        $params = $request->params;
        if (!$params) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($params, 'UserValidator', ['coin_type', 'chain_name']);
        $params['coin_type'] = strtolower($params['coin_type']);
        if ($params['chain_name'] === '') {
            $params['chain_name'] = $params['coin_type'];
        }
        $params['chain_name'] = strtolower($params['chain_name']);
        $coin_id = $this->coinService->get_coin_id($params['coin_type']);
        if ($coin_id === false) {
            return MyQuit::returnMessage(MyCode::COIN_NOT_EXISTS, '币种不存在');
        }
        //获取公链信息
        $chain_info = $this->coinService->get_chain_info($request->params['chain_name']);
        if (!$chain_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '公链不存在');
        }
        //获取coin token信息
        $token_info = $this->coinService->get_token_info($coin_id, $chain_info['id']);
        if (!$token_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        $max_withdraw = $token_info['max_withdraw'];
        $limit_amount = Redis::hGet(config('redis_key.withdraw_limit') . $params['coin_type'], (string)$request->uid);
        $max_withdraw = bcsub($max_withdraw, $limit_amount, 8);
        $data = [
            'is_graphene'  => $chain_info['is_graphene'],
            'withdraw_fee' => bcadd($token_info['withdraw_fee'], 0, 8),
            'min_withdraw' => bcadd($token_info['min_withdraw'], 0, 8),
            'max_withdraw' => $max_withdraw,
        ];
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 提币验证
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function withdraw_verify(Request $request)
    {
        $params = $request->params;
        //验证参数
        validate($params, 'UserValidator', ['coin_type', 'chain_name', 'address', 'amount', 'memo']);
        //验证资金密码
        if ($request->user_info['trade_pwd'] === '') {
            return MyQuit::returnMessage(MyCode::TRADE_PWD_NOT_SET, '请设置资金密码');
        }
        if ($request->user_info['real_name_cert'] != 2) {
            return MyQuit::returnMessage(MyCode::NO_CERTIFICATION, '请实名认证');
        }
        if ($request->user_info['mobile'] === '') {
            return MyQuit::returnMessage(MyCode::NO_BIND_MOBILE, '未绑定手机号');
        }
        //验证用户组
        if ($request->user_info['user_group'] == 30) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '测试用户组不能提币');
        }
        $params['coin_type'] = strtolower($params['coin_type']);
        if ($params['chain_name'] === '') {
            $params['chain_name'] = $params['coin_type'];
        }
        $params['chain_name'] = strtolower($params['chain_name']);
        $coin_info = $this->coinService->get_coin_info($params['coin_type']);
        if ($coin_info['id'] === false) {
            return MyQuit::returnMessage(MyCode::COIN_NOT_EXISTS, '币种不存在');
        }
        if ($coin_info['get_cash_status'] === 2) {
            return MyQuit::returnMessage(MyCode::WITHDRAW_NOT_OPEN, '该币种未开放提币');
        }
        if (env('APP_DEBUG') != 1) {
            if (Redis::get(config('not_withdraw_key') . '_' . $request->uid)) {
                return MyQuit::returnMessage(MyCode::FORBBIDEN_WITHDRAW, '禁止提现');
            }
        }
        //验证钱包金额是否正常
        $abnormal = $this->walletData->user_wallet_abnormal($request->uid);
        if ($abnormal) {
            return MyQuit::returnMessage(MyCode::WALLET_ABNORMAL, '钱包金额异常，请联系客服处理');
        }
        //获取公链信息
        $chain_info = $this->coinService->get_chain_info($request->params['chain_name']);
        if (!$chain_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '公链不存在');
        }
        //获取coin token信息
        $token_info = $this->coinService->get_token_info($coin_info['id'], $chain_info['id']);
        if (!$token_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        //验证地址格式
        $key = $params['coin_type'];
        if ($params['coin_type'] !== $params['chain_name']) {
            $key .= '_' . $params['chain_name'];
        }
        $res = $this->myValidator->verify_address($key, $params['address']);
        if ($res === false) {
            return MyQuit::returnMessage(MyCode::ADDRESS_FORMAT_ERROR, '地址格式错误');
        }
        $amount = bcadd($params['amount'], 0, 8);
        $coin_actual_amount = bcsub($amount, $token_info['withdraw_fee'], 8);
        //验证最小额度和最大额度
        if ($coin_actual_amount < $token_info['min_withdraw']) {
            return MyQuit::returnMessage(MyCode::MIN_WITHDRAW_ERROR, '提币数量小于最小额度');
        }
        $max_withdraw = $token_info['max_withdraw'];
        $limit_amount = Redis::hGet(config('redis_key.withdraw_limit') . $params['coin_type'], (string)$request->uid);
        $max_withdraw -= $limit_amount;
        if ($coin_actual_amount > $max_withdraw) {
            return MyQuit::returnMessage(MyCode::MAX_WITHDRAW_LIMIT, '提现数量大于最大额度');
        }
        //验证用户余额
        $user_wallet_free = $this->walletDwData->get_wallet_free($request->uid, $coin_info['id']);
        if ($user_wallet_free < $amount) {
            return MyQuit::returnMessage(MyCode::BALANCE_ERROR, '余额不足');
        }
        $params['chain_id'] = $chain_info['id'];
        $params['token_id'] = $token_info['id'];
        $params['coin_id'] = $coin_info['id'];
        $params['coin_amount'] = $amount;
        $params['trade_handling_fee'] = $token_info['withdraw_fee'];
        $params['coin_actual_amount'] = $coin_actual_amount;
        $res = Redis::set(config('redis_key.withdraw_key') . $request->uid, json_encode($params),
            ["EX" => config('second_operate_expire')]);
        if ($res) {
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        }
        return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
    }

    /**
     * 提币
     * @param Request $request
     * @return array|bool
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function withdraw(Request $request)
    {
        $params = $request->params;
        //验证参数
        validate($params, 'UserValidator', ['mv_code', 'ev_code', 'gv_code']);
        //验证用户组
        if ($request->user_info['user_group'] == 30) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '测试用户组不能提币');
        }
        //验证提现信息
        $withdraw_info = Redis::get(config('redis_key.withdraw_key') . $request->uid);
        if (empty($withdraw_info)) {
            return MyQuit::returnMessage(MyCode::OPERATE_EXPIRE, '操作超时');
        }
        $withdraw_info = json_decode($withdraw_info, true);
        //验证验证码
        $res = $this->verifyService->auth_all_verify_code($request->uid, $params, 'withdraw');
        if ($res !== true) {
            return MyQuit::returnMessage($res['code'], $res['msg']);
        }
        //手续费
        $withdraw_info['uid'] = $request->uid;
        $withdraw_info['coin_name'] = $withdraw_info['coin_type'];
        $withdraw_info['coin_address'] = $withdraw_info['address'];
        DB::beginTransaction();
        try {
            //插入提现记录表
            $insert_id = CoinWithdrawLog::insertGetId($withdraw_info);
            if (!$insert_id) {
                throw new DbException('insert CoinWithdrawLog error');
            }
            //更新该提现记录表的流水号
            $withdraw_number = 'WD' . date("YmdHis") . $insert_id;
            $up_res = CoinWithdrawLog::where(['id' => $insert_id])->update(['withdraw_number' => $withdraw_number]);
            if (!$up_res) {
                throw new DbException('up CoinWithdrawLog error');
            }
            //添加余额变化
            $wallet_res = $this->walletDwServer->append_wallet_frozen($request->uid, $withdraw_info['coin_amount'],
                $withdraw_info['coin_id'], 'withdraw_frozen');
            if ($wallet_res === false) {
                throw new DbException('余额不足', MyCode::BALANCE_ERROR);
            }
            DB::commit();
            Redis::del(config('redis_key.withdraw_key') . $request->uid);
            //设置用户提现的数量
            $this->myCommon->set_withdraw_amount(config('redis_key.withdraw_limit') . $withdraw_info['coin_type'],
                $request->uid, $withdraw_info['coin_amount']);
            //发送审核短信
            [$config_json] = ConfigData::getConfigValue('system', 'sms_mobile');
            $config = json_decode($config_json, true);
            $config_data = [
                'area_code' => $config[0],
                'mobile'    => $config[1],
            ];
            $send_data = ['提币'];
            $this->myCommon->push_notice_queue($config_data['mobile'], $config_data['area_code'], 'audit_temp_id', '', $send_data);
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        } catch (DbException $e) {
            DB::rollBack();
            CLog::error($e->getMessage());
            if ($e->getCode() === MyCode::BALANCE_ERROR) {
                return MyQuit::returnMessage(MyCode::BALANCE_ERROR, '用户余额不足');
            }
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
        }
    }

    /**
     * 划转【暂时不用】
     * @param Request $request
     * @return array
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function assets_transfer(Request $request)
    {
        $params = $request->params;
        validate($params, 'UserValidator', ['amount', 'coin_type', 'from', 'to']);
        if ($params['from'] === $params['to']) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        $coin_id = $this->coinService->get_coin_id($params['coin_type']);
        if ($coin_id === false) {
            return MyQuit::returnMessage(MyCode::COIN_NOT_EXISTS, '币种不存在');
        }
        $amount = bcadd($params['amount'], 0, 8);
        $method = $params['from'] . '_to_' . $params['to'];
        $class = 'wallet' . ucfirst($params['from']) . 'Data';
        $res = $this->$class->$method($request->uid, $amount, $coin_id);
        if ($res === 500) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
        }
        if ($res === false) {
            return MyQuit::returnMessage(MyCode::BALANCE_ERROR, '余额不足');
        }
        return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
    }

    /**
     * 获取单个币种资产
     * @param Request $request
     * @return array
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get_assets(Request $request)
    {
        $params = $request->params;
        validate($params, 'UserValidator', ['coin_id', 'assets_type']);

        $class = 'wallet' . ucfirst($params['assets_type']) . 'Data';
        $data = [
            "coin_id"            => $params['coin_id'],
            "free_coin_amount"   => "0.00000000", //可用金额
            "frozen_coin_amount" => "0.0000000000", //冻结金额
            "pledge_coin_amount" => "0.0000000000", //抵押金额，挖矿账户才有
        ];
        //获取币种名称
        $coin = $this->coinService->get_coin_name($params['coin_id']);
        if (!$coin) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '币种不存在');
        }
        $data['coin_type'] = $coin['coin_name_en'];
        $wallet = $this->$class->get_wallet($request->uid, $params['coin_id']);
        if ($wallet) {
            $data['free_coin_amount'] = bcadd($wallet['free_coin_amount'], 0, 8);
            $data['frozen_coin_amount'] = bcadd($wallet['frozen_coin_amount'], 0, 8);
            if ($params['assets_type'] === 'mining') {
                $data['pledge_coin_amount'] = bcadd($wallet['pledge_coin_amount'], 0, 8);
            }
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 余额明细
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function bill_details(Request $request)
    {
        $params = $request->params;
        validate($params, 'UserValidator', ['assets_type', 'coin_type']);
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $table_name = 'user_amount_log_' . $params['assets_type'];
        $coin_type = strtolower($params['coin_type']);
        if ($params['assets_type'] === 'mining') {
            $table_name .= '_' . $coin_type;
        }
        $trade_type_table_name = 'trade_type_' . $params['assets_type'];
        $trade_type_data = DB::table($trade_type_table_name)->where(['show_type' => 1])->orWhere(['show_type' => 2])->get(['id'])->toArray();
        $trade_type_ids = [];
        foreach ($trade_type_data as $val) {
            $trade_type_ids[] = $val['id'];
        }
        $where = [
            'uid'             => $request->uid,
            'trade_coin_type' => $coin_type,
        ];
        if (isset($params['trade_type_id'])) {
            validate($params, 'UserValidator', ['trade_type_id']);
            if (!in_array($params['trade_type_id'], $trade_type_ids)) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '交易类型不存在');
            }
            $where['trade_type_id'] = $params['trade_type_id'];
        }
        if (isset($params['start_date'])) {
            validate($params, 'UserValidator', ['start_date']);
            $where[] = ['create_time', '>=', $params['start_date']];
        }
        if (isset($params['end_date'])) {
            validate($params, 'UserValidator', ['end_date']);
            $where[] = ['create_time', '<', $params['end_date']];
        }
        $data = PaginationData::table($table_name)
            ->select('trade_type_id', 'trade_coin_type', 'trade_coin_amount', 'create_time')
            ->where($where)
            ->whereIn('trade_type_id', $trade_type_ids)
            ->forPage($page, $size)
            ->orderBy('id', 'desc')
            ->get();
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['trade_coin_type'] = strtoupper($val['trade_coin_type']);
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 交易类型列表
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function trade_type_list(Request $request)
    {
        $params = $request->params;
        validate($params, 'UserValidator', ['assets_type']);
        $table_name = 'trade_type_' . $params['assets_type'];
        $data = DB::table($table_name)->where(['show_type' => 1])->orWhere(['show_type' => 2])->get()->toArray();
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 我的收益
     * @param Request $request
     * @return array
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function income(Request $request)
    {
        $params = $request->get();
        validate($params, 'UserValidator', ['product_type']);
        $data = DB::table("power_income_total as pit")
            ->select("pit.*", "po.pledge_price")
            ->leftJoin('power_order as po', 'po.id', '=', 'pit.order_id')
            ->where([
                'pit.uid'          => $request->uid,
                'pit.product_type' => $params['product_type'],
            ])
            ->orderBy('id', 'desc')
            ->get()->toArray();

        $total_income = 0;
        foreach ($data as $k => &$v) {
            $v['yesterday_income'] = bcadd($v['yesterday_income'], 0, 4);
            $v['yesterday_released'] = bcadd($v['yesterday_released'], 0, 4);
            $v['total_unreleased'] = bcadd($v['total_unreleased'], 0, 4);
            $v['total_income'] = bcadd($v['total_income'], 0, 4);
            $v['total_released'] = bcadd($v['total_released'], 0, 4);
            $total_income += $v['total_income'];
            $v['pledge'] = $v['pledge_price'];
        }

        $fil_to_usdt = $this->klineService->get_last_close_price('fil', "usdt");
        $usdt_to_cny = $this->klineService->get_last_close_price('usdt', 'cny');
        $usdt = bcmul($total_income, $fil_to_usdt, 4);
        $total_income_cny = bcmul($usdt, $usdt_to_cny, 4);
        $ret = [
            'total_income_fil'  => $total_income,
            'total_income_usdt' => $usdt,
            'total_income_cny'  => $total_income_cny,
            'data'              => $data,
        ];

        return MyQuit::returnSuccess($ret, MyCode::SUCCESS, 'success');
    }

    /**
     * 我的收益详情
     * @param Request $request
     * @RequestMapping(method={RequestMethod::GET})
     * @return array
     */
    public function income_detals(Request $request)
    {
        $params = $request->params;
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        validate($params, 'UserValidator', ['order_id']);
        $period = PowerOrder::where('id', $params['order_id'])->value('period');
        $is_experience = PowerOrder::where(['id' => $params['order_id']])->value('is_experience');
        $where = [
            'uid'         => $request->uid,
            'order_id'    => $params['order_id'],
            'income_type' => 1,
        ];
        if ($is_experience != 1) {
            $where[] = ['day', '<=', $period];
        }
        if (isset($params['start_date'])) {
            validate($params, 'UserValidator', ['start_date']);
            $where[] = ['created_at', '>=', $params['start_date']];
        }
        if (isset($params['end_date'])) {
            validate($params, 'UserValidator', ['end_date']);
            $where[] = ['created_at', '<', $params['end_date']];
        }

        $data = PaginationData::table('power_income')
            ->select('id', 'order_id', 'created_at', 'today_income', 'released', 'manage_fee', 'release_days', 'day')
            ->where($where)
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get();
        foreach ($data['data'] as $key => $val) {
            //已释放收益率：已释放收益/今日收益
            $data['data'][$key]['released_rate'] = $val['today_income'] != 0 ? bcmul($val['released'] / $val['today_income'], 100, 2) : '0.00';
            if ($is_experience == 1) {
                $data['data'][$key]['total_day'] = 0;
            } else {
                $data['data'][$key]['total_day'] = 180;
            }
            //总天数
            $data['data'][$key]['period'] = $period;
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * chia-我的收益
     * @param Request $request
     * @return array
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function chia_income(Request $request)
    {
        $params = $request->params;
        validate($params, 'UserValidator', ['product_type']);
        $data = DB::table("chia_income_total")
            ->where([
                'uid'          => $request->uid,
                'product_type' => $params['product_type'],
            ])
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        $total_income = 0;
        foreach ($data as $k => &$v) {
            $chia_order_info = ChiaOrder::select('period', 'shelf_date')->where('id', $v['order_id'])->first();
            $day = MyCommon::days_between_dates(date('Y-m-d H:i:s'), $chia_order_info['shelf_date']);//当前挖矿天数
            if ($day <= $chia_order_info['period']) {
                $chia_income_info = DB::table('chia_income')->where([['order_id', '=', $v['order_id']], ['day', '<', $v['day']]])->orderBy('id', 'desc')->first();
            } elseif ($day == $chia_order_info['period'] + 1) {
                $chia_income_info = DB::table('chia_income')->where([['order_id', '=', $v['order_id']], ['day', '<=', $v['day']]])->orderBy('id', 'desc')->first();
            } elseif ($day > $chia_order_info['period'] + 1) {
                $chia_income_info = null;
            }
            $v['yesterday_income'] = $chia_income_info ? bcadd($chia_income_info['today_income'], 0, 4) : '0.0000';
            $v['total_income'] = bcadd($v['total_income'], 0, 4);
            $total_income += $v['total_income'];
        }
        $xch_to_usdt = $this->klineService->get_last_close_price('xch', "usdt");
        $usdt_to_cny = $this->klineService->get_last_close_price('usdt', 'cny');
        $usdt = bcmul($total_income, $xch_to_usdt, 4);
        $total_income_cny = bcmul($usdt, $usdt_to_cny, 4);
        $ret = [
            'total_income_xch'  => $total_income,
            'total_income_usdt' => $usdt,
            'total_income_cny'  => $total_income_cny,
            'data'              => $data,
        ];
        return MyQuit::returnSuccess($ret, MyCode::SUCCESS, 'success');
    }

    /**
     * chia-我的收益详情
     * @param Request $request
     * @RequestMapping(method={RequestMethod::GET})
     * @return array
     */
    public function chia_income_detals(Request $request)
    {
        $params = $request->params;
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        validate($params, 'UserValidator', ['order_id']);
        $where = [
            'uid'         => $request->uid,
            'order_id'    => $params['order_id'],
            'income_type' => 1
        ];
        if (isset($params['start_date'])) {
            validate($params, 'UserValidator', ['start_date']);
            $where[] = ['created_at', '>=', $params['start_date']];
        }
        if (isset($params['end_date'])) {
            validate($params, 'UserValidator', ['end_date']);
            $where[] = ['created_at', '<', $params['end_date']];
        }
        $data = PaginationData::table('chia_income')
            ->select('id', 'order_id', 'created_at', 'today_income', 'manage_fee', 'day')
            ->where($where)
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get();
        foreach ($data['data'] as $key => $val) {
            //总天数
            $data['data'][$key]['period'] = ChiaOrder::where('id', $val['order_id'])->value('period');
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 存币冻结列表
     * @param Request $request
     * @RequestMapping(method={RequestMethod::GET})
     * @return array
     */
    public function coin_store_log(Request $request)
    {
        $params = $request->params;
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $data = PaginationData::table('coin_store_log')
            ->where(['uid' => $request->uid])
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get();
        foreach ($data['data'] as $key => $val) {
            if (config('debug') != 1) {
                $data['data'][$key]['created_at'] = date('Y-m-d', strtotime($val['created_at']));
                $data['data'][$key]['release_date'] = date('Y-m-d', strtotime($val['release_date']));
            }
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
