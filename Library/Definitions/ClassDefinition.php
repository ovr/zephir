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

namespace Zephir\Definitions;

use Zephir\Definitions\ClassDefinition\MethodDefinition;

class ClassDefinition
{
    /**
     * @var string
     */
    const CLASS_TYPE = 'class';

    /**
     * @var string
     */
    const INTERFACE_TYPE = 'interface';

    /**
     * @var string|null
     */
    private $docBlock;

    /**
     * @var MethodDefinition[]
     */
    private $methods = [];

    /**
     * Class name
     *
     * @var string
     */
    private $name;

    private $type = self::CLASS_TYPE;

    /**
     * @param string $name ClassName
     * @param string|null $docBlock phpdoc comment for class
     */
    public function __construct($name, $docBlock = null)
    {
        $this->name = $name;
        $this->docBlock = $docBlock;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    public function addMethod(MethodDefinition $methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
