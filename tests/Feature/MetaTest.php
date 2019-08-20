<?php

namespace Tests\Feature;

use Mockery;
use Testgear\DB\RefreshTables;
use Tests\AppTestCase;

class MetaTest extends AppTestCase {

    use RefreshTables;

    protected function setUp() : void {
        parent::setUp();

    }

    public function testMetaCreateLink() {
        $hash = base64_encode($this->faker->name);
        $data = [
            'hash' => $hash,
            'file' => $this->faker->md5,
            'temp' => 0,
            'type' => 'upload'
        ];

        $response = $this->postJson('/meta', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('links', ['hash' => $hash]);
    }

    public function testMetaCreateTemporaryLink() {
        $hash = base64_encode($this->faker->name);
        $data = [
            'hash' => $hash,
            'file' => $this->faker->md5,
            'temp' => 1,
            'type' => 'upload',
            'expire' => '2017-07-21T17:32:28Z'
        ];

        $response = $this->postJson('/meta', $data);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('links', ['hash' => $hash]);
    }

}