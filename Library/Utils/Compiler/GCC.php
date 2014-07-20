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

namespace Zephir\Utils\Compiler;

use Zephir\Compiler;

class GCC implements CompilerInterface
{
    const NAME = 'gcc';

    public function getVersion()
    {
        if (file_exists('.temp/' . Compiler::VERSION . '/gcc-version')) {
            return file_get_contents('.temp/' . Compiler::VERSION . '/' . $this::NAME . '-version');
        }

        system($this::NAME . ' -v 2> .temp/' . Compiler::VERSION . '/' . $this::NAME . '-version-temp');
        $lines = file('.temp/' . Compiler::VERSION . '/' . $this::NAME . '-version-temp');
        foreach ($lines as $line) {
            if (strpos($line, 'LLVM') !== false) {
                file_put_contents('.temp/' . Compiler::VERSION . '/' . $this::NAME . '-version', '4.8.0');

                return '4.8.0';
            }
        }

        $lastLine = $lines[count($lines) - 1];
        if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $lastLine, $matches)) {
            file_put_contents('.temp/' . Compiler::VERSION . '/' . $this::NAME . '-version', $matches[0]);

            return $matches[0];
        }

        return '0.0.0';
    }
}
