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

namespace CyberSpectrum\I18N\ContaoBundle\Test;

use CyberSpectrum\I18N\ContaoBundle\CyberSpectrumI18NContaoBundle;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectMetaModelAttributeHandlerPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\RegisterDictionaryTypesPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\SetDefaultsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\UpdateForRockSolidCustomElementPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This tests the bundle.
 *
 * @covers \CyberSpectrum\I18N\ContaoBundle\CyberSpectrumI18NContaoBundle
 */
class CyberSpectrumI18NContaoBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['addCompilerPass'])
            ->getMock();

        $arguments = [];
        $container
            ->expects($this->exactly(6))
            ->method('addCompilerPass')
            ->willReturnCallback(function () use (&$arguments) {
                $arguments[] = func_get_args();
            });

        $bundle = new CyberSpectrumI18NContaoBundle();

        $bundle->build($container);

        $this->assertInstanceOf(CollectContaoExtractorsPass::class, $arguments[0][0]);
        $this->assertInstanceOf(CollectExtractorConditionsPass::class, $arguments[1][0]);
        $this->assertInstanceOf(CollectMetaModelAttributeHandlerPass::class, $arguments[2][0]);
        $this->assertInstanceOf(SetDefaultsPass::class, $arguments[3][0]);
        $this->assertInstanceOf(UpdateForRockSolidCustomElementPass::class, $arguments[4][0]);
        $this->assertSame(PassConfig::TYPE_BEFORE_OPTIMIZATION, $arguments[4][1]);
        $this->assertSame(10, $arguments[4][2]);
        $this->assertInstanceOf(RegisterDictionaryTypesPass::class, $arguments[5][0]);
    }
}
