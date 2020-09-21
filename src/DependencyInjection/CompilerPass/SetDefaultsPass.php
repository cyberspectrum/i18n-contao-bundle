<?php

/**
 * This file is part of cyberspectrum/i18n-contao-bundle.
 *
 * (c) 2018 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/i18n-contao-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2018 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/i18n-contao-bundle/blob/master/LICENSE MIT
 * @filesource
 */

declare(strict_types = 1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\ContaoDictionaryProvider;
use CyberSpectrum\I18N\Contao\Mapping\Terminal42ChangeLanguage\MapBuilder as Terminal42MapBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This pass updates the services to have sane default services.
 */
class SetDefaultsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
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
     * @return void
     *
     * @throws \RuntimeException When no default map builder has been defined.
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

        throw new \RuntimeException(
            'No default map builder defined, please install an extension that provides ' .
            '"cyberspectrum_i18n.contao.default_map_builder".'
        );
    }
}
