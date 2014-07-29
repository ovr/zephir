<?php

namespace Zephir;

use Zephir\Compiler\Exception;
use Zephir\Compiler\File;

trait Parsers
{
    /**
     * @var Parser\ParserInterface[]
     */
    private $parsers;

    public function initParsers()
    {
        $parsers = $this->getConfig()->get('parsers');
        if (count($parsers) == 0) {
            throw new Exception('Please init parsers in project configuration file');
        }

        foreach($parsers as $key => $value) {
            $this->parsers[$key] = new $value['class']();
        }
    }

    /**
     * @param $extension
     * @return Parser\ParserInterface
     * @throws Compiler\Exception
     */
    public function getParser($extension)
    {
        if ($this->parsers[$extension]) {
            return $this->parsers[$extension];
        }

        throw new Exception('No parsers for file with '. $extension . ' extension');
    }
}
