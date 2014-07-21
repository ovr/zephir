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

use Zephir\Compiler\Exception as CompilerException;

/**
 * CompilerFile
 *
 * This class represents every file compiled in a project
 * Every file may contain a class or an interface
 */
class CompilerFile
{
    private $namespace;

    private $className;

    private $filePath;

    private $ir;

    private $originalNode;

    private $compiledFile;

    /**
     * @var ClassDefinition
     */
    private $classDefinition;

    private $headerCBlocks;

    /**
     * @var Config
     */
    private $config = null;

    /**
     * @var Logger
     */
    private $logger = null;

    /**
     * @var AliasManager
     */
    private $aliasManager;

    /**
     * CompilerFile constructor
     *
     * @param string $className
     * @param string $filePath
     */
    public function __construct($className, $filePath, Config $config, Logger $logger)
    {
        $this->className = $className;
        $this->filePath = $filePath;
        $this->compiledFilePath = preg_replace('/\.zep$/', '', $className);
        $this->filesCompiled = array();
        $this->headerCBlocks = array();
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Returns the class definition related to the compiled file
     *
     * @return ClassDefinition
     */
    public function getClassDefinition()
    {
        return $this->classDefinition;
    }

    /**
     * Compiles the file generating a JSON intermediate representation
     *
     * @return array
     */
    public function genIR()
    {

        $compilePath = '.temp' . DIRECTORY_SEPARATOR . Compiler::VERSION . DIRECTORY_SEPARATOR . str_replace(DIRECTORY_SEPARATOR, '_', realpath($this->filePath)) . ".js";
        $zepRealPath = realpath($this->filePath);



        $changed = false;
        if (file_exists($compilePath)) {
            if (filemtime($compilePath) < filemtime($zepRealPath) || filemtime($compilePath) < filemtime(ZEPHIRPATH . '/bin/zephir-parser')) {
                system(ZEPHIRPATH . '/bin/zephir-parser ' . $zepRealPath . ' > ' . $compilePath);
                $changed = true;
            }
        } else {
            system(ZEPHIRPATH . '/bin/zephir-parser ' . $zepRealPath . ' > ' . $compilePath);
            $changed = true;
        }

        if ($changed || !file_exists($compilePath . '.php')) {
            $json = json_decode(file_get_contents($compilePath), true);
            $data = '<?php return ' . var_export($json, true) . ';';
            file_put_contents($compilePath . '.php', $data);
        }

        return require $compilePath . '.php';
    }

    /**
     * Compiles the class/interface contained in the file
     *
     * @param CompilationContext $compilationContext
     * @param string $namespace
     * @param string $topStatement
     */
    public function compileClass(CompilationContext $compilationContext, $namespace, $topStatement)
    {
        $classDefinition = $this->classDefinition;

        /**
         * Do the compilation
         */
        $classDefinition->compile($compilationContext);

        $separators = str_repeat('../', count(explode('\\', $classDefinition->getCompleteName())) - 1);

        $code  = '' . PHP_EOL;
        $code .= '#ifdef HAVE_CONFIG_H' . PHP_EOL;
        $code .= '#include "' . $separators . 'ext_config.h"' . PHP_EOL;
        $code .= '#endif' . PHP_EOL;
        $code .= '' . PHP_EOL;

        $code .= '#include <php.h>' . PHP_EOL;
        $code .= '#include "' . $separators . 'php_ext.h"' . PHP_EOL;
        $code .= '#include "' . $separators . 'ext.h"' . PHP_EOL;
        $code .= '' . PHP_EOL;

        if ($classDefinition->getType() == 'class') {
            $code .= '#include <Zend/zend_operators.h>' . PHP_EOL;
            $code .= '#include <Zend/zend_exceptions.h>' . PHP_EOL;
            $code .= '#include <Zend/zend_interfaces.h>' . PHP_EOL;
        } else {
            $code .= '#include <Zend/zend_exceptions.h>' . PHP_EOL;
        }
        $code .= '' . PHP_EOL;

        $code .= '#include "kernel/main.h"' . PHP_EOL;

        if ($classDefinition->getType() == 'class') {
            foreach ($compilationContext->headersManager->get() as $header => $one) {
                $code .= '#include "' . $header . '.h"' . PHP_EOL;
            }
        }

        if (count($this->headerCBlocks) > 0) {
            $code .= implode($this->headerCBlocks, PHP_EOL) . PHP_EOL;
        }

        /**
         * Prepend the required files to the header
         */
        $compilationContext->codePrinter->preOutput($code);
    }

    /**
     * Compiles a comment as a top-level statement
     *
     * @param CompilationContext $compilationContext
     * @param array $topStatement
     */
    public function compileComment(CompilationContext $compilationContext, $topStatement)
    {
        $compilationContext->codePrinter->output('/' . $topStatement['value'] . '/');
    }

    /**
     * Creates a definition for an interface
     *
     * @param string $namespace
     * @param array $topStatement
     */
    public function preCompileInterface($namespace, $topStatement)
    {
        $classDefinition = new ClassDefinition($namespace, $topStatement['name']);

        if (isset($topStatement['extends'])) {
            $classDefinition->setExtendsClass($this->getFullName($topStatement['extends']));
        }

        $classDefinition->setType('interface');

        if (isset($topStatement['definition'])) {
            $definition = $topStatement['definition'];

            /**
             * Register constants
             */
            if (isset($definition['constants'])) {
                foreach ($definition['constants'] as $constant) {
                    $classConstant = new ClassConstant($constant['name'], isset($constant['default']) ? $constant['default'] : null, isset($constant['docblock']) ? $constant['docblock'] : null);
                    $classDefinition->addConstant($classConstant);
                }
            }

            /**
             * Register methods
             */
            if (isset($definition['methods'])) {
                foreach ($definition['methods'] as $method) {
                    $classMethod = new ClassMethod($classDefinition, $method['visibility'], $method['name'], isset($method['parameters']) ? new ClassMethodParameters($method['parameters']) : null, null, isset($method['docblock']) ? $method['docblock'] : null, isset($method['return-type']) ? $method['return-type'] : null, $method);
                    $classDefinition->addMethod($classMethod, $method);
                }
            }
        }

        $this->classDefinition = $classDefinition;
    }

    /**
     * Creates the property shortcuts
     *
     * @param array $property
     * @param ClassDefinition $classDefinition
     * @throws CompilerException
     */
    protected function processShorcuts(array $property, ClassDefinition $classDefinition)
    {
        foreach ($property['shortcuts'] as $shortcut) {

            if (substr($property['name'], 0, 1) == '_') {
                $name = substr($property['name'], 1);
            } else {
                $name = $property['name'];
            }

            switch ($shortcut['name']) {

                case 'get':
                    $classDefinition->addMethod(new ClassMethod(
                        $classDefinition,
                        array('public'),
                        'get' . ucfirst($name),
                        null,
                        new StatementsBlock(array(
                            array(
                                'type' => 'return',
                                'expr' => array(
                                    'type' => 'property-access',
                                    'left' => array(
                                        'type' => 'variable',
                                        'value' => 'this'
                                    ),
                                    'right' => array(
                                        'type' => 'variable',
                                        'value' => $property['name']
                                    )
                                )
                            )
                        )),
                        isset($shortcut['docblock']) ? $shortcut['docblock'] : isset($property['docblock']) ? $property['docblock'] : null,
                        null,
                        $shortcut
                    ), $shortcut);
                    break;

                case 'set':
                    $classDefinition->addMethod(new ClassMethod(
                        $classDefinition,
                        array('public'),
                        'set' . ucfirst($name),
                        new ClassMethodParameters(array(
                            array(
                                'type' => 'parameter',
                                'name' => $name,
                                'const' => 0,
                                'data-type' => 'variable',
                                'mandatory' => 0
                            )
                        )),
                        new StatementsBlock(array(
                            array(
                                'type' => 'let',
                                'assignments' => array(
                                    array(
                                        'assign-type' => 'object-property',
                                        'operator' => 'assign',
                                        'variable' => 'this',
                                        'property' => $property['name'],
                                        'expr' => array(
                                            'type' => 'variable',
                                            'value' => $name,
                                        )
                                    )
                                )
                            )
                        )),
                        isset($shortcut['docblock']) ? $shortcut['docblock'] : isset($property['docblock']) ? $property['docblock'] : null,
                        null,
                        $shortcut
                    ), $shortcut);
                    break;

                case 'toString':
                case '__toString':
                    $classDefinition->addMethod(new ClassMethod(
                        $classDefinition,
                        array('public'),
                        '__toString',
                        null,
                        new StatementsBlock(array(
                            array(
                                'type' => 'return',
                                'expr' => array(
                                    'type' => 'property-access',
                                    'left' => array(
                                        'type' => 'variable',
                                        'value' => 'this'
                                    ),
                                    'right' => array(
                                        'type' => 'variable',
                                        'value' => $property['name']
                                    )
                                )
                            )
                        )),
                        isset($shortcut['docblock']) ? $shortcut['docblock'] : isset($property['docblock']) ? $property['docblock'] : null,
                        null,
                        $shortcut
                    ), $shortcut);
                    break;

                default:
                    throw new CompilerException("Unknown shortcut '" . $shortcut['name'] . "'", $shortcut);
            }
        }
    }

    /**
     * Creates a definition for a class
     *
     * @param string $namespace
     * @param array $topStatement
     */
    public function preCompileClass($namespace, $topStatement)
    {
        $classDefinition = new ClassDefinition($namespace, $topStatement['name']);

        if (isset($topStatement['extends'])) {
            $classDefinition->setExtendsClass($this->getFullName($topStatement['extends']));
        }

        if (isset($topStatement['implements'])) {
            foreach ($topStatement['implements'] as &$implement) {
                $implement['value'] = $this->getFullName($implement['value']);
            }
            $classDefinition->setImplementsInterfaces($topStatement['implements']);
        }

        if (isset($topStatement['abstract'])) {
            $classDefinition->setIsAbstract($topStatement['abstract']);
        }

        if (isset($topStatement['final'])) {
            $classDefinition->setIsFinal($topStatement['final']);
        }

        if (isset($topStatement['definition'])) {

            $definition = $topStatement['definition'];

            if (isset($definition['properties'])) {
                foreach ($definition['properties'] as $property) {
                    /**
                     * Add property to the definition
                     */
                    $classDefinition->addProperty(new ClassProperty(
                        $classDefinition,
                        $property['visibility'],
                        $property['name'],
                        isset($property['default']) ? $property['default'] : null,
                        isset($property['docblock']) ? $property['docblock'] : null,
                        $property
                    ));

                    /**
                     * Check and process shortcuts
                     */
                    if (isset($property['shortcuts'])) {
                        $this->processShorcuts($property, $classDefinition);
                    }
                }
            }

            /**
             * Register constants
             */
            if (isset($definition['constants'])) {
                foreach ($definition['constants'] as $constant) {
                    $classDefinition->addConstant(new ClassConstant(
                        $constant['name'],
                        isset($constant['default']) ? $constant['default'] : null,
                        isset($constant['docblock']) ? $constant['docblock'] : null
                    ));
                }
            }

            /**
             * Register methods
             */
            if (isset($definition['methods'])) {
                foreach ($definition['methods'] as $method) {
                    $classDefinition->addMethod(new ClassMethod(
                        $classDefinition,
                        $method['visibility'],
                        $method['name'],
                        isset($method['parameters']) ? new ClassMethodParameters($method['parameters']) : null,
                        isset($method['statements']) ? new StatementsBlock($method['statements']) : null,
                        isset($method['docblock']) ? $method['docblock'] : null,
                        isset($method['return-type']) ? $method['return-type'] : null,
                        $method
                    ), $method);
                }
            }
        }

        $this->classDefinition = $classDefinition;
    }

    /**
     * @throws ParseException
     * @throws CompilerException
     * @throws Exception
     */
    public function preCompile()
    {
        $ir = $this->genIR();

        if (!is_array($ir)) {
            throw new Exception("Cannot parse file: " . realpath($this->filePath));
        }

        if (isset($ir['type']) && $ir['type'] == 'error') {
            throw new ParseException($ir['message'], $ir);
        }

        /**
         * Alias Manager
         */
        $this->aliasManager = new AliasManager();

        /**
         * Traverse the top level statements looking for the namespace
         */
        $namespace = null;
        foreach ($ir as $topStatement) {
            switch ($topStatement['type']) {

                case 'namespace':
                    if ($namespace !== null) {
                        throw new CompilerException("The namespace must be defined just one time", $topStatement);
                    }
                    $namespace = $topStatement['name'];
                    $this->namespace = $namespace;
                    if (!preg_match('/^[A-Z]/', $namespace)) {
                        throw new CompilerException("Namespace '" . $namespace . "' must be in camelized-form", $topStatement);
                    }
                    break;

                case 'cblock':
                    $this->headerCBlocks[] = $topStatement['value'];
                    break;
            }
        }

        if (!$namespace) {
            throw new CompilerException("A namespace is required", $topStatement);
        }

        $class = false;
        $interface = false;

        foreach ($ir as $topStatement) {
            switch ($topStatement['type']) {

                case 'class':
                    if ($class || $interface) {
                        throw new CompilerException("More than one class/interface defined in the same file", $topStatement);
                    }
                    $class = true;
                    $name = $topStatement['name'];
                    $this->preCompileClass($namespace, $topStatement);
                    $this->originalNode = $topStatement;
                    break;

                case 'interface':
                    if ($class || $interface) {
                        throw new CompilerException("More than one class/interface defined in the same file", $topStatement);
                    }
                    $interface = true;
                    $name = $topStatement['name'];
                    $this->preCompileInterface($namespace, $topStatement);
                    $this->originalNode = $topStatement;
                    break;

                case 'use':
                    if ($interface || $class) {
                        throw new CompilerException("Aliasing must be done before declaring any class or interface", $topStatement);
                    }
                    $this->aliasManager->add($topStatement);
                    break;
            }
        }

        if (!$class && !$interface) {
            throw new CompilerException("Every file must contain at least a class or an interface", $topStatement);
        }

        if (strtolower($this->filePath) != strtolower(str_replace('\\', '/', $namespace) . '/' . $name) . '.zep') {
            throw new CompilerException('Unexpected class name ' . str_replace('\\', '/', $namespace) . '\\' . $name . ' in file: ' . $this->filePath);
        }

        $this->ir = $ir;
    }

    /**
     * Returns the path to the compiled file
     *
     * @return string
     */
    public function getCompiledFile()
    {
        return $this->compiledFile;
    }

    /**
     * Check dependencies
     *
     * @param \Compiler $compiler
     */
    public function checkDependencies(Compiler $compiler)
    {
        $classDefinition = $this->classDefinition;

        $extendedClass = $classDefinition->getExtendsClass();
        if ($extendedClass) {
            if ($classDefinition->getType() == 'class') {
                if ($compiler->isClass($extendedClass)) {
                    $extendedDefinition = $compiler->getClassDefinition($extendedClass);
                    $classDefinition->setExtendsClassDefinition($extendedDefinition);
                } else {
                    if ($compiler->isInternalClass($extendedClass)) {
                        $extendedDefinition = $compiler->getInternalClassDefinition($extendedClass);
                        $classDefinition->setExtendsClassDefinition($extendedDefinition);
                    } else {
                        throw new CompilerException('Cannot locate class "' . $extendedClass . '" when extending class "' . $classDefinition->getCompleteName() . '"', $this->originalNode);
                    }
                }
            } else {
                if ($compiler->isInterface($extendedClass)) {
                    $extendedDefinition = $compiler->getClassDefinition($extendedClass);
                    $classDefinition->setExtendsClassDefinition($extendedDefinition);
                } else {
                    if ($compiler->isInternalInterface($extendedClass)) {
                        $extendedDefinition = $compiler->getInternalClassDefinition($extendedClass);
                        $classDefinition->setExtendsClassDefinition($extendedDefinition);
                    } else {
                        throw new CompilerException('Cannot locate interface "' . $extendedClass . '" when extending interface "' . $classDefinition->getCompleteName() . '"', $this->originalNode);
                    }
                }
            }
        }
    }

    /**
     * Compiles the file
     *
     * @param \Compiler $compiler
     * @param \StringsManager $stringsManager
     */
    public function compile(Compiler $compiler, StringsManager $stringsManager)
    {

        if (!$this->ir) {
            throw new CompilerException('IR related to compiled file is missing');
        }

        /**
         * Compilation context stores common objects required by compilation entities
         */
        $compilationContext = new CompilationContext;

        /**
         * Set global compiler in the compilation context
         */
        $compilationContext->compiler = $compiler;

        /**
         * Set global config in the compilation context
         */
        $compilationContext->config = $this->config;

        /**
         * Set global logger in the compilation context
         */
        $compilationContext->logger = $this->logger;

        /**
         * Set global strings manager
         */
        $compilationContext->stringsManager = $stringsManager;

        /**
         * Headers manager
         */
        $headersManager = new HeadersManager();
        $compilationContext->headersManager = $headersManager;

        /**
         * Main code-printer for the file
         */
        $codePrinter = new CodePrinter();
        $compilationContext->codePrinter = $codePrinter;

        /**
         * Alias manager
         */
        $compilationContext->aliasManager = $this->aliasManager;

        $codePrinter->outputBlankLine();

        $class = false;
        $interface = false;
        foreach ($this->ir as $topStatement) {

            switch ($topStatement['type']) {

                case 'class':
                    if ($interface || $class) {
                        throw new CompilerException("More than one class defined in the same file", $topStatement);
                    }
                    $class = true;
                    $this->compileClass($compilationContext, $this->namespace, $topStatement);
                    break;

                case 'interface':
                    if ($interface || $class) {
                        throw new CompilerException("More than one class defined in the same file", $topStatement);
                    }
                    $class = true;
                    $this->compileClass($compilationContext, $this->namespace, $topStatement);
                    break;

                case 'comment':
                    $this->compileComment($compilationContext, $topStatement);
                    break;

            }
        }

        $classDefinition = $this->classDefinition;
        if (!$classDefinition) {
            $this->ir = null;
            return;
        }

        $classDefinition->setOriginalNode($this->originalNode);

        $completeName = $classDefinition->getCompleteName();

        $path = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($completeName));

        $filePath = 'ext/' . $path . '.zep.c';
        $filePathHeader = 'ext/' . $path . '.zep.h';

        if (strpos($path, DIRECTORY_SEPARATOR)) {
            $dirname = dirname($filePath);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0755, true);
            }
        }

        if ($codePrinter) {

            /**
             * If the file does not exists we create it for the first time
             */
            if (!file_exists($filePath)) {
                file_put_contents($filePath, $codePrinter->getOutput());
                if ($compilationContext->headerPrinter) {
                    file_put_contents($filePathHeader, $compilationContext->headerPrinter->getOutput());
                }
            } else {

                /**
                 * Use md5 hash to avoid rewrite the file again and again when it hasn't changed
                 * thus avoiding unnecesary recompilations
                 */
                $output = $codePrinter->getOutput();
                $hash = hash_file('md5', $filePath);
                if (md5($output) != $hash) {
                    file_put_contents($filePath, $output);
                }

                if ($compilationContext->headerPrinter) {
                    $output = $compilationContext->headerPrinter->getOutput();
                    $hash = hash_file('md5', $filePathHeader);
                    if (md5($output) != $hash) {
                        file_put_contents($filePathHeader, $output);
                    }
                }
            }
        }

        /**
         * Add to file compiled
         */
        $this->compiledFile = $path . '.c';
        $this->ir = null;
    }

    /**
     * Transform class/interface name to FQN format
     *
     * @param string $name
     * @return string
     */
    protected function getFullName($name)
    {
        return Utils::getFullName($name, $this->namespace, $this->aliasManager);
    }
}
