<?php

namespace Zephir\Parser;

use Zephir\Compiler\File;
use Zephir\Parser;

interface ParserInterface
{
    /**
     * @param File $file
     * @return mixed
     */
    public function parse(File $file);
}
