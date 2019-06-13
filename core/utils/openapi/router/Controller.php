<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 5:23 PM
 */

namespace Openapi\Router;


class Controller {
    public $name;
    public $namespace;
    public $validationNamespace;
    public $controllersPath;
    public $description;
    public $methods=[];

    public function __construct($class, $namespace, $validationNamespace, $controllersPath, $description) {
        $this->name = $class;
        $this->namespace = $namespace;
        $this->validationNamespace = $validationNamespace;
        $this->controllersPath = $controllersPath;
        $this->description = $description;
    }

    public function addMethod($method, $summary, $description, $args) {
        if (!key_exists($method, $this->methods))
            $this->methods[$method] = ['summary' => $summary, 'description' => $description, 'args' => $args];
    }

    public function save($withValidation = false){
        $str =
            "<?php".PHP_EOL.
            "namespace {$this->namespace};".PHP_EOL.PHP_EOL.
            "use \\{$this->validationNamespace}\\Validator;".PHP_EOL.PHP_EOL.
            "/**".PHP_EOL.
            " * @description {$this->description}".PHP_EOL.
            " */".PHP_EOL.
            "class ".ucfirst($this->name)."Controller {".PHP_EOL.
            "    use \Router\\Routable;".PHP_EOL;
        foreach ($this->methods as $method => $data) {
            if (!empty($data["args"])) {
                $args = [];
                foreach (array_keys($data["args"]) as $value) {
                    $args[] = "$" . $value;
                }
                $args = implode(",", $args);

                $body = "";
                if ($withValidation) {
                    $fields = [];
                    foreach (array_keys($data["args"]) as $value) {
                        $fields[] = "'$value' => " . "$" . $value;
                    }
                    $fields = implode(",", $fields);
                    $body =
                        "        \$validator = new Validator([$fields],\"" . ucfirst($method) . "\");" . PHP_EOL .
                        "        \$result = \$validator->get();" . PHP_EOL .
                        "        \$errors = \$validator->errors();" . PHP_EOL;
                }
            } else {
                $args = "";
                $body = "";
                if ($withValidation) {
                    $body =
                        "        \$validator = new Validator(\$this->request->getArgs(),\"" . ucfirst($method) . "\");" . PHP_EOL .
                        "        \$result = \$validator->get();" . PHP_EOL .
                        "        \$errors = \$validator->errors();" . PHP_EOL;
                }
            }

            $str .=
                PHP_EOL.
                "    /**".PHP_EOL.
                "     * @summary {$data['summary']}".PHP_EOL.
                "     * @description {$data['description']}".PHP_EOL.
                "     */".
                PHP_EOL."   public function {$method} ($args) {".PHP_EOL.
                $body.PHP_EOL.
                "    }".PHP_EOL;
        }
        $str .= "}";
        if (!is_dir($this->controllersPath)){
            mkdir($this->controllersPath, 0755, true);
        }
        file_put_contents($this->controllersPath."/".ucfirst($this->name)."Controller.php", $str);
        echo "  ".$this->name.PHP_EOL;
    }
}