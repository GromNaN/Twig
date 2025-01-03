<?php

namespace Twig\Attribute;

use Twig\Environment;
use Twig\TwigCallableInterface;

/**
 * Base class for all Twig attributes that define a Twig callable.
 */
abstract class AsTwigCallable
{
    /**
     * @param callable|array{class-string, string} $callable A callable implementing the function.
     */
    abstract public function getTwigCallable(string|array|\Closure $callable, \ReflectionFunctionAbstract $function): TwigCallableInterface;

    /**
     * Detect if the first argument of the method is the environment.
     */
    protected function needsEnvironment(\ReflectionFunctionAbstract $function): bool
    {
        if (!$parameters = $function->getParameters()) {
            return false;
        }

        return $parameters[0]->getType() instanceof \ReflectionNamedType
            && Environment::class === $parameters[0]->getType()->getName()
            && !$parameters[0]->isVariadic();
    }
}