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

namespace Zephir;

/**
 * ClassConstant
 *
 * Represents a constant class
 */
class ClassConstant
{
    /**
     * Constant's name
     *
     * @var string
     */
    protected $name;

    /**
     * AST
     *
     * @var array
     */
    protected $expression;

    /**
     * @var string
     */
    protected $docblock;

    public function __construct($name, array $expression, $docBlock)
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->docblock = $docBlock;
    }

    /**
     * Returns the constant's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the constant's value
     *
     * @return array
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Get value of constant
     *
     * @return mixed
     */
    public function getValue()
    {
        if (isset($this->expression['value'])) {
            return $this->expression['value'];
        }

        return false;
    }

    /**
     * Returns the docblock related to the constant
     *
     * @return string
     */
    public function getDocBlock()
    {
        return $this->docblock;
    }

    /**
     * Get type of class constant
     *
     * @return string
     */
    public function getType()
    {
        return $this->expression['type'];
    }

    /**
     * Produce the code to register a class constant
     *
     * @param CompilationContext $compilationContext
     */
    public function compile(CompilationContext $compilationContext)
    {

        switch ($this->expression['type']) {

            case 'long':
            case 'int':
                $compilationContext->codePrinter->output("zend_declare_class_constant_long(" . $compilationContext->classDefinition->getClassEntry() . ", SL(\"" . $this->getName() . "\"), " . $this->getValue() . " TSRMLS_CC);");
                break;

            case 'double':
                $compilationContext->codePrinter->output("zend_declare_class_constant_double(" . $compilationContext->classDefinition->getClassEntry() . ", SL(\"" . $this->getName() . "\"), " . $this->getValue() . " TSRMLS_CC);");
                break;

            case 'bool':
                if ($this->expression['value'] == 'false') {
                    $compilationContext->codePrinter->output("zend_declare_class_constant_bool(" . $compilationContext->classDefinition->getClassEntry() . ", SL(\"" . $this->getName() . "\"), 0 TSRMLS_CC);");
                } else {
                    $compilationContext->codePrinter->output("zend_declare_class_constant_bool(" . $compilationContext->classDefinition->getClassEntry() . ", SL(\"" . $this->getName() . "\"), 1 TSRMLS_CC);");
                }
                break;

            case 'string':
                $compilationContext->codePrinter->output("zend_declare_class_constant_string(" . $compilationContext->classDefinition->getClassEntry() . ", SL(\"" . $this->getName() . "\"), \"" . Utils::addSlashes($this->getValue()) . "\" TSRMLS_CC);");
                break;

            default:
                $compilationContext->codePrinter->output("zend_declare_class_constant_null(" . $compilationContext->classDefinition->getClassEntry() . ", SL(\"" . $this->getName() . "\") TSRMLS_CC);");
        }
    }
}
