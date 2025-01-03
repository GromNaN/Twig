<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Extension;

use Twig\Attribute\AsTwigCallable;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Define Twig filters, functions, and tests with PHP attributes.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
final class AttributeExtension extends AbstractExtension
{
    private array $classes;
    private array $callables;

    /**
     * A list of objects or class names defining filters, functions, and tests using PHP attributes.
     * When passing a class name, it must be available in runtimes.
     *
     * @param list<object|class-string> $classes
     */
    public function __construct(array $classes)
    {
        $this->classes = $classes;
    }

    public function getFilters(): array
    {
        if (!isset($this->callables)) {
            $this->initFromAttributes();
        }

        return array_values(array_filter($this->callables, static fn ($callable): bool => $callable instanceof TwigFilter));
    }

    public function getFunctions(): array
    {
        if (!isset($this->callables)) {
            $this->initFromAttributes();
        }

        return array_values(array_filter($this->callables, static fn ($callable): bool => $callable instanceof TwigFunction));
    }

    public function getTests(): array
    {
        if (!isset($this->callables)) {
            $this->initFromAttributes();
        }

        return array_values(array_filter($this->callables, static fn ($callable): bool => $callable instanceof TwigTest));
    }

    private function initFromAttributes()
    {
        $callables = [];

        foreach ($this->classes as $objectOrClass) {
            $reflectionClass = new \ReflectionClass($objectOrClass);
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($reflectionMethod->getAttributes(AsTwigCallable::class, \ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
                    $attribute = $reflectionAttribute->newInstance();
                    assert($attribute instanceof AsTwigCallable);
                    $callable = $attribute->getTwigCallable([$objectOrClass, $reflectionMethod->getName()], $reflectionMethod);

                    if ($callable->getMinimalNumberOfRequiredArguments() > $reflectionMethod->getNumberOfParameters()) {
                        throw new \LogicException(sprintf('"%s::%s()" needs at least %d arguments to be used %s, but only %d defined.',
                            $reflectionClass->getName(),
                            $reflectionMethod->getName(),
                            $callable->getMinimalNumberOfRequiredArguments(),
                            $reflectionAttribute->getName(),
                            $reflectionMethod->getNumberOfParameters(),
                        ));
                    }

                    $callables[] = $callable;
                }
            }
        }

        // Assign all at the end to avoid inconsistent state in case of exception
        $this->callables = array_values($callables);
    }

    public function getLastModified(): int
    {
        $lastModified = filemtime(__FILE__);
        foreach ($this->classes as $objectOrClass) {
            $reflectionClass = new \ReflectionClass($objectOrClass);
            if (is_file($filename = $reflectionClass->getFileName())) {
                $lastModified = max($lastModified, filemtime($filename));
            }
        }

        return $lastModified;
    }
}
