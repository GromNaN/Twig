<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Attribute;

use Twig\DeprecatedCallableInfo;
use Twig\TwigTest;

/**
 * Registers a method as template test.
 *
 * The first argument is the value to test and the other arguments are the
 * arguments passed to the test in the template.
 *
 *     #[AsTwigTest('foo')]
 *     public function fooTest($value, $arg1 = null) { ... }
 *
 *     {% if value is foo(arg1) %}
 *
 * @see TwigTest
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsTwigTest extends AsTwigCallable
{
    /**
     * @param non-empty-string $name The name of the test in Twig.
     * @param DeprecatedCallableInfo|null $deprecationInfo Information about the deprecation
     */
    public function __construct(
        public string $name,
        public ?bool $needsEnvironment = null,
        public bool $needsCharset = false,
        public bool $needsContext = false,
        public ?DeprecatedCallableInfo $deprecationInfo = null,
    ) {
    }

    public function getTwigCallable(array|string|\Closure $callable, \ReflectionFunctionAbstract $function): TwigTest
    {
        return new TwigTest($this->name, $callable, [
            'needs_environment' => $this->needsEnvironment ?? $this->needsEnvironment($function),
            'needs_context' => $this->needsContext,
            'needs_charset' => $this->needsCharset,
            'is_variadic' => $function->isVariadic(),
            'deprecation_info' => $this->deprecationInfo,
        ]);
    }
}
