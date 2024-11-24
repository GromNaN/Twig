<?php

namespace Twig\Tests\Extension\Fixtures;

use Twig\Attribute\AsTwigExtension;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Attribute\AsTwigTest;
use Twig\DeprecatedCallableInfo;
use Twig\Environment;

#[AsTwigExtension]
class ExtensionWithAttributes
{
    #[AsTwigFilter(name: 'foo', isSafe: ['html'])]
    public function fooFilter(string|int $string)
    {
    }

    #[AsTwigFilter('with_context_filter')]
    public function withContextFilter(array $context, string $string)
    {
    }

    #[AsTwigFilter('no_context_filter')]
    public function noContextFilter($context)
    {
    }

    #[AsTwigFilter('with_env_filter')]
    public function withEnvFilter(Environment $env, string $string)
    {
    }

    #[AsTwigFilter('with_env_and_context_filter')]
    public function withEnvAndContextFilter(Environment $env, array $context, array $data)
    {
    }

    #[AsTwigFilter('variadic_filter')]
    public function variadicFilter(string ...$strings)
    {
    }

    #[AsTwigFilter('deprecated_filter', deprecationInfo: new DeprecatedCallableInfo('foo/bar', '1.2'))]
    public function deprecatedFilter(string $string)
    {
    }

    #[AsTwigFilter('pattern_*_filter')]
    public function patternFilter(string $string)
    {
    }

    #[AsTwigFunction(name: 'foo', isSafe: ['html'])]
    public function fooFunction(string|int $string)
    {
    }

    #[AsTwigFunction('with_context_function')]
    public function withContextFunction(array $context, string $string)
    {
    }

    #[AsTwigFunction('no_context_function')]
    public function noContextFunction($context)
    {
    }

    #[AsTwigFunction('with_env_function')]
    public function withEnvFunction(Environment $env, string $string)
    {
    }

    #[AsTwigFunction('with_env_and_context_function')]
    public function withEnvAndContextFunction(Environment $env, array $context, string $string)
    {
    }

    #[AsTwigFunction('no_arg_function')]
    public function noArgFunction()
    {
    }

    #[AsTwigFunction('variadic_function')]
    public function variadicFunction(string ...$strings)
    {
    }

    #[AsTwigFunction('deprecated_function', deprecationInfo: new DeprecatedCallableInfo('foo/bar', '1.2'))]
    public function deprecatedFunction(string $string)
    {
    }

    #[AsTwigTest(name: 'foo')]
    public function fooTest(string|int $value)
    {
    }

    #[AsTwigTest('variadic_test')]
    public function variadicTest(string ...$value)
    {
    }

    #[AsTwigTest('deprecated_test', deprecationInfo: new DeprecatedCallableInfo('foo/bar', '1.2'))]
    public function deprecatedTest($value, $argument)
    {
    }
}
