<?php
/**
 * @Filename         : TaskService.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2021-1-3 18:35
 * @Description      : this is task dispose
 **/

class TaskService {

    /**
     * @var task_type
     */
    public $task_type;

    /**
     * @var $task_action
     */
    public $task_action;

    /**
     * @var task_scope
     */
    public $task_scope;

    /**
     * analysis response data
     * @param $data
     */
    public static function analysis_data($data) {
        $task_action = empty($data['Action']) ? '':$data['Action'];
        $allow_action = array_merge(ClientTcpConfig::$plugins_action, ClientTcpConfig::$os_action);
        if (!in_array($task_action, $allow_action)) {
            return false;
        }
        //TODO valudate ClientType is paas
        //TODO validate SecretKey is true
        return true;
    }



    /**
     * dispose task
     * @param $data
     * @return array
     */
    public static function dispose_task($data) {
        $result = array();
        if(self::analysis_data($data)) {
            $action_name = $data['Action'];
            $result = ActionService::$action_name($data);
        }
        return $result;
    }

}