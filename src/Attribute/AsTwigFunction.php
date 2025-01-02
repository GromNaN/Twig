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
use Twig\TwigFunction;

/**
 * Registers a method as template function.
 *
 * If the first argument of the method has Twig\Environment type-hint, the function will receive the current environment.
 * If the next argument of the method is named $context and has array type-hint, the function will receive the current context.
 * Additional arguments of the method come from the function call.
 *
 *     #[AsTwigFunction('foo')]
 *     function fooFunction(Environment $env, array $context, string $string, $arg1 = null, ...) { ... }
 *
 *     {{ foo('string', arg1) }}
 *
 * @see TwigFunction
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsTwigFunction
{
    /**
     * @param non-empty-string $name The name of the function in Twig.
     * @param string[]|null $isSafe List of formats in which you want the raw output to be printed unescaped.
     * @param null|callable(Node):bool $isSafeCallback Function called at compilation time to determine if the function is safe.
     * @param DeprecatedCallableInfo|null $deprecationInfo Information about the deprecation
     */
    public function __construct(
        public string $name,
        public bool $needsCharset = false,
        public bool $needsContext = false,
        public ?array $isSafe = null,
        public mixed $isSafeCallback = null,
        public ?DeprecatedCallableInfo $deprecationInfo = null,
    ) {
    }
}
