<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyQuit;
use App\Lib\MyValidator;
use App\Model\Entity\AddressManager;
use App\Rpc\Lib\CoinInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
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
 * Class AddressController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/address")
 * @Middlewares({
 *      @Middleware(AuthMiddleware::class)
 *     })
 */
class AddressController
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
     * 地址列表
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function list(Request $request)
    {
        $where = ['uid' => $request->uid];
        $currency = $request->params['currency'] ?? '';
        $chain_name = $request->params['chain_name'] ?? '';
        if ($currency) {
            $where['coin_name'] = $currency;
        }
        if ($chain_name) {
            $where['chain_name'] = $chain_name;
        }
        $data = DB::table('address_manager')->select('id', 'coin_name', 'chain_name', 'address', 'memo', 'remark')->where($where)->get()->toArray();
        foreach ($data as $key => $val) {
            $data[$key]['coin_name'] = strtoupper($val['coin_name']);
            $data[$key]['chain_name'] = strtoupper($val['chain_name']);
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 添加地址
     * @param Request $request
     * @return array
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function add(Request $request)
    {
        $params = $request->params;
        validate($params, 'AddressValidator', ['coin_name', 'chain_name', 'address', 'memo', 'remark']);
        $coin_name = strtolower($params['coin_name']);
        $coin_id = $this->coinService->get_coin_id($coin_name);
        if ($coin_id === false) {
            return MyQuit::returnMessage(MyCode::COIN_NOT_EXISTS, '币种不存在');
        }
        //验证地址有效性
        $key = $coin_name;
        if ($params['chain_name']) {
            $chain_name = strtolower($params['chain_name']);
            //获取公链信息
            $chain_exists = $this->coinService->chain_exists($chain_name);
            if (!$chain_exists) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '公链不存在');
            }
            if ($chain_name !== $coin_name) {
                $key .= '_' . $chain_name;
            }
        }
        $res = $this->myValidator->verify_address($key, $params['address']);
        if ($res === false) {
            return MyQuit::returnMessage(MyCode::ADDRESS_FORMAT_ERROR, '地址格式错误');
        }
        //判断地址释放已经添加
        $is_exists = AddressManager::where(['uid' => $request->uid, 'coin_name' => $coin_name, 'address' => $params['address']])->exists();
        if ($is_exists) {
            return MyQuit::returnMessage(MyCode::ADDRESS_ALREADY_EXISTS, '该地址已经存在');
        }
        //插表
        $params['uid'] = $request->uid;
        $res = AddressManager::insert($params);
        if ($res) {
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        }
        return MyQuit::returnMessage(MyCode::SERVER_ERROR, '服务器错误');
    }

    /**
     * 删除地址
     * @param Request $request
     * @return array
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function del(Request $request)
    {
        $params = $request->params;
        if (empty($params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($params, 'AddressValidator', ['id']);
        $res = AddressManager::where(['id' => $params, 'uid' => $request->uid])->delete();
        if ($res) {
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        }
        return MyQuit::returnMessage(MyCode::ADDRESS_NOT_EXISTS, '地址不存在');
    }

}
