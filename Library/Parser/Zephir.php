<?php

namespace Zephir\Parser;

use Zephir\Compiler;
use Zephir\Compiler\File;
use Zephir\Definitions\ClassDefinition;

class Zephir implements ParserInterface
{

    public function __construct()
    {
        if (!file_exists(ZEPHIRPATH . '/bin/zephir-parser')) {
            throw new Exception('zephir-parser was not found');
        }
    }

    public function parse(File $file)
    {
        $json = $this->parseFile($file);

        foreach($json as $row) {
            switch($row->type) {
                case 'class':
                    $classDefinition = new ClassDefinition($row->name, $row->docblock);

                    $file->addClass($classDefinition);
                    break;
            }
        }
    }

    protected function getCachePath($filename)
    {
        return '.temp' . DIRECTORY_SEPARATOR . Compiler::VERSION . DIRECTORY_SEPARATOR . $filename . ".js";
    }

    public function parseFile($file)
    {
        $outputPath = $this->getCachePath($file->getFileName());
        system(ZEPHIRPATH . '/bin/zephir-parser ' . $file->getFilepath() . ' > ' . $outputPath);

        $file = file_get_contents($outputPath);
        $json = json_decode($file);

        return $json;
    }
}
