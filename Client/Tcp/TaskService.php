<?php
/**
 * @Filename         : TaskService.php
 * @Author           : LeoHao
 * @Email            : blueseamyheart@hotmail.com
 * @Last modified    : 2021-1-3 18:35
 * @Description      : this is task dispose
 **/

class Task_Service {

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
     * @var config
     */
    public $config;
    /**
     * Task_Service constructor.
     * @param $data paas response
     */
    public function __construct($data) {
        $this->config = new Client_Tcp_Config();
        $data = $this->object_to_array($data);
        $this->analysis_data($data);
    }

    /**
     * analysis response data
     * @param $data
     */
    public function analysis_data($data) {
        $this->task_action = empty($data['action']) ? '':$data['action'];
        if (!in_array($this->task_action, $this->config->allow_action)) {
            throw new Exception("this action don't allow");
        }
    }

    /**
     * object as array
     * @param $array
     * @return array|mixed
     */
    public function object_to_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        }
        if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = $this->object_to_array($value);
            }
        }
        return $array;
    }

    public function dispose_task() {
        $output = exec("sh /usr/lib/lua/plugins/" . $this->task_action . ".sh");
        return $output;
    }
}
?>