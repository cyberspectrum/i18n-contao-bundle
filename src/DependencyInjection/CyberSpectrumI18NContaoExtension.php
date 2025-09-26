<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection;

use Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle;
use MadeYourDay\RockSolidCustomElements\RockSolidCustomElementsBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Terminal42\ChangeLanguage\Terminal42ChangeLanguageBundle;

use function array_key_exists;
use function in_array;

/**
 * This is the class that loads and manages the bundle configuration
 */
class CyberSpectrumI18NContaoExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('contao/services.yml');
        $loader->load('contao/extractor.yml');
        /** @psalm-suppress UndefinedClass - we do not need this class, this check is for feature detection only */
        if ($this->hasBundle(RockSolidCustomElementsBundle::class, $container)) {
            $loader->load('contao/rock-solid-custom-elements.yml');
        }
        /** @psalm-suppress UndefinedClass - we do not need this class, this check is for feature detection only */
        if (
            $this->hasBundle('changelanguage', $container)
            || $this->hasBundle(Terminal42ChangeLanguageBundle::class, $container)
        ) {
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
     */
    private function hasBundle(string $class, ContainerBuilder $container): bool
    {
        /**
         * @var array<string, string> $bundles
         * @psalm-suppress UndefinedDocblockClass - UnitEnum is added in PHP 8.1 and therefore throws in PHP 7.4.
         */
        $bundles = $container->getParameter('kernel.bundles');
        if (in_array($class, $bundles, true)) {
            return true;
        }

        if (array_key_exists($class, $bundles) && $bundles[$class] === ContaoModuleBundle::class) {
            return true;
        }

        return false;
    }
}
