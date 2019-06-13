<?php
namespace Openapi;

class ValidationMask {
    
    public $namespace;
    public $name;
    public $structure;
    public $type;
    public $str;

    public function __construct($maskName) {
        $this->name = $maskName;
    }

    public function with($maskData, $maskType, $namespace) {
        $this->structure = $maskData;
        $this->type = $maskType;
        $this->namespace = $namespace;
    }

    public function createString() {
        $str = "<?php".PHP_EOL.
            "namespace {$this->namespace};".PHP_EOL.PHP_EOL.
            "class ". $this->generateName()." extends \\Validation\\masks\\{$this->type} {".PHP_EOL.
            "public \$structure = ".PHP_EOL.$this->varexport($this->structure,true).PHP_EOL.";".PHP_EOL."}";
        $this->str = $str;
    }

    public function save($path, $fileDriver = null) {
        $name = $this->generateName().".php";
        $savePath = $path."/$name";
        if ($fileDriver) {
            return $fileDriver->write($savePath, $this->str);
        } else {
            return file_put_contents($savePath, $this->str);
        }
    }

    public function read() {
        $class = $this->namespace."\\".$this->generateName();
        $mask = new $class();
        $reflection = new \ReflectionClass($class);
        $fulltype = $reflection->getParentClass()->name;
        $exp = explode("\\", $fulltype);
        $type = end($exp);

        $this->structure = $mask->structure;
        $this->type = $type;
    }

    public function generateName() {
        return ucfirst($this->name);
    }

    public function varexport($expression, $return=FALSE) {
        $export = var_export($expression, TRUE);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $array = preg_replace(["/stdClass::__set_state\(array\($/", "/\)](,)?$/"], [NULL, ']$1'], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        if ($return)
            return $export;
        else
            echo $export;
    }
}