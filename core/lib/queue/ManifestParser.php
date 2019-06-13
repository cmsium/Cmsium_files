<?php
namespace Queue;

use Queue\Exceptions\ManifestNotFound;
use Queue\Exceptions\WrongQueueException;

class ManifestParser {

    public $manifest;

    public function __construct($manifest_path = ROOTDIR.'/config/queues.json') {
        $str = file_get_contents($manifest_path);
        if ($str !== null) {
            $this->manifest = json_decode($str);
        } else {
            throw new ManifestNotFound();
        }
    }

    public function getQueues() {
        $queues=[];
        foreach ($this->manifest as $name => $queue){
            if ($this->checkStatus($queue)){
                $queues[$name] = $queue;
            }
        }
        return $queues;
    }

    public function checkQueue($name) {
        if (!property_exists($this->manifest, $name)){
            throw new WrongQueueException();
        }
        return $this->manifest->$name;
    }

    public function getQueue($name) {
        $queue = $this->checkQueue($name);
        $queue->name = $name;
        if (isset($queue->task_structure)){
            $queue->task_structure = $this->getTaskStructure($queue);
        }
        if (isset($queue->overflow)){
            $queue->overflow = $this->getObject($queue->overflow, "\Queue\Overflow\\");
            //TODO callback;
        }
        return $this->getObject($queue, "\Queue\Queues\\");
    }

    public function getTaskStructure($queue) {
        $class = "\Queue\Tasks\\$queue->task_structure";
        return $class::$structure;
    }

    public function getObject($manifest_obj, $namespace) {
        $type = $manifest_obj->type;
        $class = $namespace.$type;
        @$reflection = new \ReflectionClass($class);
        $params = $reflection->getConstructor()->getParameters();
        $args=[];
        foreach ($params as $param){
            $name = $param->name;
            if (isset($manifest_obj->$name)){
                $manifest_param = $manifest_obj->$name;
            } else {
                $manifest_param = null;
            }
            $args[$name] = $manifest_param;
        }
        $args = array_values($args);
        $obj = new $class(...$args);
        return $obj;
    }

    public function checkStatus($queue) {
        if (isset($queue->status))
            return $queue->status != 'disabled';
        else
            return true;
    }

}