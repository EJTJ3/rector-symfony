<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony30\Rector\MethodCall\StringFormTypeToClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerXml(__DIR__ . '/../Source/custom_container.xml');
    $rectorConfig->rule(StringFormTypeToClassRector::class);
};
