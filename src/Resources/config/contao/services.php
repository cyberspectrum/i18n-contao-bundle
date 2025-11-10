<?php

declare(strict_types=1);

use CyberSpectrum\I18N\Contao\ContaoDictionaryDefinitionBuilder;
use CyberSpectrum\I18N\Contao\ContaoDictionaryProvider;
use CyberSpectrum\I18N\Contao\ExtractorFactory;
use CyberSpectrum\I18N\ContaoBundle\Command\DebugMapCommand;
use CyberSpectrum\I18NBundle\DependencyInjection\CompilerPass\CollectDictionaryProvidersPass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ContaoDictionaryProvider::class)
        ->arg('$connection', service('database_connection'))
        ->arg('$extractorFactory', service(ExtractorFactory::class))
        ->arg('$mapBuilder', service('cyberspectrum_i18n.contao.default_map_builder'))
        ->arg('$dictionaryMeta', [])
        ->call('setLogger', [service('logger')])
        ->tag(CollectDictionaryProvidersPass::TAG_DICTIONARY_PROVIDER, ['provider' => 'contao']);

    // FIXME: add some cache here.
    $services->set('cyberspectrum_i18n.expression_language')
        ->class(ExpressionLanguage::class);

    $services->set(ExtractorFactory::class)
        ->arg('$tableExtractors', param('cyberspectrum_i18n.contao.extractors'))
        ->arg('$extractorContainer', service('cyberspectrum_i18n.contao.extractor_container'))
        ->call('setLogger', [service('logger')]);

    $services->set('cyberspectrum_i18n.contao.extractor_container')
        ->class(ServiceLocator::class)
        ->args([[]])
        ->tag('container.service_locator');

    $services->set(ContaoDictionaryDefinitionBuilder::class);

    $services->set(DebugMapCommand::class)
            ->arg('$mapBuilder', service('cyberspectrum_i18n.contao.default_map_builder'))
            ->tag('console.command');
};
