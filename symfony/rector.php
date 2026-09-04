<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withImportNames(importShortClasses: false)
    ->withPreparedSets(symfonyCodeQuality: true)
    ->withComposerBased(symfony: true)
    ->withConfiguredRule(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
    ]);
