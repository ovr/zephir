<?php

/*
 +--------------------------------------------------------------------------+
 | Zephir Language                                                          |
 +--------------------------------------------------------------------------+
 | Copyright (c) 2013-2014 Zephir Team and contributors                     |
 +--------------------------------------------------------------------------+
 | This source file is subject the MIT license, that is bundled with        |
 | this package in the file LICENSE, and is available through the           |
 | world-wide-web at the following url:                                     |
 | http://zephir-lang.com/license.html                                      |
 |                                                                          |
 | If you did not receive a copy of the MIT license and are unable          |
 | to obtain it through the world-wide-web, please send a note to           |
 | license@zephir-lang.com so we can mail you a copy immediately.           |
 +--------------------------------------------------------------------------+
*/

namespace Zephir\Compiler;


use Zephir\Definitions\ClassDefinition;

class File
{
    /**
     * @var array
     */
    protected $pathInfo;

    /**
     * @var ClassDefinition
     */
    protected $classes = [];

    public function __construct($filePath)
    {
        if (!is_file($filePath)) {
            throw new Exception('Couldn`t find file by path: ' . $filePath);
        }

        if (!is_readable($filePath)) {
            throw new Exception('File is not readable: ' . $filePath);
        }

        $this->pathInfo = pathinfo($filePath);
    }

    public function addClass(ClassDefinition $class)
    {
        $this->classes[] = $class;
    }

    public function preCompile()
    {

    }

    public function compile()
    {

    }

    public function getFilePath()
    {
        return $this->pathInfo['dirname'] . DIRECTORY_SEPARATOR . $this->pathInfo['basename'];
    }

    public function getExtension()
    {
        return $this->pathInfo['extension'];
    }

    public function getFileName()
    {
        return $this->pathInfo['filename'];
    }
}
