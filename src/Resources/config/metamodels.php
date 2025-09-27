<?php

declare(strict_types=1);

use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectMetaModelAttributeHandlerPass;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryDefinitionBuilder;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryProvider;
use CyberSpectrum\I18N\MetaModels\MetaModelHandlerFactory;
use CyberSpectrum\I18N\MetaModels\MetaModelTextHandlerFactory;
use CyberSpectrum\I18NBundle\DependencyInjection\CompilerPass\CollectDictionaryProvidersPass;
use MetaModels\AttributeTranslatedLongtextBundle\Attribute\TranslatedLongtext;
use MetaModels\AttributeTranslatedTextBundle\Attribute\TranslatedText;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ServiceLocator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MetaModelDictionaryProvider::class)
        ->arg('$factory', service('metamodels.factory'))
        ->arg('$handlerFactory', service(MetaModelHandlerFactory::class))
        ->call('setLogger', [service('logger')])
        ->tag(CollectDictionaryProvidersPass::TAG_DICTIONARY_PROVIDER, ['provider' => 'metamodels']);

    $services->set(MetaModelHandlerFactory::class)
        ->arg('$handlerFactories', service('cyberspectrum_i18n.metamodels.attribute_handler_factories'));

    $services->set('cyberspectrum_i18n.metamodels.attribute_handler_factories', ServiceLocator::class)
        ->args([[]])
        ->tag('container.service_locator');

    /** @psalm-suppress UndefinedClass */
    $services->set(MetaModelTextHandlerFactory::class)
        ->tag(
            CollectMetaModelAttributeHandlerPass::TAG_ATTRIBUTE_HANDLER_FACTORY,
            ['type' => TranslatedText::class]
        )
        ->tag(
            CollectMetaModelAttributeHandlerPass::TAG_ATTRIBUTE_HANDLER_FACTORY,
            ['type' => TranslatedLongtext::class]
        );
    $services->set(MetaModelDictionaryDefinitionBuilder::class);
};
