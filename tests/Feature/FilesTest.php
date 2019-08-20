<?php
namespace Tests\Feature;

use App\AppFile;
use App\Link;
use Mockery;
use Testgear\DB\RefreshTables;
use Tests\AppTestCase;

class FilesTest extends AppTestCase {

    use RefreshTables;

    public $tmp_storage_path = ROOTDIR . '/storage_tmp';
    public $test_storage_path = ROOTDIR . '/storage_test';

    protected function setUp() : void {
        parent::setUp();

        //mock controller client
        $controllerRequestMock = Mockery::mock('overload:App\ControllerClient');
        $controllerRequestMock
            ->shouldReceive('connect')
            ->andReturn(true);
        $controllerRequestMock
            ->shouldReceive('deleteFile')
            ->andReturn(true);

        //mock queue client
        $controllerRequestMock = Mockery::mock('overload:Queue\Producers\Producer');
        $controllerRequestMock
            ->shouldReceive('send')
            ->andReturn(true);

        //create test storage for files
        mkdir($this->tmp_storage_path, 0777);
        mkdir($this->test_storage_path, 0777);
    }

    protected function tearDown(): void {
        parent::tearDown();
        //clear test file storage
        $this->clearDir($this->test_storage_path);
        $this->clearDir($this->tmp_storage_path);
    }

    private function clearDir($dir){
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it,
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    public function testFilesStatus() {
        $data = [
            "status" => true,
            "space" => $this->faker->randomNumber(8),
            "workload" => $this->faker->randomNumber(4)
        ];
        $status_mock = Mockery::mock('overload:App\Status');
        $status_mock
            ->shouldReceive('get')
            ->andReturn($data);

        $response = $this->getJson("/status");
        $response->assertStatus(200);
        $response->assertJson($data);
    }

    public function testUploadFile() {
        //create upload link
        $hash = base64_encode($this->faker->name);
        $link_data = [
            'hash' => $hash,
            'file' => $this->faker->md5,
            'temp' => 0,
            'type' => 'upload'
        ];
        $link = new Link($link_data, app()->links);
        $link->swooleSave();
        $link->dbSave();

        $config = \Config\ConfigManager::module('app');
        $url = $config->get('host_url');

        //create test image of cat (it is important to use exactly cat images!)
        $path = $this->faker->image($this->tmp_storage_path , 640,  480, 'cats');
        $name = @end(explode('/', $path));
        $data = [
            'file' => $name,
        ];
        app()->test_files = [
            'file' => [
                'name' => $name,
                'tmp_name' => $path,
                'type' => 'image/jpg',
                'size' => 1234567
            ]
        ];

        //upload file
        $response = $this->postJson('/file/'.$link_data['hash'], $data);

        $response->assertStatus(200);
        $response->assertJson([
            'url' => "http://$url/file/{$link_data['hash']}",
            'id' => $link_data['file']
        ]);
        $this->assertDatabaseHas('files', ['file_id' => $link_data['file']]);
        $this->assertDatabaseHas('links', ['hash' => $link_data['hash'], 'type' => 'read']);
        //file exists
        $this->assertEquals(1, iterator_count(new \FilesystemIterator($this->test_storage_path , \FilesystemIterator::SKIP_DOTS)));
        //tmp is clear
        $this->assertEquals(0, iterator_count(new \FilesystemIterator($this->tmp_storage_path , \FilesystemIterator::SKIP_DOTS)));
    }

    public function testReadFile() {
        //create read link
        $hash = base64_encode($this->faker->name);
        $file_id = $this->faker->md5;
        $link_data = [
            'hash' => $hash,
            'file' => $file_id,
            'temp' => 0,
            'type' => 'read'
        ];
        $link = new Link($link_data, app()->links);
        $link->swooleSave();
        $link->dbSave();

        //create file
        $path = $this->faker->image($this->test_storage_path , 640,  480, 'cats');
        $name = @end(explode('/', $path));

        $file = new AppFile(app()->files);
        $file_data = [
            'file_id' => $file_id,
            'path' => $path,
            'name' => $name,
            'is_delete' => 0

        ];
        $file->createFromData($file_data);
        $file->swooleSave();
        $file->dbSave();

        //get file request
        $response = $this->get("/file/$hash");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $response->assertHeader('Content-Description', 'File Transfer');
        $response->assertHeader('Content-Disposition', 'attachment; filename="'.$file_data['name'].'"');
        //TODO assert file content
    }

    public function testDeleteFile() {
        //create read link
        $hash = base64_encode($this->faker->name);
        $file_id = $this->faker->md5;
        $link_data = [
            'hash' => $hash,
            'file' => $file_id,
            'temp' => 0,
            'type' => 'read'
        ];
        $link = new Link($link_data, app()->links);
        $link->swooleSave();
        $link->dbSave();

        //create file
        $path = $this->faker->image($this->test_storage_path , 640,  480, 'cats');
        $name = @end(explode('/', $path));

        $file = new AppFile(app()->files);
        $file_data = [
            'file_id' => $file_id,
            'path' => $path,
            'name' => $name,
            'is_delete' => 0

        ];
        $file->createFromData($file_data);
        $file->swooleSave();
        $file->dbSave();

        //delete file request
        $response = $this->delete("/file/$file_id");
        $response->assertStatus(200);
        $this->assertDatabaseHas('files', ['file_id' => $file_id, 'is_delete' => 1]);
    }
}