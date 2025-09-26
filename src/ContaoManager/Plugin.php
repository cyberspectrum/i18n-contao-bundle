<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use CyberSpectrum\I18NBundle\CyberSpectrumI18NBundle;
use CyberSpectrum\I18N\ContaoBundle\CyberSpectrumI18NContaoBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Plugin for the Contao Manager.
 */
final class Plugin implements BundlePluginInterface, ConfigPluginInterface, RoutingPluginInterface
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

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    #[\Override]
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
    {
        $loader = $resolver->resolve(__DIR__ . '/../Resources/config/contao/routing.yml');

        if (!$loader instanceof LoaderInterface) {
            throw new \RuntimeException('Failed to load routes');
        }

        return $loader
            ->load(__DIR__ . '/../Resources/config/contao/routing.yml');
    }
}
