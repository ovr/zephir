<?php

namespace Zephir;

class Parsers
{
    /**
     * @var Parser\ParserInterface[]
     */
    private $parsers;

    public function __construct(Compiler $compiler)
    {
        $parsers = $compiler->getConfig()->get('parsers');
        var_dump($parsers);
    }

    public function parse()
    {

    }
}
