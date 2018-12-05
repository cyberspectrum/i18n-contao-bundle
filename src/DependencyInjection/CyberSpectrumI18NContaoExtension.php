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

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection;

use Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle;
use MadeYourDay\RockSolidCustomElements\RockSolidCustomElementsBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class CyberSpectrumI18NContaoExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('contao/services.yml');
        $loader->load('contao/extractor.yml');
        if ($this->hasBundle(RockSolidCustomElementsBundle::class, $container)) {
            $loader->load('contao/rock-solid-custom-elements.yml');
        }
        if ($this->hasBundle('changelanguage', $container)) {
            $loader->load('contao/terminal42-change-language.yml');
        }
        // Only activate if MetaModels bundle present.
        if ($this->hasBundle(MetaModelsCoreBundle::class, $container)) {
            $loader->load('metamodels.yml');
        }
    }

    /**
     * Checks if the bundle is enabled.
     *
     * @param string           $class     The bundle class or bundle name.
     * @param ContainerBuilder $container The container builder.
     *
     * @return bool
     */
    private function hasBundle(string $class, ContainerBuilder $container): bool
    {
        if (\in_array($class, $bundles = $container->getParameter('kernel.bundles'), true)) {
            return true;
        }

        if (array_key_exists($class, $bundles) && $bundles[$class] === ContaoModuleBundle::class) {
            return true;
        }

        return false;
    }
}
