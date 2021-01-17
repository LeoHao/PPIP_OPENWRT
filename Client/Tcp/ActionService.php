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
        $special_conf["connect_type"] = isset($data['ActionExt']['ConnectType']) ? strtolower($data['ActionExt']['ConnectType']) : 'l2tp';
        $special_conf["node_ip"] = isset($data['ActionExt']['NodeIp']) ? $data['ActionExt']['NodeIp'] : '';
        $special_conf["account_name"] = isset($data['ActionExt']['AccountName']) ? $data['ActionExt']['AccountName'] : '';
        $special_conf["account_pwd"] = isset($data['ActionExt']['AccountPwd']) ? $data['ActionExt']['AccountPwd'] : '';
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
}
