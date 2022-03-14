<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\ContaoDictionaryProvider;
use CyberSpectrum\I18N\Contao\Mapping\Terminal42ChangeLanguage\MapBuilder as Terminal42MapBuilder;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This pass updates the services to have sane default services.
 */
class SetDefaultsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(ContaoDictionaryProvider::class)) {
            $this->setDefaultMapBuilder($container);
        }
    }

    /**
     * Set the default map builder from the available builders.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @throws RuntimeException When no default map builder has been defined.
     */
    private function setDefaultMapBuilder(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('cyberspectrum_i18n.contao.default_map_builder')) {
            return;
        }
        if ($container->hasDefinition(Terminal42MapBuilder::class)) {
            $container->setAlias('cyberspectrum_i18n.contao.default_map_builder', Terminal42MapBuilder::class);
            return;
        }

        throw new RuntimeException(
            'No default map builder defined, please install an extension that provides ' .
            '"cyberspectrum_i18n.contao.default_map_builder".'
        );
    }
}
