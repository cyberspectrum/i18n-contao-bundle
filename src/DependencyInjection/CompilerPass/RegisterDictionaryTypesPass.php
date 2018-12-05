<?php

/**
 * This file is part of cyberspectrum/i18n-bundle.
 *
 * (c) 2018 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/i18n-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2018 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/i18n-bundle/blob/master/LICENSE MIT
 * @filesource
 */

declare(strict_types = 1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\ContaoDictionaryDefinitionBuilder;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryDefinitionBuilder;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
     * @throws \RuntimeException When a tag has no provider name or multiple services have been registered.
     */
    public function process(ContainerBuilder $container): void
    {
        $builders = ['contao' => new Reference(ContaoDictionaryDefinitionBuilder::class)];

        if ($container->hasDefinition(MetaModelDictionaryProvider::class)) {
            $builders['metamodels'] = new Reference(MetaModelDictionaryDefinitionBuilder::class);
        }

        $definition = $container->getDefinition('cyberspectrum_i18n.dictionary_definition_builders');
        $definition->setArgument(0, array_merge($definition->getArgument(0), $builders));
    }
}
