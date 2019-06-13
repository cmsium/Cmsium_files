<?php
namespace Queue\Queues;

interface Queue {
    public function push($taskData);
    public function pop();
    public function stats();
    public function destroy();
    public function getName();
}