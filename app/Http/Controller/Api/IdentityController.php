<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Model\Data\ConfigData;
use App\Model\Data\UserData;
use App\Model\Entity\IdentityLog;
use App\Model\Entity\UserBasicalInfo;
use App\Rpc\Lib\AuthInterface;
use App\Rpc\Lib\CountryCodeInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use App\Validator\IdentityValidator;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Redis\Redis;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use App\Lib\MyPushQueue;

/**
 * Class User
 * @package App\Lib
 * @Controller(prefix="/v1/identity")
 * @Middlewares({
 *     @Middleware(AuthMiddleware::class)
 * })
 */
class IdentityController
{

    /**
     * @Reference(pool="system.pool")
     *
     * @var CountryCodeInterface
     */
    private $countryCodeService;

    /**
     * @Reference(pool="auth.pool")
     *
     * @var AuthInterface
     */
    private $authService;

    /**
     * @Inject()
     *
     * @var MyCommon
     */
    private $myCommon;

    /**
     * 普通认证
     * @param Request $request
     * @return array
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function common_cert(Request $request)
    {
        $params = $request->post();
        //验证参数
        validate($params, 'IdentityValidator', ['country_id', 'real_name', 'identity_number']);
        //验证国籍id
        $country_info = $this->countryCodeService->get_country_code(['country_id' => $params['country_id']]);
        if (!$country_info) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '国家错误');
        }
        //验证身份证格式
        $is_identity = $this->myCommon->is_identity($params['identity_number'], $country_info['name_en']);
        if ($is_identity === false) {
            return MyQuit::returnMessage(MyCode::IDENTITY_FORMAT_ERROR, '身份证格式错误');
        }
        //查询身份证是否已经认证
        $is_exists = IdentityLog::where(['identity_number' => $params['identity_number']])->whereIn('status_flag', [0, 1, 3, 4])->exists();
        if ($is_exists) {
            return MyQuit::returnMessage(MyCode::IDENTITY_EXIST, '该身份认证已经存在');
        }
        DB::beginTransaction();
        try {
            //插入数据表
            $params['uid'] = $request->uid;
            $params['country_name_cn'] = $country_info['name_cn'];
            $params['country_name_en'] = $country_info['name_en'];
            $params['status_flag'] = 1;
            $res = IdentityLog::insert($params);
            if (!$res) {
                throw new DbException('新增认证记录失败');
            }
            //直接审核通过
            $res = UserBasicalInfo::where(['id' => $request->uid])->update(['real_name_cert' => 1]);
            DB::commit();
            if($res){
                //重置缓存信息
                $this->authService->reset_user_all_info($request->uid);
            }else{
                throw new DbException('用户认证失败');
            }
            return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
        } catch (DbException $e) {
            DB::rollBack();
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * 高级认证
     * @param Request $request
     * @return array
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::POST})
     */
    public function advanced_cert(Request $request)
    {
        $params = $request->post();
        //验证参数
        validate($params, 'IdentityValidator', ['identity_front', 'identity_reverse']);
        //查询身份证是否已经认证
        $is_exists = IdentityLog::where(['uid' => $request->uid])->whereIn('status_flag', [3, 4])->exists();
        if ($is_exists) {
            return MyQuit::returnMessage(MyCode::IDENTITY_EXIST, '该身份认证已经存在');
        }
        $identity_front = MyCommon::get_filename($params['identity_front']);
        if (!$identity_front) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '身份正面错误');
        }
        $identity_reverse = MyCommon::get_filename($params['identity_reverse']);
        if (!$identity_reverse) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '身份反面错误');
        }
        //更新认证记录
        $IdentityLog = IdentityLog::where(['uid' => $request->uid])->whereIn('status_flag', [1, 5])->first();
        if (!$IdentityLog) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '请先完成普通认证');
        }
        $IdentityLog->setIdentityFront($identity_front);
        $IdentityLog->setIdentityReverse($identity_reverse);
        $IdentityLog->setStatusFlag(3);
        $res = $IdentityLog->save();
        if (!$res) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '更新认证记录失败');
        }
        //发送审核短信
        [$config_json] = ConfigData::getConfigValue('system', 'sms_mobile');
        $config = json_decode($config_json, true);
        $config_data = [
            'area_code' => $config[0],
            'mobile' => $config[1],
        ];
        $send_data = ['身份'];
        $this->myCommon->push_notice_queue($config_data['mobile'], $config_data['area_code'], 'audit_temp_id', '', $send_data);
        return MyQuit::returnMessage(MyCode::SUCCESS, 'success');
    }

    /**
     * 获取身份信息
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get(Request $request)
    {
        $data = DB::table('identity_log')
            ->select('country_name_cn', 'country_name_en', 'real_name', 'identity_number', 'status_flag')
            ->where(['uid' => $request->uid])
            ->orderByDesc('id')
            ->limit(1)
            ->firstArray();
        if ($data) {
            $len = strlen($data['identity_number']);
            $end_len = $len - 4;
            $cipher = 0;
            if ($end_len > 10) {
                $cipher = $end_len - 5;
            }
            $data['identity_number'] = $this->myCommon->cipher($data['identity_number'], 2, $end_len, $cipher);
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
