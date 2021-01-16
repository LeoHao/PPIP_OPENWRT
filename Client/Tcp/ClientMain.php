<?php
/**
 * @Filename         : ClientMain.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2020-12-31 12:21
 * @Description      : this is base on Swoole tcp client
 **/

require_once(__DIR__ . "/TaskService.php");
require_once(__DIR__ . "/ActionService.php");

class ClientMain {

    /**
     * @var \swoole_client
     */
    public $client;

    /**
     * 配置项
     * @var $config array
     */
    public $config;

    /**
     * ip address
     * @var $host string
     */
    public $host;

    /**
     * port
     * @var $port int
     */
    public $port;

    /**
     * timeout
     * @var $timeout float
     */
    public $timeout;

    /**
     * data
     * @var $data json
     */
    public $data;

    /**
     * connect_type
     * @var $connect_type
     */
    public $connect_type;

    /**
     * @var Client
     */
    public static $_worker ;

    /**
     * Client constructor.
     *
     * @param $type SWOOLE_SOCK_TCP SWOOLE_SOCK_UDP
     */
    public function __construct($type)
    {
        $this->connect_type = $type;
    }

    /**
     * get client config
     */
    public function getConfig()
    {
        //TODO
    }

    /**
     * client connect server
     */
    public function clientConnect()
    {
        $this->client = new Swoole\Coroutine\Client($this->connect_type | $this->CONNECT_KEEP);
        if (!$this->client->connect($this->host, $this->port)) {
            echo "connect failed. Error: {$this->client->errCode}\n";
        }
    }

    /**
     * check client is connect
     */
    public function clientIsConnected()
    {
        $this->client->isConnected();
    }

    /**
     * send data to server
     */
    public function clientSend()
    {
        $this->client->send(json_encode($this->data));
    }

    /**
     * response function
     */
    public function clientRecvKeep()
    {
        $this->heartbeat_check();
        while (true) {
            $data = $this->client->recv();
            if (strlen($data) > 0) {
                $data = json_decode($data);
                $response = TaskService::dispose_task($data);
                if (!empty($response)) {
                    $this->data['exec_result'] = $response;
                    $this->client->send(json_encode($this->data));
                }
            } else {
                if ($data === '') {
                    // 全等于空 直接关闭连接
                    $this->client->close();
                    break;
                } else {
                    if ($data === false) {
                        continue;
                    }
                }
            }
        }
    }

    public function heartbeat_check()
    {
        Swoole\Timer::tick(3000000, function () {
            $this->client->send(json_encode(array('CpeStatus' => 'online')));
        });
    }
    /**
     * client set
     */
    public function clientSet()
    {

    }
}
