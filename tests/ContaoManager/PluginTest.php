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

/**
 * This tests the plugin.
 *
 * @covers \CyberSpectrum\I18N\ContaoBundle\ContaoManager\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * Test
     *
     * @return void
     */
    public function testGetBundles(): void
    {
        $plugin  = new Plugin();
        $bundles = $plugin->getBundles($this->getMockForAbstractClass(ParserInterface::class));

        $this->assertCount(2, $bundles);
        /** @var ConfigInterface $bundle */
        $bundle = $bundles[0];
        $this->assertSame(CyberSpectrumI18NContaoBundle::class, $bundle->getName());
        $this->assertSame(
            [
                ContaoCoreBundle::class,
                CyberSpectrumI18NBundle::class,
                MetaModelsCoreBundle::class,
                FrameworkBundle::class,
            ],
            $bundle->getLoadAfter()
        );
        $bundle = $bundles[1];
        $this->assertSame(CyberSpectrumI18NBundle::class, $bundle->getName());
    }

    /**
     * Test
     *
     * @return void
     */
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

    /**
     * Test
     *
     * @return void
     */
    public function testGetRouteCollection(): void
    {
        $plugin   = new Plugin();
        $resolver = $this->getMockForAbstractClass(LoaderResolverInterface::class);
        $loader   = $this->getMockForAbstractClass(LoaderInterface::class);
        $basePath = realpath(__DIR__ . '/../../src/ContaoManager');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($basePath . '/../Resources/config/contao/routing.yml')
            ->willReturn($loader);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($basePath . '/../Resources/config/contao/routing.yml')
            ->willReturn($collection = $this->getMockBuilder(RouteCollection::class)->getMock());

        $this->assertSame($collection, $plugin->getRouteCollection(
            $resolver,
            $this->getMockForAbstractClass(KernelInterface::class)
        ));
    }
}
