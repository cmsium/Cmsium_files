<?php

namespace Queue\Queues;

use PHPUnit\Framework\TestCase;

include_once __DIR__."/../../queues/Queue.php";
include_once __DIR__."/../../queues/SwooleChannel.php";
include_once __DIR__."/../../overflow/Overflow.php";

class SwooleChannelTest extends TestCase {

    protected $queue;
    protected $tasks;

    protected function setUp(): void
    {
        $this->tasks = 10;
        $this->queue = new SwooleChannel("test", $this->tasks);
    }

    protected function tearDown(){
        unset($this->queue);
    }

    public function testDestroy() {
        $this->queue->destroy();
        $this->assertSame(isset($this->queue->chan), false);
    }
//
//    public function testStats() {
//
//    }

    public function pushAndPopProvider() {
        return [
            [["name" => "nick", "age" => 18]],
            [["key" => "value"]],
            ["value"],
            [18]
        ];
    }

    /**
     * @dataProvider pushAndPopProvider
     */
    public function testPushAndPop($data) {
        $this->assertSame($this->queue->push($data), true);
        $this->assertSame($this->queue->pushCount, 1);

        $result = $this->queue->pop();
        $this->assertSame($this->queue->popCount, 1);
        $this->assertSame($result, $data);
    }


    public function testGetName() {
        $this->assertSame($this->queue->getName(), 'test');
    }

}
