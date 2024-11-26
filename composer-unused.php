<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;
use ComposerUnused\ComposerUnused\Configuration\PatternFilter;
use Webmozart\Glob\Glob;

return static function (Configuration $config): Configuration {
    $config


        // Ignore specific packages that are used indirectly or via configuration
        ->addNamedFilter(NamedFilter::fromString('symfony/dotenv'))
        ->addNamedFilter(NamedFilter::fromString('symfony/apache-pack'))


        // Ignore all Symfony UX packages
        ->addPatternFilter(PatternFilter::fromString('/^symfony\/ux-/'))

        // Include additional configuration files that may use dependencies
        ->setAdditionalFilesFor('symfony/framework-bundle', [
            ...Glob::glob(__DIR__ . '/config/{packages,packages/dev}/*.yaml'),
        ])
        ->setAdditionalFilesFor('twig/twig', [
            ...Glob::glob(__DIR__ . '/templates/**/*.twig'),
        ]);

    // Handle dependencies based on PHP version
    if (PHP_VERSION_ID >= 80100) {
        $config->addNamedFilter(NamedFilter::fromString('symfony/property-access'));
    }

    return $config;
};