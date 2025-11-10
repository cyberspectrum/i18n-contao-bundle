<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use CyberSpectrum\I18N\ContaoBundle\ContaoManager\Plugin;
use CyberSpectrum\I18N\ContaoBundle\CyberSpectrumI18NContaoBundle;
use CyberSpectrum\I18NBundle\CyberSpectrumI18NBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

use function realpath;

/** @covers \CyberSpectrum\I18N\ContaoBundle\ContaoManager\Plugin */
class PluginTest extends TestCase
{
    public function testGetBundles(): void
    {
        $plugin  = new Plugin();
        $bundles = $plugin->getBundles($this->getMockForAbstractClass(ParserInterface::class));

        self::assertCount(2, $bundles);
        /** @var ConfigInterface $bundle */
        $bundle = $bundles[0];
        self::assertSame(CyberSpectrumI18NContaoBundle::class, $bundle->getName());
        self::assertSame(
            [
                ContaoCoreBundle::class,
                CyberSpectrumI18NBundle::class,
                MetaModelsCoreBundle::class,
                FrameworkBundle::class,
            ],
            $bundle->getLoadAfter()
        );
        $bundle = $bundles[1];
        self::assertSame(CyberSpectrumI18NBundle::class, $bundle->getName());
    }

    public function testRegisterContainerConfiguration(): void
    {
        $plugin  = new Plugin();
        $loader  = $this->getMockForAbstractClass(LoaderInterface::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with('@CyberSpectrumI18NContaoBundle/Resources/contao-manager/framework.yml');

        $plugin->registerContainerConfiguration($loader, []);
    }
}
