<?php

/*
 +--------------------------------------------------------------------------+
 | Zephir Language                                                          |
 +--------------------------------------------------------------------------+
 | Copyright (c) 2013-2015 Zephir Team and contributors                     |
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

namespace Zephir\Statements;

use Zephir\Compiler;
use Zephir\CompilationContext;
use Zephir\CompilerException;
use Zephir\FunctionCall;
use Zephir\Optimizers\EvalExpression;
use Zephir\StatementsBlock;
use Zephir\Expression;
use Zephir\Detectors\ForValueUseDetector;
use Zephir\Variable;
use Zephir\Utils;

/**
 * ForStatement
 *
 * For statement
 */
class ForStatement extends StatementAbstract
{
    /**
     * Compiles a for statement that use a 'range' as expression
     *
     * @param array $exprRaw
     * @param CompilationContext $context
     * @return boolean
     */
    public function compileRange($exprRaw, CompilationContext $context)
    {
        if (!count($exprRaw['parameters'])) {
            return false;
        }

        if (count($exprRaw['parameters']) > 3) {
            return false;
        }

        $functionCall = new FunctionCall();
        $parameters = $functionCall->getResolvedParamsAsExpr($exprRaw['parameters'], $context, $exprRaw);

        if ($parameters[0]->getType() != 'variable') {
            if (!$parameters[0]->isIntCompatibleType()) {
                return false;
            }
        }

        if ($parameters[1]->getType() != 'variable') {
            if (!$parameters[1]->isIntCompatibleType()) {
                return false;
            }
        }

        $codePrinter = $context->codePrinter;

        /**
         * Initialize 'key' variable
         */
        if (isset($this->_statement['key'])) {
            /**
             * This variable is used to check if the loop is in its first iteration
             */
            $keyVariable = $context->symbolTable->getTempVariableForWrite('long', $context, $this->_statement);
            $keyVariable->increaseUses();
        }

        /**
         * This variable is used to check if the loop is in its first iteration
         */
        $flagVariable = $context->symbolTable->getTempVariableForWrite('bool', $context, $this->_statement);

        if ($parameters[0]->getType() != 'variable') {
            $tempVariable = $context->symbolTable->addTemp($parameters[0]->getType(), $context);
        } else {
            $rangeVariable = $context->symbolTable->getVariableForRead($parameters[0]->getCode(), $context, $this->_statement['expr']);
            $tempVariable = $context->symbolTable->addTemp($rangeVariable->getType(), $context);
        }

        /**
         * Create a copy of the current value in the end of the range to avoid modify the range
         * inside the cycle
         */
        if ($parameters[1]->getType() != 'variable') {
            $upperBoundVariable = $context->symbolTable->getTempVariable($parameters[1]->getType(), $context);
        } else {
            $rangeVariable = $context->symbolTable->getVariableForRead($parameters[1]->getCode(), $context, $this->_statement['expr']);
            $upperBoundVariable = $context->symbolTable->getTempVariable($rangeVariable->getType(), $context);
        }

        /**
         * Create an implicit 'let' operation to set the current value in the upper bound of the range
         */
        $statement = new LetStatement(array(
            'type' => 'let',
            'assignments' => array(
                array(
                    'assign-type' => 'variable',
                    'variable' => $upperBoundVariable->getName(),
                    'operator' => 'assign',
                    'expr' => array(
                        'type' => $parameters[1]->getType(),
                        'value' => $parameters[1]->getCode(),
                        'file' => $this->_statement['file'],
                        'line' => $this->_statement['line'],
                        'char' => $this->_statement['char']
                    ),
                    'file' => $this->_statement['file'],
                    'line' => $this->_statement['line'],
                    'char' => $this->_statement['char']
                )
            )
        ));
        $statement->compile($context);

        if ($this->_statement['reverse']) {
            /**
             * Create an implicit 'let' operation for the initialize expression, @TODO use a builder
             */
            $statement = new LetStatement(array(
                'type' => 'let',
                'assignments' => array(
                    array(
                        'assign-type' => 'variable',
                        'variable' => $tempVariable->getName(),
                        'operator' => 'assign',
                        'expr' => array(
                            'type' => 'variable',
                            'value' => $upperBoundVariable->getName(),
                            'file' => $this->_statement['file'],
                            'line' => $this->_statement['line'],
                            'char' => $this->_statement['char']
                        ),
                        'file' => $this->_statement['file'],
                        'line' => $this->_statement['line'],
                        'char' => $this->_statement['char']
                    )
                )
            ));
        } else {
            /**
             * Create an implicit 'let' operation for the initialize expression, @TODO use a builder
             */
            $statement = new LetStatement(array(
                'type' => 'let',
                'assignments' => array(
                    array(
                        'assign-type' => 'variable',
                        'variable' => $tempVariable->getName(),
                        'operator' => 'assign',
                        'expr' => array(
                            'type'  => $parameters[0]->getType(),
                            'value' => $parameters[0]->getCode(),
                            'file'  => $this->_statement['file'],
                            'line'  => $this->_statement['line'],
                            'char'  => $this->_statement['char']
                        ),
                        'file' => $this->_statement['file'],
                        'line' => $this->_statement['line'],
                        'char' => $this->_statement['char']
                    )
                )
            ));
        }

        $statement->compile($context);

        /**
         * Initialize 'key' variable
         */
        if (isset($this->_statement['key'])) {
            $codePrinter->output($keyVariable->getName() . ' = 0;');
        }
        $codePrinter->output($flagVariable->getName() . ' = 0;');

        if ($this->_statement['reverse']) {
            $conditionExpr = array(
                'type' => 'greater-equal',
                'left' => array('type' => 'variable', 'value' => $tempVariable->getName()),
                'right' => array('type' => $parameters[0]->getType(), 'value' => $parameters[0]->getCode())
            );
        } else {
            $conditionExpr = array(
                'type' => 'less-equal',
                'left' => array('type' => 'variable', 'value' => $tempVariable->getName()),
                'right' => array('type' => 'variable', 'value' => $upperBoundVariable->getName())
            );
        }

        $expr = new EvalExpression();
        $condition = $expr->optimize($conditionExpr, $context);

        $codePrinter->output('if (' . $condition . ') {');
        $codePrinter->increaseLevel();

        /**
         * Inside a cycle
         */
        $context->insideCycle++;

        $codePrinter->output('while (1) {');
        $codePrinter->increaseLevel();

        $codePrinter->output('if (' . $flagVariable->getName() . ') {');

        $codePrinter->increaseLevel();

        if (isset($this->_statement['key'])) {
            $codePrinter->output($keyVariable->getName() . '++;');
        }

        if ($this->_statement['reverse']) {
            if (!isset($parameters[2])) {
                $statement = new LetStatement(array(
                    'type' => 'let',
                    'assignments' => array(
                        array(
                            'assign-type' => 'decr',
                            'variable' => $tempVariable->getName(),
                            'file' => $this->_statement['file'],
                            'line' => $this->_statement['line'],
                            'char' => $this->_statement['char']
                        )
                    )
                ));
            } else {
                $statement = new LetStatement(array(
                    'type' => 'let',
                    'assignments' => array(
                        array(
                            'assign-type' => 'variable',
                            'operator' => 'sub-assign',
                            'variable' => $tempVariable->getName(),
                            'expr' => array(
                                'type' => $parameters[2]->getType(),
                                'value' => $parameters[2]->getCode(),
                                'file' => $this->_statement['file'],
                                'line' => $this->_statement['line'],
                                'char' => $this->_statement['char']
                            ),
                            'file' => $this->_statement['file'],
                            'line' => $this->_statement['line'],
                            'char' => $this->_statement['char']
                        )
                    )
                ));
            }
        } else {
            if (!isset($parameters[2])) {
                $statement = new LetStatement(array(
                    'type' => 'let',
                    'assignments' => array(
                        array(
                            'assign-type' => 'incr',
                            'variable' => $tempVariable->getName(),
                            'file' => $this->_statement['file'],
                            'line' => $this->_statement['line'],
                            'char' => $this->_statement['char']
                        )
                    )
                ));
            } else {
                $statement = new LetStatement(array(
                    'type' => 'let',
                    'assignments' => array(
                        array(
                            'assign-type' => 'variable',
                            'operator' => 'add-assign',
                            'variable' => $tempVariable->getName(),
                            'expr' => array(
                                'type' => $parameters[2]->getType(),
                                'value' => $parameters[2]->getCode(),
                                'file' => $this->_statement['file'],
                                'line' => $this->_statement['line'],
                                'char' => $this->_statement['char']
                            ),
                            'file' => $this->_statement['file'],
                            'line' => $this->_statement['line'],
                            'char' => $this->_statement['char']
                        )
                    )
                ));
            }
        }
        $statement->compile($context);

        /**
         * Multi-line conditions would need to be regenerated here
         */
        $condition = $expr->optimize($conditionExpr, $context);
        $codePrinter->output('if (!(' . $condition . ')) {');
        $codePrinter->output("\t" . "break;");
        $codePrinter->output('}');

        $codePrinter->decreaseLevel();

        $codePrinter->output('} else {');
        $codePrinter->output("\t" . $flagVariable->getName() . ' = 1;');
        $codePrinter->output('}');

        /**
         * Initialize 'key' variable
         */
        if (isset($this->_statement['key'])) {
            /**
             * Check for anonymous variables
             */
            if ($this->_statement['key'] != '_') {
                $keyVariableName = $this->_statement['key'];
            } else {
                $keyVariableName = $keyVariable->getName();
            }

            /**
             * Create an implicit 'let' operation, @TODO use a builder
             */
            $statement = new LetStatement(array(
                'type' => 'let',
                'assignments' => array(
                    array(
                        'assign-type' => 'variable',
                        'variable' => $keyVariableName,
                        'operator' => 'assign',
                        'expr' => array(
                            'type' => 'variable',
                            'value' => $keyVariable->getName(),
                            'file' => $this->_statement['file'],
                            'line' => $this->_statement['line'],
                            'char' => $this->_statement['char']
                        ),
                        'file' => $this->_statement['file'],
                        'line' => $this->_statement['line'],
                        'char' => $this->_statement['char']
                    )
                )
            ));

            $statement->compile($context);
        }

        /**
         * Initialize 'value' variable
         */
        if (isset($this->_statement['value'])) {
            /**
             * Check for anonymous variables
             */
            if ($this->_statement['value'] != '_') {
                $valueVariable = $this->_statement['value'];
            } else {
                $valueVariable = $tempVariable->getName();
            }

            /**
             * Create an implicit 'let' operation, @TODO use a builder
             */
            $statement = new LetStatement(array(
                'type' => 'let',
                'assignments' => array(
                    array(
                        'assign-type' => 'variable',
                        'variable' => $valueVariable,
                        'operator' => 'assign',
                        'expr' => array(
                            'type'  => 'variable',
                            'value' => $tempVariable->getName(),
                            'file'  => $this->_statement['file'],
                            'line'  => $this->_statement['line'],
                            'char'  => $this->_statement['char']
                        ),
                        'file' => $this->_statement['file'],
                        'line' => $this->_statement['line'],
                        'char' => $this->_statement['char']
                    )
                )
            ));

            $statement->compile($context);
        }

        $codePrinter->decreaseLevel();

        /**
         * Compile statements in the 'for' block
         */
        if (isset($this->_statement['statements'])) {
            $st = new StatementsBlock($this->_statement['statements']);
            $st->isLoop(true);
            if (isset($this->_statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->_statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->_statement['value']);
            $st->compile($context);
        }

        /**
         * Restore the cycle counter
         */
        $context->insideCycle--;

        $codePrinter->output('}');

        $codePrinter->decreaseLevel();

        $codePrinter->output('}');
    }

    /**
     * Compiles a 'for' statement that use an 'iterator' as expression
     *
     * @param array $exprRaw
     * @param CompilationContext $context
     * @return boolean
     */
    public function compileIterator(array $exprRaw, $context)
    {
        $iteratorVariable = $context->symbolTable->getTempVariableForWrite('zend_object_iterator', $context);

        $context->headersManager->add('kernel/iterator');

        $codePrinter = $context->codePrinter;

        /**
         * Resolve first parameter of iterator(p)
         */
        $expr = new Expression($exprRaw['parameters'][0]['parameter']);
        $expr->setReadOnly(false);
        $expression = $expr->compile($context);

        if ($expression->getType() != 'variable') {
            throw new CompilerException("Unknown type: " . $expression->getType(), $exprRaw);
        }

        $exprVariable = $context->symbolTable->getVariableForRead($expression->getCode(), $context, $this->_statement['expr']);
        switch ($exprVariable->getType()) {
            case 'variable':
                break;
        }

        /**
         * Initialize 'key' variable
         */
        if (isset($this->_statement['key'])) {
            if ($this->_statement['key'] != '_') {
                $keyVariable = $context->symbolTable->getVariableForWrite($this->_statement['key'], $context, $this->_statement['expr']);
                if ($keyVariable->getType() != 'variable') {
                    throw new CompilerException("Cannot use variable: " . $this->_statement['key'] . " type: " . $keyVariable->getType() . " as key in hash traversal", $this->_statement['expr']);
                }
            } else {
                /**
                 * Anonymous key variable
                 */
                $keyVariable = $context->symbolTable->getTempVariableForWrite('variable', $context);
                $keyVariable->increaseUses();
            }

            $keyVariable->setMustInitNull(true);
            $keyVariable->setIsInitialized(true, $context, $this->_statement);
            $keyVariable->setDynamicTypes('undefined');
        }

        /**
         * Initialize 'value' variable
         */
        if (isset($this->_statement['value'])) {
            if ($this->_statement['value'] != '_') {
                $variable = $context->symbolTable->getVariableForWrite($this->_statement['value'], $context, $this->_statement['expr']);
                if ($variable->getType() != 'variable') {
                    throw new CompilerException("Cannot use variable: " . $this->_statement['value'] . " type: " . $variable->getType() . " as value in hash traversal", $this->_statement['expr']);
                }
            } else {
                /**
                 * Anonymous value variable
                 */
                $variable = $context->symbolTable->getTempVariableForWrite('variable', $context);
                $variable->increaseUses();
            }

            $variable->setMustInitNull(true);
            $variable->setIsInitialized(true, $context, $this->_statement);
            $variable->setDynamicTypes('undefined');
        }

        /**
         * Variables are initialized in a different way inside cycle
         */
        $context->insideCycle++;

        $codePrinter->output($iteratorVariable ->getName() . ' = zephir_get_iterator(' . $exprVariable->getName() . ' TSRMLS_CC);');

        $codePrinter->output($iteratorVariable ->getName() . '->funcs->rewind(' . $iteratorVariable->getName() . ' TSRMLS_CC);');
        $codePrinter->output('for (;' . $iteratorVariable->getName() . '->funcs->valid(' . $iteratorVariable->getName() . ' TSRMLS_CC) == SUCCESS && !EG(exception); ' . $iteratorVariable ->getName() . '->funcs->move_forward(' . $iteratorVariable ->getName() . ' TSRMLS_CC)) {');

        if (isset($this->_statement['key'])) {
            $context->symbolTable->mustGrownStack(true);
            $codePrinter->output("\t" . 'ZEPHIR_GET_IMKEY(' . $this->_statement['key'] . ', ' . $iteratorVariable->getName() . ');');
        }

        if (isset($this->_statement['value'])) {
            $context->symbolTable->mustGrownStack(true);
            $codePrinter->output("\t" . '{ zval **tmp; ');
            $codePrinter->output("\t" . $iteratorVariable->getName() . '->funcs->get_current_data(' . $iteratorVariable->getName() . ', &tmp TSRMLS_CC);');
            $codePrinter->output("\t" . $variable->getName() . ' = *tmp;');
            $codePrinter->output("\t" . '}');
        }

        /**
         * Compile statements in the 'for' block
         */
        if (isset($this->_statement['statements'])) {
            $st = new StatementsBlock($this->_statement['statements']);
            $st->isLoop(true);
            if (isset($this->_statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->_statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->_statement['value']);
            $st->compile($context);
        }

        /**
         * Restore the cycle counter
         */
        $context->insideCycle--;

        $codePrinter->output('}');

        $codePrinter->output($iteratorVariable ->getName() . '->funcs->dtor(' . $iteratorVariable ->getName() . ' TSRMLS_CC);');
    }

    /**
     * Compiles traversing of string values
     * - Evaluated expression must be a string
     * - Every key must be an integer or compatible
     * - Every value must be a char/integer or compatible
     *
     * @param array $expression
     * @param CompilationContext $context
     * @param Variable $exprVariable
     */
    public function compileStringTraverse(array $expression, CompilationContext $context, Variable $exprVariable)
    {
        $codePrinter = $context->codePrinter;

        /**
         * Initialize 'key' variable
         */
        if (isset($this->_statement['key'])) {
            if ($this->_statement['key'] != '_') {
                $keyVariable = $context->symbolTable->getVariableForWrite($this->_statement['key'], $context, $this->_statement['expr']);
                switch ($keyVariable->getType()) {
                    case 'int':
                    case 'uint':
                    case 'long':
                    case 'ulong':
                    case 'char':
                    case 'uchar':
                        break;
                    default:
                        throw new CompilerException("Cannot use variable: " . $this->_statement['key'] . " type: " . $keyVariable->getType() . " as key in string traversal", $this->_statement['expr']);
                }
            } else {
                $keyVariable = $context->symbolTable->getTempVariableForWrite('int', $context);
                $keyVariable->increaseUses();
            }

            $keyVariable->setMustInitNull(true);
            $keyVariable->setIsInitialized(true, $context, $this->_statement);
        }

        /**
         * Initialize 'value' variable
         */
        if (isset($this->_statement['value'])) {
            if ($this->_statement['value'] != '_') {
                $variable = $context->symbolTable->getVariableForWrite($this->_statement['value'], $context, $this->_statement['expr']);
                switch ($variable->getType()) {
                    case 'int':
                    case 'uint':
                    case 'long':
                    case 'ulong':
                    case 'char':
                    case 'uchar':
                        break;
                    default:
                        throw new CompilerException("Cannot use variable: " . $this->_statement['value'] . " type: " . $variable->getType() . " as value in string traversal", $this->_statement['expr']);
                }
            } else {
                $variable = $context->symbolTable->getTempVariableForWrite('char', $context);
                $variable->increaseUses();
            }

            $variable->setMustInitNull(true);
            $variable->setIsInitialized(true, $context, $this->_statement);
        }

        $tempVariable = $context->symbolTable->addTemp('long', $context);

        /**
         * Create a temporary value to store the constant string
         */
        if ($expression->getType() == 'string') {
            $constantVariable = $context->symbolTable->getTempLocalVariableForWrite('variable', $context, $this->_statement);
            $codePrinter->output('ZVAL_STRING(&' . $constantVariable->getName() . ', "' . Utils::addSlashes($expression->getCode()) . '", 0);');
            $stringVariable = $constantVariable;
        } else {
            $stringVariable = $exprVariable;
        }

        if ($this->_statement['reverse']) {
            if ($stringVariable->isLocalOnly()) {
                $codePrinter->output('for (' . $tempVariable->getName() . ' = Z_STRLEN_P(&' . $stringVariable->getName() . '); ' . $tempVariable->getName() . ' >= 0; ' . $tempVariable->getName() . '--) {');
            } else {
                $codePrinter->output('for (' . $tempVariable->getName() . ' = Z_STRLEN_P(' . $stringVariable->getName() . '); ' . $tempVariable->getName() . ' >= 0; ' . $tempVariable->getName() . '--) {');
            }
        } else {
            if ($stringVariable->isLocalOnly()) {
                $codePrinter->output('for (' . $tempVariable->getName() . ' = 0; ' . $tempVariable->getName() . ' < Z_STRLEN_P(&' . $stringVariable->getName() . '); ' . $tempVariable->getName() . '++) {');
            } else {
                $codePrinter->output('for (' . $tempVariable->getName() . ' = 0; ' . $tempVariable->getName() . ' < Z_STRLEN_P(' . $stringVariable->getName() . '); ' . $tempVariable->getName() . '++) {');
            }
        }

        if (isset($this->_statement['key'])) {
            $codePrinter->output("\t" . $keyVariable->getName() . ' = ' . $tempVariable->getName() . '; ');
        }

        $context->headersManager->add('kernel/operators');
        if ($stringVariable->isLocalOnly()) {
            $codePrinter->output("\t" . $variable->getName() . ' = ZEPHIR_STRING_OFFSET(&' . $stringVariable->getName() . ', ' . $tempVariable->getName() . ');');
        } else {
            $codePrinter->output("\t" . $variable->getName() . ' = ZEPHIR_STRING_OFFSET(' . $stringVariable->getName() . ', ' . $tempVariable->getName() . ');');
        }

        /**
         * Variables are initialized in a different way inside cycle
         */
        $context->insideCycle++;

        /**
         * Compile statements in the 'for' block
         */
        if (isset($this->_statement['statements'])) {
            $st = new StatementsBlock($this->_statement['statements']);
            $st->isLoop(true);
            if (isset($this->_statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->_statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->_statement['value']);
            $st->compile($context);
        }

        $context->insideCycle--;

        $codePrinter->output('}');
    }

    /**
     * Compiles traversing of hash values
     *
     * - Evaluated expression must be a zval
     * - A key must be a zval
     * - A value must be a zval
     *
     * @param array $expression
     * @param CompilationContext $context
     * @param Variable $exprVariable
     */
    public function compileHashTraverse($expression, $context, $exprVariable)
    {
        $codePrinter = $context->codePrinter;

        /**
         * Initialize 'key' variable
         */
        if (isset($this->_statement['key'])) {
            if ($this->_statement['key'] != '_') {
                $keyVariable = $context->symbolTable->getVariableForWrite($this->_statement['key'], $context, $this->_statement['expr']);
                if ($keyVariable->getType() != 'variable') {
                    throw new CompilerException("Cannot use variable: " . $this->_statement['key'] . " type: " . $keyVariable->getType() . " as key in hash traversal", $this->_statement['expr']);
                }
            } else {
                $keyVariable = $context->symbolTable->getTempVariableForWrite('variable', $context);
            }

            $keyVariable->setMustInitNull(true);
            $keyVariable->setIsInitialized(true, $context, $this->_statement);
            $keyVariable->setDynamicTypes('undefined');
        }

        /**
         * Initialize 'value' variable
         */
        if (isset($this->_statement['value'])) {
            if ($this->_statement['value'] != '_') {
                $variable = $context->symbolTable->getVariableForWrite($this->_statement['value'], $context, $this->_statement['expr']);
                if ($variable->getType() != 'variable') {
                    throw new CompilerException("Cannot use variable: " . $this->_statement['value'] . " type: " . $variable->getType() . " as value in hash traversal", $this->_statement['expr']);
                }
            } else {
                $variable = $context->symbolTable->getTempVariableForWrite('variable', $context);
            }

            $variable->setMustInitNull(true);
            $variable->setIsInitialized(true, $context, $this->_statement);
            $variable->setDynamicTypes('undefined');
        }

        /**
         * Variables are initialized in a different way inside cycle
         */
        $context->insideCycle++;

        /**
         * Create a hash table and hash pointer temporary variables
         */
        $arrayPointer = $context->symbolTable->addTemp('HashPosition', $context);
        $arrayHash = $context->symbolTable->addTemp('HashTable', $context);

        /**
         * Create a temporary zval to fetch the items from the hash
         */
        $tempVariable = $context->symbolTable->addTemp('variable', $context);
        $tempVariable->setIsDoublePointer(true);

        $context->headersManager->add('kernel/hash');

        $duplicateHash = '0';
        $duplicateKey = true;

        /**
         * We have to check if hashes are modified within the for's block
         */
        if (isset($this->_statement['statements'])) {
            /**
             * Create the statements block here to obtain the last use line
             */
            $st = new StatementsBlock($this->_statement['statements']);

            $detector = new ForValueUseDetector();
            if ($detector->detect($exprVariable->getName(), $this->_statement['statements'])) {
                $duplicateHash = '1';
            }

            /**
             * Detect if the key is modified or passed to an external scope
             */
            if (isset($this->_statement['key'])) {
                if (!$keyVariable->isTemporal()) {
                    $detector->setDetectionFlags(ForValueUseDetector::DETECT_ALL);
                    if ($detector->detect($keyVariable->getName(), $this->_statement['statements'])) {
                        $loopContext = $context->currentMethod->getLocalContextPass();
                        //echo $st->getLastLine();
                        //echo $loopContext->getLastVariableUseLine($keyVariable->getName());
                        $duplicateKey = true;
                    }
                }
            }
        }

        $codePrinter->output('zephir_is_iterable(' . $expression->getCode() . ', &' . $arrayHash->getName() . ', &' . $arrayPointer ->getName() . ', ' . $duplicateHash . ', ' . $this->_statement['reverse'] . ', "' . Compiler::getShortUserPath($this->_statement['file']) . '", ' . $this->_statement['line'] . ');');

        $codePrinter->output('for (');
        $codePrinter->output('  ; zephir_hash_get_current_data_ex(' . $arrayHash->getName() . ', (void**) &' . $tempVariable->getName() . ', &' . $arrayPointer ->getName() . ') == SUCCESS');
        if ($this->_statement['reverse']) {
            $codePrinter->output('  ; zephir_hash_move_backwards_ex(' . $arrayHash->getName() . ', &' . $arrayPointer ->getName() . ')');
        } else {
            $codePrinter->output('  ; zephir_hash_move_forward_ex(' . $arrayHash->getName() . ', &' . $arrayPointer ->getName() . ')');
        }
        $codePrinter->output(') {');

        if (isset($this->_statement['key'])) {
            if ($duplicateKey) {
                $context->symbolTable->mustGrownStack(true);
                $codePrinter->output("\t" . 'ZEPHIR_GET_HMKEY(' . $keyVariable->getName() . ', ' . $arrayHash->getName() . ', ' . $arrayPointer ->getName() . ');');
            } else {
                $codePrinter->output("\t" . 'ZEPHIR_GET_HKEY(' . $keyVariable->getName() . ', ' . $arrayHash->getName() . ', ' . $arrayPointer ->getName() . ');');
            }
        }

        if (isset($this->_statement['value'])) {
            $context->symbolTable->mustGrownStack(true);
            $codePrinter->output("\t" . 'ZEPHIR_GET_HVALUE(' . $variable->getName() . ', ' . $tempVariable->getName() . ');');
        }

        /**
         * Compile statements in the 'for' block
         */
        if (isset($this->_statement['statements'])) {
            $st->isLoop(true);
            if (isset($this->_statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->_statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->_statement['value']);
            $st->compile($context);
        }

        /**
         * Restore the cycle counter
         */
        $context->insideCycle--;

        $codePrinter->output('}');
    }

    /**
     * @param CompilationContext $context
     * @throws CompilerException
     */
    public function compile(CompilationContext $context)
    {
        $exprRaw = $this->_statement['expr'];

        /**
         * @TODO implement optimizers here
         */
//        var_dump($exprRaw);
        if ($exprRaw['type'] == 'fcall') {
            if ($exprRaw['name'] == 'range') {
                $status = $this->compileRange($exprRaw, $context);
                if ($status !== false) {
                    return;
                }
            }

            if ($exprRaw['name'] == 'iterator') {
                $status = $this->compileIterator($exprRaw, $context);
                if ($status !== false) {
                    return;
                }
            }
        }

        $expr = new Expression($exprRaw);
        $expr->setReadOnly(true);
        $expression = $expr->compile($context);

        /**
         * Check for traversing a constant string
         */
        if ($expression->getType() == 'string') {
            $this->compileStringTraverse($expression, $context, null);
            return;
        }

        if ($expression->getType() != 'variable' && $expression->getType() != 'array') {
            throw new CompilerException("Unknown type: " . $expression->getType(), $exprRaw);
        }

        $exprVariable = $context->symbolTable->getVariableForRead($expression->getCode(), $context, $this->_statement['expr']);
        switch ($exprVariable->getType()) {

            case 'variable':
            case 'array':
                $this->compileHashTraverse($expression, $context, $exprVariable);
                break;

            case 'string':
                $this->compileStringTraverse($expression, $context, $exprVariable);
                break;

            default:
                throw new CompilerException("Cannot traverse value type: " . $exprVariable->getType(), $exprRaw);
        }
    }
}
