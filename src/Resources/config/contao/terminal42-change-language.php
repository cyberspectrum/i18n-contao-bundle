<?php

declare(strict_types=1);

use CyberSpectrum\I18N\Contao\Mapping\Terminal42ChangeLanguage\ContaoDatabase;
use CyberSpectrum\I18N\Contao\Mapping\Terminal42ChangeLanguage\MapBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ContaoDatabase::class)->arg('$connection', service('database_connection'));

    $services->set(MapBuilder::class)
        ->arg('$database', service(ContaoDatabase::class))
        ->call('setLogger', [service('logger')]);
};
