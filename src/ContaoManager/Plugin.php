<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use CyberSpectrum\I18NBundle\CyberSpectrumI18NBundle;
use CyberSpectrum\I18N\ContaoBundle\CyberSpectrumI18NContaoBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Plugin for the Contao Manager.
 *
 * @api
 */
final class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(CyberSpectrumI18NContaoBundle::class)
                ->setLoadAfter(
                    [
                        FrameworkBundle::class,
                        ContaoCoreBundle::class,
                        MetaModelsCoreBundle::class,
                        CyberSpectrumI18NBundle::class,
                    ]
                ),
            BundleConfig::create(CyberSpectrumI18NBundle::class)
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        $loader->load('@CyberSpectrumI18NContaoBundle/Resources/contao-manager/framework.yml');
    }
}
