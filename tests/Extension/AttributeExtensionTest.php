<?php

namespace Twig\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Twig\DeprecatedCallableInfo;
use Twig\Extension\AttributeExtension;
use Twig\Tests\Extension\Fixtures\FilterWithoutValue;
use Twig\Tests\Extension\Fixtures\TestWithoutValue;
use Twig\Tests\Extension\Fixtures\ExtensionWithAttributes;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Nested attributes are not supported in PHP 8.0.
 * @requires PHP >= 8.1
 */
class AttributeExtensionTest extends TestCase
{
    /**
     * @dataProvider provideFilters
     */
    public function testFilter(string $name, string $method, array $options)
    {
        foreach ([new ExtensionWithAttributes(), ExtensionWithAttributes::class] as $object) {
            $found = false;
            $extension = new AttributeExtension([$object]);
            foreach ($extension->getFilters() as $filter) {
                if ($filter->getName() === $name) {
                    $found = true;
                    $this->assertEquals(new TwigFilter($name, [$object, $method], $options), $filter);
                }
            }

            $this->assertTrue($found, sprintf('Filter "%s" is not registered.', $name));
        }
    }

    public static function provideFilters()
    {
        yield 'with name' => ['foo', 'fooFilter', ['is_safe' => ['html']]];
        yield 'with env' => ['with_env_filter', 'withEnvFilter', ['needs_environment' => true]];
        yield 'with context' => ['with_context_filter', 'withContextFilter', ['needs_context' => true]];
        yield 'with env and context' => ['with_env_and_context_filter', 'withEnvAndContextFilter', ['needs_environment' => true, 'needs_context' => true]];
        yield 'variadic' => ['variadic_filter', 'variadicFilter', ['is_variadic' => true]];
        yield 'deprecated' => ['deprecated_filter', 'deprecatedFilter', ['deprecation_info' => new DeprecatedCallableInfo('foo/bar', '1.2')]];
        yield 'pattern' => ['pattern_*_filter', 'patternFilter', []];
    }

    /**
     * @dataProvider provideFunctions
     */
    public function testFunction(string $name, string $method, array $options)
    {
        foreach ([new ExtensionWithAttributes(), ExtensionWithAttributes::class] as $object) {
            $found = false;
            $extension = new AttributeExtension([$object]);
            foreach ($extension->getFunctions() as $function) {
                if ($function->getName() === $name) {
                    $found = true;
                    $this->assertEquals(new TwigFunction($name, [$object, $method], $options), $function);
                }
            }

            $this->assertTrue($found, sprintf('Function "%s" is not registered.', $name));
        }
    }

    public static function provideFunctions()
    {
        yield 'with name' => ['foo', 'fooFunction', ['is_safe' => ['html']]];
        yield 'with env' => ['with_env_function', 'withEnvFunction', ['needs_environment' => true]];
        yield 'with context' => ['with_context_function', 'withContextFunction', ['needs_context' => true]];
        yield 'with env and context' => ['with_env_and_context_function', 'withEnvAndContextFunction', ['needs_environment' => true, 'needs_context' => true]];
        yield 'no argument' => ['no_arg_function', 'noArgFunction', []];
        yield 'variadic' => ['variadic_function', 'variadicFunction', ['is_variadic' => true]];
        yield 'deprecated' => ['deprecated_function', 'deprecatedFunction', ['deprecation_info' => new DeprecatedCallableInfo('foo/bar', '1.2')]];
    }

    /**
     * @dataProvider provideTests
     */
    public function testTest(string $name, string $method, array $options)
    {
        foreach ([new ExtensionWithAttributes(), ExtensionWithAttributes::class] as $object) {
            $extension = new AttributeExtension([$object]);
            foreach ($extension->getTests() as $test) {
                $found = false;
                if ($test->getName() === $name) {
                    $found = true;
                    $this->assertEquals(new TwigTest($name, [$object, $method], $options), $test);
                }
            }

            $this->assertTrue($found, sprintf('Test "%s" is not registered.', $name));
        }
    }

    public static function provideTests()
    {
        yield 'with name' => ['foo', 'fooTest', []];
        yield 'with env' => ['with_env_test', 'withEnvTest', ['needs_environment' => true]];
        yield 'with context' => ['with_context_test', 'withContextTest', ['needs_context' => true]];
        yield 'with env and context' => ['with_env_and_context_test', 'withEnvAndContextTest', ['needs_environment' => true, 'needs_context' => true]];
        yield 'variadic' => ['variadic_test', 'variadicTest', ['is_variadic' => true]];
        yield 'deprecated' => ['deprecated_test', 'deprecatedTest', ['deprecation_info' => new DeprecatedCallableInfo('foo/bar', '1.2')]];
    }

    public function testRuntimeExtension()
    {
        $class = ExtensionWithAttributes::class;
        $extension = new AttributeExtension([$class]);

        $this->assertSame([$class, 'fooFilter'], $extension->getFilters()[0]->getCallable());
        $this->assertSame([$class, 'fooFunction'], $extension->getFunctions()[0]->getCallable());
        $this->assertSame([$class, 'fooTest'], $extension->getTests()[0]->getCallable());
    }

    public function testFilterRequireOneArgument()
    {
        $extension = new AttributeExtension([FilterWithoutValue::class]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"'.FilterWithoutValue::class.'::myFilter()" needs at least 1 arguments to be used AsTwigFilter, but only 0 defined.');

        $extension->getTests();
    }

    public function testTestRequireOneArgument()
    {
        $extension = new AttributeExtension([TestWithoutValue::class]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"'.TestWithoutValue::class.'::myTest()" needs at least 1 arguments to be used AsTwigTest, but only 0 defined.');

        $extension->getTests();
    }
}
