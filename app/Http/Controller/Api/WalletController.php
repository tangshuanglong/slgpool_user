<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Lib\MyValidator;
use App\Model\Data\WalletDwData;
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
use App\Validator\WalletValidator;
use Swoft\Validator\Exception\ValidatorException;

/**
 * Class AddressController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/wallet")
 */
class WalletController{

    /**
     * @Inject()
     * @var WalletDwData
     */
    private $walletDwData;

    /**
     * 钱包操作
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function update(Request $request)
    {
        $params = $request->params;
        if (empty($params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '参数缺失');
        }
        validate($params, 'WalletValidator', ['timestamp', 'sign', 'uid', 'coin_id', 'trade_type', 'method', 'amount']);
        if (MyCommon::getMillisecond() - $params['timestamp'] > 3600000) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '请求超时');
        }
        $requestSign = $params['sign'];
        unset($params['sign']);
        $sign = MyCommon::walletGenerateSign($params, config('rpc_app_id'), config('rpc_app_secret'));
        if ($sign != $requestSign) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '签名错误');
        }
        $method = $params['method'];
        $res = $this->walletDwData->$method($params['uid'], $params['amount'], $params['coin_id'], $params['trade_type']);
        if ($res) {
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        }
        return MyQuit::returnMessage(MyCode::BALANCE_ERROR, '余额不足');
    }



}
