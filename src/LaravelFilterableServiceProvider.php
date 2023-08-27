<?php

namespace Albet\LaravelFilterable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelFilterableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-filterable')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-filterable_table');
    }
}
