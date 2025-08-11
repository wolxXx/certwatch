<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    //->withSkipPath(
        //'./Application/Util/Session.php'
    //)
    ->withTypeCoverageLevel(0)
    ->withParallel()
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withAttributesSets(doctrine: true)
    ->withRules([
                    \SavinMikhail\AddNamedArgumentsRector\AddNamedArgumentsRector::class
                ])

;
