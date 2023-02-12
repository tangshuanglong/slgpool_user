<?php
namespace App\Console\Command;

use App\Lib\MyCommon;
use App\Lib\MyRedisHelper;
use App\Model\Logic\ApolloLogic;
use Swoft\Apollo\Config;
use Swoft\Bean\BeanFactory;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Consul\Agent;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Consul\Health;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Swoft\Db\DB;
use Swoft\Redis\Redis;
use Swoole\Coroutine\Http\Client;

/**
*@Command()
*/
class ServerCommand{

    /**
     * @Inject()
     *
     * @var Agent
     */
    private $agent;

    /**
     * @Inject()
     *
     * @var Health
     */
    private $health;

    /**
     * @Inject()
     *
     * @var Config
     */
    private $config;

	/**
	*@CommandMapping()
	*/
	public function run()
	{
        \co::set(['max_coroutine' => 10001]);
	    $start = MyCommon::getMillisecond();
	    for ($i = 0; $i < 10000; $i++){
	        go(function (){
                $client = new Client('192.168.0.114', 86);
                $client->setMethod('get');
                $client->execute('/');
                $client->close();
            });

        }
        $end = MyCommon::getMillisecond();
        echo 'total = '.($end - $start).PHP_EOL;
	}

    /**
     *@CommandMapping()
     */
    public function run_curl()
    {
        $start = MyCommon::getMillisecond();
        for ($i = 0; $i < 10000; $i++){
            $curl = curl_init();
            //设置请求的url
            curl_setopt($curl, CURLOPT_URL, '192.168.0.114');
            //指定端口
            curl_setopt($curl, CURLOPT_PORT, '86');
            //设置头文件的信息作为数据流输出
            curl_setopt($curl, CURLOPT_HEADER, 0);
            //设置获取的信息以文件流的形式返回，而不是直接输出。
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // https请求 不验证证书和hosts
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Keep-alive' => 120]);
            curl_setopt($curl, CURLOPT_TIMEOUT, 3);
            //执行命令
            curl_exec($curl);
            //echo curl_getinfo($curl,CURLINFO_HTTP_CODE);
            //关闭URL请求
            curl_close($curl);
        }
        $end = MyCommon::getMillisecond();
        echo 'total = '.($end - $start).PHP_EOL;
    }

    /**
     * @CommandMapping()
     */
    public function test_mycat()
    {
        $data = [
            'uid' => 10017,
            'trade_type_id' => 1,
            'trade_coin_id' => 10,
            'trade_coin_type' => 'usdt',
            'trade_coin_amount' => 100,
            'user_coin_available_balance' => 100,
            'user_coin_frozen_blance' => 100,
            'user_coin_total_balance' => 100,
            'create_time' => date("Y-m-d H:i:s")
        ];
        DB::table('user_amount_log_dw')->insert($data);
    }

    /**
     * @CommandMapping()
     */
    public function test()
    {
        $start = MyCommon::getMillisecond();
        $t = 0;
        for ($i = 0; $i < 100000000; $i++){
            if ('main.currency.market' === 'main.currency.market') {
                $t++;
            }
        }
        $end = MyCommon::getMillisecond();
        echo 'total = '.($end - $start).PHP_EOL;
        echo $t.PHP_EOL;
    }



}
