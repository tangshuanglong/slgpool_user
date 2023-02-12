<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Rpc\Lib;

/**
 * Class UserInterface
 *
 * @since 2.0
 */
interface UserInterface
{

    /**
     * 获取所有用户信息
     * @param $uid
     * @return mixed
     */
    public function get_user_all_info($uid);

    /**
     * 用户是否存在
     * @param array $where
     * @return mixed
     */
    public function is_exists(array $where);


}
