<?php

namespace App\Rpc\Service;
use App\Rpc\Lib\IdentityInterface;
use Swoft\Db\DB;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class IdentityService
 * @package App\Rpc\Service
 * @Service()
 */
class IdentityService implements IdentityInterface{

    /**
     * 是否身份认证
     * @param int $uid
     * @return bool
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function is_identity_auth(int $uid)
    {
        return DB::table('identity_auth_log')->where(['uid' => $uid])->exists();
    }


}
