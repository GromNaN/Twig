<?php

namespace Twig\Tests\Extension\Fixtures;

use Twig\Attribute\AsTwigExtension;
use Twig\Attribute\AsTwigFilter;

#[AsTwigExtension]
class FilterWithoutValue
{
    #[AsTwigFilter('my_filter')]
    public function myFilter()
    {
    }
}