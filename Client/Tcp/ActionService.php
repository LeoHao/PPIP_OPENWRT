<?php
/**
 * @Filename         : ActionService.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2021-1-17 01:03
 * @Description      : dispose action
 **/

define('ROOT_PATH' , dirname(dirname(dirname(__FILE__))));
define('SH_PATH' , dirname(dirname(dirname(__FILE__))) . '/Command/NetWork/');
define('CONF_PATH' , dirname(dirname(dirname(__FILE__))) . '/Command/Config/');
class ActionService
{

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
		$exec_str = "sh " . SH_PATH . $sh_file . " " . $params_str;
		self::resetNetworkConf($special_conf['vpn_name']);
		$output = exec($exec_str);
		self::setIpRouteForSpecial(ucfirst($special_conf['vpn_name']), $special_conf['local_wan_ip']);
		$reload_str = "/etc/init.d/network reload";
		exec($reload_str);
		return $output;
	}

	/**
	 * plugins_network_webside_open
	 * @param $data
	 * @return string $output
	 */
	public static function plugins_network_webside_open($data)
	{
		if (empty($data['ActionExt'])) {
			return array();
		}
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
		$domain = $data['ActionExt']['Domain'];
		if (in_array('', $special_conf)) {
			return array();
		}
		$params_str = implode(" ", $special_conf);
		$exec_str = "sh " . SH_PATH . $sh_file . " " . $params_str;
		self::resetNetworkConf('webside');
		$output = exec($exec_str);
		self::setIpRouteForWebSide('Webside', $special_conf['account_remote_address'], $special_conf['local_wan_ip'], ucfirst($domain));
		$reload_str = "/etc/init.d/network reload";
		exec($reload_str);
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
		foreach ($action_array as $word) {
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

	/**
	 * get_local_gateway
	 * @return string|string[]
	 */
	public static function get_local_gateway($type)
	{
		$gateway = '';
		$uci_search = "uci show network.$type.ipaddr";
		$uci_info = exec($uci_search);
		if ($uci_info) {
			$ip_info = explode("=", $uci_info);
			$ip_addr = str_replace('\'', '', $ip_info[1]);
			$ip_addr_arr = explode(".", $ip_addr);
			array_pop($ip_addr_arr);
			array_push($ip_addr_arr, 1);
			$gateway = implode(".", $ip_addr_arr);
		}
		return $gateway;
	}

	/**
	 * setIpRouteForType
	 * @param $type
	 * @param $route_ip
	 */
	public static function setIpRouteForSpecial($type, $route_ip)
	{
		$route_file = CONF_PATH . $type . '/' . $type.'Route.db';
		$uci_file =  CONF_PATH . $type . '/' . $type.'Uci.db';
		$handle = fopen($route_file, "r");
		$contents = fread($handle, filesize($route_file));
		$ip_router = explode("|", $contents);

		$ip_addr_arr = explode(".", $route_ip);
		array_pop($ip_addr_arr);
		array_push($ip_addr_arr, 1);
		$gateway = implode(".", $ip_addr_arr);
		$type_conf = fopen($uci_file, "w") or die("Unable to open file!");
		foreach ($ip_router as $key => $single_ip) {
			$txt = "config route 'ppip_special_route$key'\n";
			fwrite($type_conf, $txt);
			$txt = "\toption interface 'wan'\n";
			fwrite($type_conf, $txt);
			$txt = "\toption target '$single_ip'\n";
			fwrite($type_conf, $txt);
			$txt = "\toption gateway '$gateway'\n";
			fwrite($type_conf, $txt);
			$txt = "\n";
			fwrite($type_conf, $txt);
		}
		fclose($type_conf);
		$uci_handle = fopen($uci_file, "r");
		$uci_conf = fread($uci_handle, filesize($uci_file));
		$network_conf_path = "/etc/config/network";
		$network_conf_handle = fopen($network_conf_path, "r");
		$network_conf = fread($network_conf_handle, filesize($network_conf_path));
		$network_conf_arr = explode("config route 'ppip_special_route0'", $network_conf);
		$new_network_conf[] = $network_conf_arr[0];
		$new_network_conf[] = $uci_conf;
		$new_network_conf = implode("\n", $new_network_conf);
		$write_network_conf = fopen($network_conf_path, "w") or die("Unable to open file!");
		fwrite($write_network_conf, $new_network_conf);
	}

	/**
	 * setIpRouteForWebSide
	 * @param $type
	 * @param $route_ip
	 */
	public static function setIpRouteForWebSide($type, $route_ip, $local_gateway, $domain)
	{
		$route_file = CONF_PATH . $type . '/' . $domain.'Route.db';
		$uci_file =  CONF_PATH . $type . '/' . $domain.'Uci.db';
		$handle = fopen($route_file, "r");
		$contents = fread($handle, filesize($route_file));
		$ip_router = explode("|", $contents);

		$ip_addr_arr = explode(".", $route_ip);
		array_pop($ip_addr_arr);
		array_push($ip_addr_arr, 1);
		$gateway = implode(".", $ip_addr_arr);

		$local_gateway = explode(".", $local_gateway);
		array_pop($local_gateway);
		array_push($local_gateway, 1);
		$local_gateway = implode(".", $local_gateway);

		$type_conf = fopen($uci_file, "w") or die("Unable to open file!");
		$local_gateway_key = 0;
		foreach ($ip_router as $key => $single_ip) {
			$txt = "config route 'ppip_webside_route$key'\n";
			fwrite($type_conf, $txt);
			$txt = "\toption interface 'wan'\n";
			fwrite($type_conf, $txt);
			$txt = "\toption target '$single_ip'\n";
			fwrite($type_conf, $txt);
			$txt = "\toption gateway '$gateway'\n";
			fwrite($type_conf, $txt);
			$txt = "\n";
			fwrite($type_conf, $txt);
			$local_gateway_key = $key;
		}

		$local_gateway_key++;
		$txt = "config route 'ppip_webside_route$local_gateway_key'\n";
		fwrite($type_conf, $txt);
		$txt = "\toption interface 'wan'\n";
		fwrite($type_conf, $txt);
		$txt = "\toption target '0.0.0.0/0'\n";
		fwrite($type_conf, $txt);
		$txt = "\toption gateway '$local_gateway'\n";
		fwrite($type_conf, $txt);
		$txt = "\n";
		fwrite($type_conf, $txt);

		fclose($type_conf);
		$uci_handle = fopen($uci_file, "r");
		$uci_conf = fread($uci_handle, filesize($uci_file));
		$network_conf_path = "/etc/config/network";
		$network_conf_handle = fopen($network_conf_path, "r");
		$network_conf = fread($network_conf_handle, filesize($network_conf_path));
		$network_conf_arr = explode("config route 'ppip_webside_route0'", $network_conf);
		$new_network_conf[] = $network_conf_arr[0];
		$new_network_conf[] = $uci_conf;
		$new_network_conf = implode("\n", $new_network_conf);
		$write_network_conf = fopen($network_conf_path, "w") or die("Unable to open file!");
		fwrite($write_network_conf, $new_network_conf);
	}

	public static function resetNetworkConf($type)
	{
		$network_conf_path = "/etc/config/network";
		$network_conf_handle = fopen($network_conf_path, "r");
		$network_conf = fread($network_conf_handle, filesize($network_conf_path));
		$network_conf_arr = explode("config route 'ppip_" . $type . "_route0'", $network_conf);
		$write_network_conf = fopen($network_conf_path, "w") or die("Unable to open file!");
		fwrite($write_network_conf, $network_conf_arr[0]);
	}
}
