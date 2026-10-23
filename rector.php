<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php85\Rector\Property\AddOverrideAttributeToOverriddenPropertiesRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/database',
        __DIR__.'/public',
    ])
    ->withSkip([
        AddOverrideAttributeToOverriddenPropertiesRector::class,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withComposerBased(laravel: true)
    ->withSets([
        // Adds type hints and generic return types to improve Laravel code type safety.
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
        // Makes working with Laravel Factories easier and more IDE friendly.
        LaravelSetList::LARAVEL_FACTORIES,
        // Improves the usage of Laravel Collections by using simpler, more efficient, or more readable methods.
        LaravelSetList::LARAVEL_COLLECTION,
        // Changes the string or class const used for a service container make call.
        // https://laravel.com/docs/13.x/container#the-make-method
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        // Converts uses of things like $app['config'] to $app->make('config').
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        // Converts most string and array helpers into Str and Arr Facades' static calls.
        // https://laravel.com/docs/13.x/facades#facades-vs-helper-functions
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,

        // Some Rules we should enable later
        // @see https://github.com/driftingly/rector-laravel/blob/main/README.md#additional-sets

        // LaravelSetList::LARAVEL_CODE_QUALITY
        // LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER
        // LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES
        // LaravelSetList::LARAVEL_IF_HELPERS
        // LaravelSetList::LARAVEL_TESTING
    ])
    ->withPhpSets();
