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

namespace Zephir\Utils;

use Zephir\Utils;

class Compiler
{
    const GCC = 'gcc';

    const LLVM_GCC = 'llvm-gcc';

    /**
     * @param $name
     * @return Compiler\GCC|Compiler\LLVM_GCC
     */
    public static function factory($name)
    {
        switch ($name) {
            case self::GCC:
                return new Utils\Compiler\GCC();
                break;
            case self::LLVM_GCC:
                return new Utils\Compiler\LLVM_GCC();
                break;
            default:
                break;
        }
    }
}
