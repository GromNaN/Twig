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
use Twig\Node\Node;
use Twig\TwigCallableInterface;
use Twig\TwigFilter;

/**
 * Registers a method as template filter.
 *
 * If the first argument of the method has Twig\Environment type-hint, the filter will receive the current environment.
 * If the next argument of the method is named $context and has array type-hint, the filter will receive the current context.
 * Additional arguments of the method come from the filter call.
 *
 *     #[AsTwigFilter('foo')]
 *     function fooFilter(Environment $env, array $context, $string, $arg1 = null, ...) { ... }
 *
 *    {{ 'string'|foo(arg1) }}
 *
 * @see TwigFilter
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsTwigFilter
{
    /**
     * @param non-empty-string $name The name of the filter in Twig.
     * @param string[]|null $isSafe List of formats in which you want the raw output to be printed unescaped.
     * @param null|callable(Node):bool $isSafeCallback Function called at compilation time to determine if the filter is safe.
     * @param string|null $preEscape Some filters may need to work on input that is already escaped or safe, for
     *                               example when adding (safe) HTML tags to originally unsafe output. In such a
     *                               case, set preEscape to an escape format to escape the input data before it
     *                               is run through the filter.
     * @param string[]|null $preservesSafety Preserves the safety of the value that the filter is applied to.
     * @param DeprecatedCallableInfo|null $deprecationInfo Information about the deprecation
     */
    public function __construct(
        public string $name,
        public ?bool $needsEnvironment = null,
        public bool $needsCharset = false,
        public bool $needsContext = false,
        public ?array $isSafe = null,
        public mixed $isSafeCallback = null,
        public ?string $preEscape = null,
        public ?array $preservesSafety = null,
        public ?DeprecatedCallableInfo $deprecationInfo = null,
    ) {
    }
}
