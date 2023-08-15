<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerXml(__DIR__ . '/../xml/services.xml');
    $rectorConfig->rule(GetToConstructorInjectionRector::class);
};
