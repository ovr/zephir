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

class File
{
    /**
     * @var array
     */
    protected $pathinfo;

    public function __construct($filepath)
    {
        if (!is_file($filepath)) {
            throw new Exception('Couldn`t find file by path: ' . $filepath);
        }

        if (!is_readable($filepath)) {
            throw new Exception('File is not readable: ' . $filepath);
        }

        $this->pathinfo = pathinfo($filepath);
    }

    public function getFilepath()
    {
        return $this->pathinfo['dirname'] . DIRECTORY_SEPARATOR . $this->pathinfo['basename'];
    }

    public function getFileName()
    {
        return $this->pathinfo['filename'];
    }
}
