<?php
/**
 * @Filename         : ActionService.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2021-1-17 01:03
 * @Description      : dispose action
 **/

define('ROOT_PATH' , dirname(dirname(dirname(__FILE__))));

class ActionService {

	/**
	 * plugins network special open
	 * @param $data
	 * @return string $output
	 */
	public static function plugins_network_special_open($data)
	{
		if (empty($data['ActionExt'])) {
			return array();
		}
		$sh_path = ROOT_PATH . '/Command/NetWork/';
		$sh_file = self::getActionToShName($data['Action']);
		$special_conf = array();
		$special_conf['connect_type'] = isset($data['ActionExt']['ConnectType']) ? strtolower($data['ActionExt']['ConnectType']) : 'l2tp';
		$special_conf['node_ip'] = isset($data['ActionExt']['NodeIp']) ? $data['ActionExt']['NodeIp'] : '';
		$special_conf['account_name'] = isset($data['ActionExt']['AccountName']) ? $data['ActionExt']['AccountName'] : '';
		$special_conf['account_pwd'] = isset($data['ActionExt']['AccountPwd']) ? $data['ActionExt']['AccountPwd'] : '';
		$special_conf['account_remote_address'] = isset($data['ActionExt']['AccountRemoteAddress']) ? $data['ActionExt']['AccountRemoteAddress'] : '';
		$special_conf['vpn_name'] = 'special';
		$wan_info = self::get_wan_info();
		$special_conf['local_wan_physical'] = $wan_info[0];
		$special_conf['local_wan_ip'] = $wan_info[1];
		$lan_gateway = self::get_lan_gateway();
		$special_conf['local_lan_gateway'] = $lan_gateway;
		$special_conf['nat_type'] = $special_conf['connect_type'] . '-' . $special_conf['vpn_name'];
		if (in_array('', $special_conf)) {
			return array();
		}
		$params_str = implode(" ", $special_conf);
		$exec_str = "sh " . $sh_path . $sh_file . " " . $params_str;
		$output = exec($exec_str);
		return $output;
	}

	/**
	 * get action to sh name
	 * @param $action_name
	 * @return string function_name
	 */
	public static function getActionToShName($action_name)
	{
		$action_array = explode("_", $action_name);
		$first_word = array_shift($action_array);
		$function_name = array();
		array_push($function_name, $first_word);
		foreach ($action_array as $word){
			array_push($function_name, ucfirst(strtolower($word)));
		}
		$function_name = implode("", $function_name);
		$function_name .= '.sh';

		return $function_name;
	}

	/**
	 * get cpe wan ip
	 *
	 * @return array $wan_info
	 */
	public static function get_wan_info()
	{
		$uci_search_wan = "uci show network.wan.ifname";
		$wan_ifname = exec($uci_search_wan);
		if ($wan_ifname) {
			$ifname = explode("=", $wan_ifname);
			$local_ip = swoole_get_local_ip();
			$wan_name = str_replace('\'', '', $ifname[1]);
			$wan_ip = $local_ip[$wan_name];
		}
		$wan_info = array($wan_name, $wan_ip);
		return $wan_info;
	}

	/**
	 * get_lan_ip
	 * @return string|string[]
	 */
	public static function get_lan_ip()
	{
		$lan_ip = '';
		$uci_search_lan = "uci show network.lan.ipaddr";
		$lan_ipaddr = exec($uci_search_lan);
		if ($lan_ipaddr) {
			$lan_info = explode("=", $lan_ipaddr);
			$lan_ip = str_replace('\'', '', $lan_info[1]);
		}
		return $lan_ip;
	}

	/**
	 * get_lan_gateway
	 * @return string|string[]
	 */
	public static function get_lan_gateway()
	{
		$lan_gateway = '';
		$uci_search_lan = "uci show network.lan.ipaddr";
		$lan_ipaddr = exec($uci_search_lan);
		if ($lan_ipaddr) {
			$lan_info = explode("=", $lan_ipaddr);
			$lan_ip = str_replace('\'', '', $lan_info[1]);
			$lan_ip_arr = explode(".", $lan_ip);
			array_pop($lan_ip_arr);
			array_push($lan_ip_arr, 0);
			$lan_gateway = implode(".", $lan_ip_arr);
		}
		return $lan_gateway;
	}
}
