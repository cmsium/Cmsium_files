<?php

namespace Queue\Queues;

use PHPUnit\Framework\TestCase;
use Queue\Exceptions\PushErrorException;
use Queue\Overflow\Overflow;
use Queue\Tasks\TestTask;

include_once __DIR__."/../../queues/Queue.php";
include_once __DIR__."/../../queues/SwooleTable.php";
include_once __DIR__."/../../tasks/Task.php";
include_once __DIR__."/../../tasks/TestTask.php";
include_once __DIR__."/../../overflow/Overflow.php";
include_once __DIR__."/../../exceptions/PushErrorException.php";

class SwooleTableTest extends TestCase {

    protected $queue;
    protected $tasks;

    protected function setUp(): void
    {
        $this->tasks = 10;
        $this->queue = new SwooleTable("test", $this->tasks, TestTask::$structure);
    }

    protected function tearDown(){
        unset($this->queue);
    }

    /**
     * @dataProvider pushProvider
     */
    public function testPushAndPop($data, $expect) {
        $this->assertSame($this->queue->push($data), true);
        $this->assertSame($this->queue->pushCount, 1);
        $this->assertSame($this->queue->tail, 1);
        $this->assertSame($this->queue->table[0]->value, $expect);

        $result = $this->queue->pop();
        $this->assertSame($this->queue->popCount, 1);
        $this->assertSame($this->queue->head, 1);
        $this->assertSame($result, $expect);
    }


    public function pushProvider() {
        return [
            [["name" => "nick", "age" => 18], ["name" => "nick", "age" => 18]],
            [["name" => "nick", "age" => 18, "some" => "else"], ["name" => "nick", "age" => 18]],
            [["name" => "nick"], ["name" => "nick", "age" => 0]],
            [["age" => 18], ["name" => "", "age" => 18]],
            [["name" => "abcdefghijklmnop", "age" => 18], ["name" => "abcdefgh", "age" => 18]]
        ];
    }


    public function testGetName() {
        $this->assertSame($this->queue->getName(), 'test');
    }

    public function testStats() {
        $data = ["name" => "nick", "age" => 18];
        $this->queue->push($data);
        $this->queue->push($data);
        $stats = $this->queue->stats();
        $this->assertSame($stats, ['queue_num' => 2, 'head' => 0, 'tail' => 2]);

        $this->queue->pop($data);
        $stats = $this->queue->stats();
        $this->assertSame($stats, ['queue_num' => 1, 'head' => 1, 'tail' => 2]);
    }

    public function testDestroy() {
        $this->queue->destroy();
        $this->assertSame(isset($this->queue->table), false);
    }

    public function overflowPushProvider() {
        return [
            [1000, 100, 22],
            [1000, 200, 46],
            [1000, 300, 111],
            [1000, 1000, 586],
        ];
    }

    public function testOverflowWithError() {
        $this->expectException(PushErrorException::class);
        $data = ["name" => "nick", "age" => 18];
        $overflow = $this->createMock(Overflow::class);
        $overflow->method('check')
            ->willReturn(true);
        $overflow->method('invokeCallback')
            ->willReturn(true);
        $overflow->method('resolveOverflow')
            ->will($this->throwException(new PushErrorException()));
        $this->queue->overflow = $overflow;

        for ($i = 0; $i < $this->tasks * 100; $i++) {
            $this->queue->push($data);
        }
    }

    /**
     * @dataProvider overflowPushProvider
     */
    public function testOverflowWithPush($limit, $excess, $expect) {
        $this->queue = new SwooleTable("test", $limit, TestTask::$structure);
        $overflow = $this->createMock(Overflow::class);
        $overflow->method('check')
            ->willReturn(true);
        $overflow->method('invokeCallback')
            ->willReturn(true);
        $overflow->method('resolveOverflow')
            ->will($this->returnCallback(function ($queue, $task) {
                $queue->pop();
                return $queue->fpush($task);
            }));
        $this->queue->overflow = $overflow;

        for ($i = 0; $i < $limit + $excess; $i++) {
            $data = ["name" => "nick", "age" => $i];
            $this->queue->push($data);
        }
        $this->assertSame($this->queue->pop(), ['name' => 'nick', 'age' => $expect]);
    }

}
