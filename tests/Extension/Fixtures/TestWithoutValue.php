<?php

namespace Twig\Tests\Extension\Fixtures;

use Twig\Attribute\AsTwigExtension;
use Twig\Attribute\AsTwigTest;

#[AsTwigExtension]
class TestWithoutValue
{
    #[AsTwigTest('my_test')]
    public function myTest()
    {
    }
}