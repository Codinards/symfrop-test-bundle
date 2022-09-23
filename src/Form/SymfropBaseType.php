<?php

namespace Njeaner\Symfrop\Form;

use Njeaner\Symfrop\Twig\SymfropTwigExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropBaseType extends AbstractType
{
    private ?FormBuilderInterface $builder = null;

    public function __construct(
        private SymfropTwigExtension $translator
    ) {
    }

    public function setBuilder(FormBuilderInterface $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function add(string|FormBuilderInterface $child, ?string $type = null, array $options = []): self
    {
        $label = is_string($child) ? $child : $child->getName();

        $this->builder->add($child, $type, array_merge($options, [
            'label' => ($options['label'] ?? $this->translator->__u($label)) . (isset($options['required']) ? ' * ' : '')
        ]));

        return $this;
    }
}
