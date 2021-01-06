<?php
/**
 * @Filename         : ClientTcp.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2020-12-31 12:21
 * @Description      : this is base on Swoole tcp client
 **/
set_time_limit(0);

require_once(__DIR__ . "/ClientMain.php");
require_once(__DIR__ . "/ClientTcpConfig.php");

class Client_Tcp {

    /**
     * @var $tcp_client object
     */
    public $tcp_client;

    /**
     * ip address
     * @var $host string
     */
    public $server_host;

    /**
     * port
     * @var $port int
     */
    public $server_port;

    /**
     * connect type
     * @var $connect_type
     */
    public $connect_type;

    /**
     * request data
     * @var $request_data
     */
    public $request_data;

    /**
     * timeout
     * @var $timeout float
     */
    public $timeout;

    /**
     * Client_Tcp constructor.
     */
    public function __construct()
    {
        $this->set_config();
        $this->send_data();
    }

    /**
     * set client config
     */
    public function set_config()
    {
        $this->server_host = Client_Tcp_Config::SERVER_HOST;
        $this->server_port = Client_Tcp_Config::SERVER_PORT;
        $this->connect_type = Client_Tcp_Config::CONNECT_TYPE;
    }

    /**
     * client main
     */
    public function main()
    {
        go(function() {
            $this->tcp_client = new Client_Main($this->connect_type);
            $this->tcp_client->host = $this->server_host;
            $this->tcp_client->port = $this->server_port;
            $this->tcp_client->data = $this->request_data;
            $this->tcp_client->clientConnect();
            $this->tcp_client->clientSend();
            $this->tcp_client->clientIsConnected();
            $this->tcp_client->clientRecvKeep();
        });
    }

    /**
     * get cpe wan ip
     *
     * @return $wan_ip string
     */
    public function get_wan_ip()
    {
        $shell_wan_command = "ubus call network.interface.wan status | grep " . '\"address\"' . " | grep -oE '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}'";
        $wan_ip = exec($shell_wan_command);
        $local_ip = swoole_get_local_ip();
        $wan_ip = $local_ip['eth1'];
        return $wan_ip;
    }

    /**
     * get wan mac address
     * 
     * @return string
     */
    public function get_wan_mac_address()
    {
        $shell_wan_command = "ifconfig | grep eth1 | awk '{ print $5 }'";
        $wan_mac_address = exec($shell_wan_command);
        $local_mac = swoole_get_local_mac();
        $wan_mac_address = $local_mac['eth1'];
        return $wan_mac_address;
    }

    /**
     * send tcp server data
     */
    public function send_data()
    {
        $data = array();
        $data['ip'] = $this->get_wan_ip();
        $data['status'] = 'online';
        $data['auth_name'] = 'cpe';
        $data['mac_address'] = $this->get_wan_mac_address();
        $this->request_data = $data;
    }
}
$tcp = new Client_Tcp();
$tcp->main();
?>