<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Console\Command;

use App\Lib\MyCommon;
use Swoft\Apollo\Config;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Co;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Exception\SwoftException;
use Swoft\Log\Helper\CLog;
use Swoft\Stdlib\Helper\JsonHelper;
use Swoft\WebSocket\Server\WebSocketServer;
use Throwable;

/**
 * Class AgentCommand
 *
 * @since 2.0
 *
 * @Command("agent", desc="this is an agent for Apllo config center")
 */
class AgentCommand
{
    /**
     * @Inject()
     *
     * @var Config
     */
    private $config;

    /**
     * @Inject()
     *
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @CommandMapping(name="index")
     */
    public function index(): void
    {
        $namespaces = [
            'base'
        ];

        while (true) {
            try {
                $this->config->listen($namespaces, [$this, 'updateConfigFile']);
            } catch (Throwable $e) {
                CLog::error('Config agent fail(%s %s %d)!', $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }
    }

    /**
     * @CommandMapping()
     */
    public function up_code(): void
    {
        $namespaces = [
            'MyCode'
        ];

        while (true) {
            try {
                $this->config->listen($namespaces, [$this, 'updateFile']);
            } catch (Throwable $e) {
                CLog::error('Config agent fail(%s %s %d)!', $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }
    }

    /**
     * @param array $data
     */
    public function updateFile(array $data)
    {
        foreach ($data as $namespace => $namespaceData) {
            $configFile = sprintf('@app/lib/%s.php', $namespace);

            $configKVs = $namespaceData['configurations'] ?? '';
            asort($configKVs);
            $content   = "<?php \n\n namespace App\lib; \n\n/**
    *   功能模块状态码
    *   基础类别状态码为0000-1000，统一4位，不足4位前面补零
    *   法币交易功能状态码为2000-2999
    *   币币交易功能状态码为3000-3999
    *   用户信息功能状态码为4000-4999
    *   登录，注册，修改密码，google验证码，二步登录，交易密码等安全类别统一用5000-5999
    *   其他功能类别统一用7000-7999
    *   8000以后的留着备用，以后有后续功能可以往后添加
    **/ \n class MyCode{ \n\n";
            foreach ($configKVs as $key => $val) {
                $content .= "    CONST ".$key." = '".$val."';\n\n";
            }
            $content   .= "\n}";
            Co::writeFile(alias($configFile), $content, FILE_NO_DEFAULT_CONTEXT);

            CLog::info('MyCode update success！');

//            /** @var HttpServer $server */
//            $server = bean('httpServer');
//            $server->restart();
//
//            /** @var ServiceServer $server */
//            $server = bean('rpcServer');
//            $server->restart();

            /** @var WebSocketServer $server */
//            $server = bean('wsServer');
//            $server->restart();
        }
    }

    /**
     * @param array $data
     *
     * @throws SwoftException
     */
    public function updateConfigFile(array $data): void
    {
        foreach ($data as $namespace => $namespaceData) {
            $configFile = sprintf('@config/%s.php', $namespace);

            $configKVs = $namespaceData['configurations'] ?? '';
            foreach ($configKVs as $key => $val) {
                $data = json_decode($val, true);
                if (JSON_ERROR_NONE === json_last_error()) {
                    $configKVs[$key] = $data;
                }
                unset($val, $data, $key);
            }
            $content   = '<?php return ' . var_export($configKVs, true) . ';';
            Co::writeFile(alias($configFile), $content, FILE_NO_DEFAULT_CONTEXT);

            CLog::info('Apollo update success！');

//            /** @var HttpServer $server */
//            $server = bean('httpServer');
//            $server->restart();
//
//            /** @var ServiceServer $server */
//            $server = bean('rpcServer');
//            $server->restart();

            /** @var WebSocketServer $server */
//            $server = bean('wsServer');
//            $server->restart();
        }
    }
}
