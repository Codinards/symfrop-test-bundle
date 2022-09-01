<?php

namespace Njeaner\Symfrop\Command\Utils;

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 */
final class TemplateComponentGenerator
{
    public function generateRouteForControllerMethod(
        string $routePath,
        string $routeName,
        array $methods = [],
        array $requirements = [],
        bool $indent = true,
        bool $trailingNewLine = true
    ): string {
        $attribute = sprintf('%s#[Route(\'%s\', name: \'%s\'', $indent ? '    ' : null, $routePath, $routeName);

        if (!empty($methods)) {
            $attribute .= ', methods: [';

            foreach ($methods as $method) {
                $attribute .= sprintf('\'%s\', ', $method);
            }

            $attribute = rtrim($attribute, ', ');

            $attribute .= ']';
        }

        if (!empty($requirements)) {
            $attribute .= ', requirements: [';

            foreach ($requirements as $key => $requirement) {
                $attribute .= $key . ' => ' . $requirement . ', ';
            }

            $attribute = rtrim($attribute, ', ');

            $attribute .= ']';
        }

        $attribute .= sprintf(')]%s', $trailingNewLine ? "\n" : null);

        return $attribute;
    }

    public function getPropertyType(ClassNameDetails $classNameDetails): ?string
    {
        return sprintf('%s ', $classNameDetails->getShortName());
    }

    /**
     * @throws ReflectionException
     */
    public function repositoryHasAddRemoveMethods(string $repositoryFullClassName): bool
    {
        $reflectedComponents = new ReflectionClass($repositoryFullClassName);

        return $reflectedComponents->hasMethod('add') && $reflectedComponents->hasMethod('remove');
    }
}
