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

use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Attribute\AsTwigTest;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Define Twig filters, functions, and tests with PHP attributes.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 *
 * @internal
 */
final class AttributeExtension extends AbstractExtension
{
    private array $classes;
    private array $filters;
    private array $functions;
    private array $tests;

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
        if (!isset($this->filters)) {
            $this->initFromAttributes();
        }

        return $this->filters;
    }

    public function getFunctions(): array
    {
        if (!isset($this->functions)) {
            $this->initFromAttributes();
        }

        return $this->functions;
    }

    public function getTests(): array
    {
        if (!isset($this->tests)) {
            $this->initFromAttributes();
        }

        return $this->tests;
    }

    private function initFromAttributes()
    {
        $filters = $functions = $tests = [];

        foreach ($this->classes as $objectOrClass) {
            $reflectionClass = new \ReflectionClass($objectOrClass);

            foreach ($reflectionClass->getMethods() as $method) {
                foreach ($method->getAttributes(AsTwigFilter::class) as $reflectionAttribute) {
                    /** @var AsTwigFilter $attribute */
                    $attribute = $reflectionAttribute->newInstance();

                    $callable = new TwigFilter($attribute->name, [$objectOrClass, $method->getName()], [
                        'needs_environment' => $attribute->needsEnvironment ?? $this->needsEnvironment($method),
                        'needs_context' => $attribute->needsContext,
                        'needs_charset' => $attribute->needsCharset,
                        'is_variadic' => $method->isVariadic(),
                        'is_safe' => $attribute->isSafe,
                        'is_safe_callback' => $attribute->isSafeCallback,
                        'pre_escape' => $attribute->preEscape,
                        'preserves_safety' => $attribute->preservesSafety,
                        'deprecation_info' => $attribute->deprecationInfo,
                    ]);

                    if ($callable->getMinimalNumberOfRequiredArguments() > $method->getNumberOfParameters()) {
                        throw new \LogicException(sprintf('"%s::%s()" needs at least %d arguments to be used AsTwigFilter, but only %d defined.', $reflectionClass->getName(), $method->getName(), $callable->getMinimalNumberOfRequiredArguments(), $method->getNumberOfParameters()));
                    }

                    $filters[$attribute->name] = $callable;
                }

                foreach ($method->getAttributes(AsTwigFunction::class) as $reflectionAttribute) {
                    /** @var AsTwigFunction $attribute */
                    $attribute = $reflectionAttribute->newInstance();

                    $callable = new TwigFunction($attribute->name, [$objectOrClass, $method->getName()], [
                        'needs_environment' => $attribute->needsEnvironment ?? $this->needsEnvironment($method),
                        'needs_context' => $attribute->needsContext,
                        'needs_charset' => $attribute->needsCharset,
                        'is_variadic' => $method->isVariadic(),
                        'is_safe' => $attribute->isSafe,
                        'is_safe_callback' => $attribute->isSafeCallback,
                        'deprecation_info' => $attribute->deprecationInfo,
                    ]);

                    if ($callable->getMinimalNumberOfRequiredArguments() > $method->getNumberOfParameters()) {
                        throw new \LogicException(sprintf('"%s::%s()" needs at least %d arguments to be used AsTwigFunction, but only %d defined.', $reflectionClass->getName(), $method->getName(), $callable->getMinimalNumberOfRequiredArguments(), $method->getNumberOfParameters()));
                    }

                    $functions[$attribute->name] = $callable;
                }

                foreach ($method->getAttributes(AsTwigTest::class) as $reflectionAttribute) {

                    /** @var AsTwigTest $attribute */
                    $attribute = $reflectionAttribute->newInstance();

                    $callable = new TwigTest($attribute->name, [$objectOrClass, $method->getName()], [
                        'needs_environment' => $attribute->needsEnvironment ?? $this->needsEnvironment($method),
                        'needs_context' => $attribute->needsContext,
                        'needs_charset' => $attribute->needsCharset,
                        'is_variadic' => $method->isVariadic(),
                        'deprecation_info' => $attribute->deprecationInfo,
                    ]);

                    if ($callable->getMinimalNumberOfRequiredArguments() > $method->getNumberOfParameters()) {
                        throw new \LogicException(sprintf('"%s::%s()" needs at least %d arguments to be used AsTwigTest, but only %d defined.', $reflectionClass->getName(), $method->getName(), $callable->getMinimalNumberOfRequiredArguments(), $method->getNumberOfParameters()));
                    }

                    $tests[$attribute->name] = $callable;
                }
            }
        }

        // Assign all at the end to avoid inconsistent state in case of exception
        $this->filters = array_values($filters);
        $this->functions = array_values($functions);
        $this->tests = array_values($tests);
    }

    /**
     * Detect if the first argument of the method is the environment.
     */
    private function needsEnvironment(\ReflectionFunctionAbstract $function): bool
    {
        if (!$parameters = $function->getParameters()) {
            return false;
        }

        return $parameters[0]->getType() instanceof \ReflectionNamedType
            && Environment::class === $parameters[0]->getType()->getName()
            && !$parameters[0]->isVariadic();
    }
}
