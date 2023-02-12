<?php

namespace App\Rpc\Service;


use App\Model\Entity\RewardLog;
use App\Rpc\Lib\RewardLogInterface;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class RewardLogService
 * @package App\Rpc\Service
 * @Service()
 */
class RewardLogService implements RewardLogInterface {

    /**
     * @param array $data
     * @return bool
     */
    public function insert_reward_log(array $data)
    {
        return RewardLog::insert($data);
    }

}
