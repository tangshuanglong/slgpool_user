<?php

namespace App\Process;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\DB;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;

/**
 * Class InviteLogProcess
 * @package App\Process
 * @Bean()
 */
class InviteLogProcess extends UserProcess {


    /**
     * @inheritDoc
     * @throws \Swoft\Db\Exception\DbException
     */
    public function run(Process $process): void
    {
        sleep(1);
        while (1) {
            $data = DB::table('invite_log')->where(['status' => 1])->get()->toArray();
            foreach ($data as $val) {
                //超过半年，失效
                if (($val['create_time'] + 180*86400) < time()) {
                    DB::table('invite_log')->where(['id' => $val['id']])->update(['status' => 0]);
                }
                unset($val);
                sleep(1);
            }
            sleep(60);
        }
    }
}
