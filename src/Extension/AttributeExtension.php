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
use Twig\TwigCallableInterface;

/**
 * Define Twig filters, functions, and tests with PHP attributes.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
final class AttributeExtension extends AbstractExtension
{
    private array $filters;
    private array $functions;
    private array $tests;

    /**
     * A list of objects or class names defining filters, functions, and tests using PHP attributes.
     * When passing a class name, it must be available in runtimes.
     */
    public function __construct(private \Closure $callablesExtractor, private \Closure $lastModified)
    {
    }


    public function getFilters(): array
    {
        if (!isset($this->filters)) {
            $this->initTwigCallables();
        }

        return $this->filters;
    }

    public function getFunctions(): array
    {
        if (!isset($this->functions)) {
            $this->initTwigCallables();
        }

        return $this->functions;
    }

    public function getTests(): array
    {
        if (!isset($this->tests)) {
            $this->initTwigCallables();
        }

        return $this->tests;
    }

    private function initTwigCallables(): void
    {
        $twigCallables = ['filter' => [], 'function' => [], 'test' => []];

        foreach(($this->callablesExtractor)() as $twigCallable) {
            if (!$twigCallable instanceof TwigCallableInterface) {
                throw new \LogicException(sprintf('"%s" is not a valid Twig callable.', get_debug_type($twigCallable)));
            }

            $twigCallables[$twigCallable->getType()][] = $twigCallable;
        }

        $this->filters = $twigCallables['filter'];
        $this->functions = $twigCallables['function'];
        $this->tests = $twigCallables['test'];

        unset($this->callablesExtractor);
    }

    /**
     * A list of objects or class names defining filters, functions, and tests using PHP attributes.
     * When passing a class name, it must be available in runtimes.
     *
     * @param list<object|class-string> $classes
     */
    public static function createFromClassList(array $classes): self
    {
        return new self(
            static fn () => self::extractFromAttributes($classes),
            static function () use ($classes) {
                return max(array_map(static fn ($objectOrClass) => is_file($filename = (new \ReflectionClass($objectOrClass))->getFileName()) ? filemtime($filename) : 0, $classes));
            }
        );
    }

    private static function extractFromAttributes(array $classes)
    {
        $twigCallables = [];

        foreach ($classes as $objectOrClass) {
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

                    $twigCallables[] = $callable;
                }
            }
        }

        return $twigCallables;
    }

    public function getLastModified(): int
    {
        return max(filemtime(__FILE__), ($this->lastModified)());
    }
}
