<?php
/**
 * @Filename         : ClientTcpConfig.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2020-12-31 12:21
 * @Description      : this is base on Swoole tcp client
 **/

class Client_Tcp_Config {
    
    //const SERVER_HOST = '175.25.22.29';
    const SERVER_HOST = '192.168.3.30';

    const SERVER_PORT = '6001';

    const TIME_OUT = '0.5';

    const CONNECT_TYPE = SWOOLE_SOCK_TCP;
    const CONNECT_KEEP = SWOOLE_KEEP;

    /**
     * response paas data
     * @var array
     */
    public  $response_data = array(
        'type' => array(
            'luci_theme',
            'luci_package',
            'ppip',
            'os',
            'plugins'
        ),
        'action' => array('update', 'add', 'remove', 'cat'),
        'scope' => array('network', 'firewall'),
    );

    /**
     * allow paas action
     * @var array
     */
    public $allow_action = array(
        'luci_theme_update',
        'luci_package_update',
        'ppip_update',
        'os_update',
        'plugins_network_add',
        'plugins_network_special_open',
        'plugins_network_remove',
        'plugins_network_update',
        'plugins_firewall_add',
        'plugins_firewall_remove',
        'plugins_firewall_update'
    );
}
?>