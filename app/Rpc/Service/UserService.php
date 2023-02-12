<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Rpc\Service;

use App\Model\Data\UserData;
use App\Model\Entity\UserBasicalInfo;
use App\Rpc\Lib\UserInterface;
use Exception;
use Swoft\Co;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class UserService
 *
 * @since 2.0
 *
 * @Service()
 */
class UserService implements UserInterface
{

    /**
     * 获取用户的所有信息
     * @param $uid
     * @return mixed
     */
    public function get_user_all_info($uid)
    {
        return UserData::get_user_all_info($uid);
    }

    /**
     * 判断数据是否存在
     * @param array $where
     * @return mixed
     */
    public function is_exists(array $where)
    {
        return UserBasicalInfo::where($where)->exists();
    }
}
