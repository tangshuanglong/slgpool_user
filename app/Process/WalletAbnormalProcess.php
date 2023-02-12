<?php

namespace App\Process;

use App\Model\Data\WalletData;
use App\Model\Entity\UserAmountChangeLogDw;
use App\Model\Entity\UserAmountChangeLogMining;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoft\Redis\Redis;

/**
 * Class WalletAbnormalProcess
 * @package App\Process
 * @Bean()
 */
class WalletAbnormalProcess extends UserProcess {

    /**
     * @Inject()
     * @var WalletData
     */
    private $walletData;

    /**
     * @inheritDoc
     * @throws \Swoft\Db\Exception\DbException
     */
    public function run(Process $process): void
    {
        sleep(1);
        while (1) {
            $abnormal_users = Redis::hGetAll(config('redis_key.user_wallet_abnormal'));
            foreach ($abnormal_users as $user_id) {
                //读取用户是否有处理异常的记录 如果不存在就清除
                $exists_dw = UserAmountChangeLogDw::where(['uid' => $user_id, 'status_flag' => 2])->exists();
                $exists_mining = UserAmountChangeLogMining::where(['uid' => $user_id, 'status_flag' => 2])->exists();
                if (!$exists_dw && !$exists_mining) {
                    $this->walletData->del_user_wallet_abnormal($user_id);
                }
                sleep(1);
            }
            sleep(60);
        }
    }
}
