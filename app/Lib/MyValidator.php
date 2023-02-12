<?php

namespace App\Lib;

use App\Model\Data\UserData;
use App\Model\Entity\GoogleSecret;
use App\Model\Entity\UserBasicalInfo;
use App\Rpc\Lib\VerifyInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use App\Lib\MyCommon;
use Swoft\Bean\BeanFactory;
use Swoft\Redis\Redis;
use App\Lib\MyAuth;
use App\Lib\MyGA;
use App\Lib\MyRedisHelper;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * 自定义验证规则
 * Class MyValidator
 * @package App\Lib
 *
 * @Bean("MyValidator")
 */
class MyValidator {

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @Inject()
     * @var MyAuth
     */
    private $myAuth;

    /**
     * @Reference(pool="system.pool")
     * @var VerifyInterface
     */
    private $verifyService;

	public function __construct()
	{

	}

    /**
     * 校验账号类型
     *
     * @param string $value
     * @param string $area_code
     * @return bool|string
     */
	public function account_check(string $value, string $area_code)
	{
        if ($this->myCommon->is_email($value)) {
            return 'email';
        }
        if ($this->myCommon->is_mobile($value, $area_code)) {
            return 'mobile';
        }
        return false;
	}

    /**
     * 检测账号可用性
     * 用于注册类验证，账号存在则返回FALSE
     *
     * @param string $account
     * @param string $account_type
     * @return bool
     */
    public function account_exists(string $account, string $account_type): bool
    {
        $where = [$account_type => $account];
        $user_data = UserBasicalInfo::select('id')->where($where)->first();
        return !empty($user_data);
    }

    /**
     * 手机/邮箱验证码校验
     *
     * @param string $account
     * @param string $code
     * @param string $action
     * @return bool
     */
	public function code_verify(string $account, string $code, string $action): bool
	{
        $code_key = $action. '_code_key';
        $data = MyRedisHelper::hget($code_key, $account);
        if (empty($data))
        {
            return false;
        }
        if (time() > $data['create_time'] + (config('code_expire_time', 15)*60)){
            Redis::hDel($code_key, $account);
            return false;
        }
        if ($code != $data['code']){
            return false;
        }
        return Redis::hDel($code_key, $account);
	}

    /**
     * @param string $account
     * @param string $login_type
     * @param string $login_pwd
     * @return bool|mixed|object|\Swoft\Db\Eloquent\Builder|\Swoft\Db\Eloquent\Model|null
     * @throws \Swoft\Db\Exception\DbException
     */
	public function account_verify(string $account, string $login_type, string $login_pwd)
    {
        $user_info = UserBasicalInfo::where([$login_type => $account])->first();
        if (!$user_info){
            return false;
        }
        $verify_pwd = $this->myAuth->password_auth($login_pwd, $user_info['salt'], $user_info['login_pwd']);
        if ($verify_pwd == false){
            return $user_info['id'];
        }
        return $user_info;
    }

    /**
     * @param $uid
     * @param string $password
     * @param string $type
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function password_verify($uid, string $password, string $type = 'login_pwd')
    {
        $user_info = UserBasicalInfo::select($type, 'salt')->where(['id' => $uid])->first();
        if (!$user_info){
            return false;
        }
        return $this->myAuth->password_auth($password, $user_info['salt'], $user_info[$type]);
    }

    /**
     * 验证谷歌验证码
     * @param string $code
     * @param $uid
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
	public function google_verify(string $code, $uid)
	{
        $secret_data = GoogleSecret::where(['uid' => $uid])->first();
		if (!$secret_data){
		    return false;
        }
        /**@var MyGA $myGA */
        $myGA = BeanFactory::getBean('MyGA');
        return $myGA->verifyCode($secret_data['secret'], $code, 0);
	}


    /**
     * 验证所有验证码
     * @param $uid
     * @param $params
     * @param $action
     * @return bool|string|array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function auth_all_verify_code($uid, $params, $action)
    {
        $user_bind_info = UserData::get_bind_info($uid);
        $verify_data = [];
        if ($user_bind_info['google_validator'] == 1){
            //验证谷歌验证码
            $google_verify_code = isset($params['gv_code']) ? $params['gv_code'] : '';
            $res = $this->google_verify($google_verify_code, $uid);
            if (!$res) {
                return MyQuit::returnMessage(MyCode::CAPTCHA_GOOGLE_ERROR, '谷歌验证码错误');
            }
        }
        if ($user_bind_info['mobile_verify'] == 1){
            $mobile_verify_code = isset($params['mv_code']) ? $params['mv_code'] : '';
            //验证手机验证码
            $verify_data['mobile'] = [
                'account' => $user_bind_info['mobile'],
                'code' => $mobile_verify_code,
                'action' => $action,
            ];
        }
        if ($user_bind_info['email_verify'] == 1){
            //验证邮箱验证码
            $email_verify_code = isset($params['ev_code']) ? $params['ev_code'] : '';
            $verify_data['email'] = [
                'account' => $user_bind_info['email'],
                'code' => $email_verify_code,
                'action' => $action,
            ];
        }
        $verify_res = $this->verifyService->verify_all($verify_data);
        $res = true;
        if ($verify_res === 'mobile') {
            $res = MyQuit::returnMessage(MyCode::CAPTCHA_MOBILE_ERROR, '短信验证码错误');
        }elseif ($verify_res === 'email') {
            $res = MyQuit::returnMessage(MyCode::CAPTCHA_EMAIL_ERROR, '邮箱验证码错误');
        }elseif ($verify_res === false) {
            $res = MyQuit::returnMessage(MyCode::PARAM_ERROR, '参数错误');
        }
        return $res;
    }

    /**
     * 验证地址格式
     * @param string $key
     * @param string $address
     * @return bool
     */
    public function verify_address(string $key, string $address): bool
    {
        $preg = config('address_preg.'.$key, '/^(1|3)[a-zA-Z\d]{24,33}$/');
        if (!$preg) {
            return true;
        }
        preg_match($preg, $address, $res);
        if (empty($res)) {
            return false;
        }
        return true;
    }
}
