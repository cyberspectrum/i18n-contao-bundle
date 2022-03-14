<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\ContaoDictionaryDefinitionBuilder;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryDefinitionBuilder;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryProvider;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function array_merge;
use function assert;
use function is_array;

/**
 * This pass adds dictionary builders.
 */
class RegisterDictionaryTypesPass implements CompilerPassInterface
{
    /**
     * Collect all tagged dictionary providers.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return void
     *
     * @throws RuntimeException When a tag has no provider name or multiple services have been registered.
     */
    public function process(ContainerBuilder $container): void
    {
        $builders = ['contao' => new Reference(ContaoDictionaryDefinitionBuilder::class)];

        if ($container->hasDefinition(MetaModelDictionaryProvider::class)) {
            $builders['metamodels'] = new Reference(MetaModelDictionaryDefinitionBuilder::class);
        }

        $definition = $container->getDefinition('cyberspectrum_i18n.dictionary_definition_builders');
        $prevBuilders = $definition->getArgument(0);
        assert(is_array($prevBuilders));
        $definition->setArgument(0, array_merge($prevBuilders, $builders));
    }
}
