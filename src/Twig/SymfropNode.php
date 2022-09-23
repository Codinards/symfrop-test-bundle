<?php

namespace Njeaner\Symfrop\Twig;

use Twig\Compiler;
use Twig\Error\SyntaxError;
use Twig\Node\Node;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropNode extends Node
{
    public function __construct(Node $key, Node $body, ?Node $else, int $line, string $tag)
    {
        try {
            $name =  '"' . $key->getAttribute('value') . '"';
        } catch (SyntaxError | \Exception) {
            $name = '$context["' . $key->getAttribute('name') . '"]';
        }

        parent::__construct(
            ['key' => $key, 'body' => $body, 'else' => $else],
            ['name' =>  $name],
            $line,
            $tag
        );
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            // ->write('dd($context["name"]);');
            ->write('if($context["annotationManager"]->isAuthorize(' . $this->getAttribute('name') . ')){')
            ->subcompile($this->getNode('body'))
            ->write('}else{')
            ->subcompile($this->getNode('else'))
            ->write('}');
    }
}
