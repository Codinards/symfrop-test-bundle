<?php

namespace Njeaner\Symfrop\Twig;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Njeaner\Symfrop\Core\Manager\AnnotationManager;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropFormTokenParser extends AbstractTokenParser
{
    public function __construct(private AnnotationManager $annotationManager)
    {
    }
    public function parse(Token $token)
    {
        $line = $token->getLine();
        $stream = $this->parser->getStream();

        $key = $this->parser->getExpressionParser()->parseExpression();
        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideSymfropFork']);
        if ('elsesymfrop' == $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);
            $else = $this->parser->subparse([$this, 'decideSymfropEnd'], true);
        } else {
            $else = new Node();
        }
        $stream->expect(Token::BLOCK_END_TYPE);
        return new SymfropNode($key, $body, $else, $line, $this->getTag());
    }

    public function decideSymfropEnd(Token $token): bool
    {
        return $token->test('endsymfrop');
    }

    public function decideSymfropFork(Token $token): bool
    {
        return $token->test(['elsesymfrop', 'endsymfrop']);
    }

    public function getTag()
    {
        return 'symfrop';
    }
}
