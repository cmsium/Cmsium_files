<?php

namespace Queue\Queues;

use PHPUnit\Framework\TestCase;
use Queue\Overflow\Overflow;

include_once __DIR__."/../../queues/Queue.php";
include_once __DIR__."/../../queues/Redis.php";
include_once __DIR__."/../../overflow/Overflow.php";


class RedisTest extends TestCase {

    protected $tasks;
    protected $queue;
    protected $data;

    protected function setUp(): void
    {
        $this->tasks = 10;
        $this->queue = new Redis("test", $this->tasks, '127.0.0.1', 6379);
        $this->data['test'] = [];
        $stub = $this->createMock(\Redis::class);
        $stub->method('connect')
            ->willReturn(true);
        $stub->method('llen')
            ->will($this->returnCallback(function ($name) {
                return count($this->data[$name]);
            }));
        $stub->method('rpush')
            ->will($this->returnCallback(function ($name, $data) {
                $this->data[$name][] = $data;
                return true;
            }));
        $stub->method('lpop')
            ->will($this->returnCallback(function ($name) {
               return  array_shift($this->data[$name]);
            }));
        $stub->method('delete')
            ->will($this->returnCallback(function ($name) {
                $this->data[$name] = [];
            }));
        $this->queue->conn = $stub;
    }

    protected function tearDown(){
        unset($this->queue);
        unset($this->data);
    }

    public function testGetLen() {
        $this->assertSame($this->queue->getLen(), 0);
        $data = ['name' => "nick", 'age' => 18];
        $this->queue->push($data);
        $this->assertSame($this->queue->getLen(), 1);
        $this->queue->pop();
        $this->assertSame($this->queue->getLen(), 0);
    }


    public function testGetName() {
        $this->assertSame($this->queue->getName(), 'test');
    }

    public function testStats() {
        $this->assertSame($this->queue->stats(), ['queue_num' => 0]);
        $data = ['name' => "nick", 'age' => 18];
        $this->queue->push($data);
        $this->assertSame($this->queue->stats(), ['queue_num' => 1]);
        $this->queue->pop();
        $this->assertSame($this->queue->stats(), ['queue_num' => 0]);
    }

    /**
     * @dataProvider pushProvider
     */
    public function testPushAndPop($data, $expect) {
        $this->assertSame($this->queue->push($data), true);
        $this->assertSame($this->queue->pushCount, 1);
        $this->assertSame(json_decode($this->data['test'][0], true), $expect);

        $result = $this->queue->pop();
        $this->assertSame($this->queue->popCount, 1);
        $this->assertSame($result, $expect);
    }

    public function pushProvider() {
        return [
            [["name" => "nick", "age" => 18], ["name" => "nick", "age" => 18]],
            [["key" => "value"], ["key" => "value"]],
            ["value", "value"],
            [18, 18]
        ];
    }
}
