<?php

declare(strict_types=1);

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

use function func_get_args;

/** @covers \CyberSpectrum\I18N\ContaoBundle\CyberSpectrumI18NContaoBundle */
class CyberSpectrumI18NContaoBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->onlyMethods(['addCompilerPass'])
            ->getMock();

        $arguments = [];
        $container
            ->expects($this->exactly(6))
            ->method('addCompilerPass')
            ->willReturnCallback(function () use (&$arguments, $container) {
                $arguments[] = func_get_args();
                return $container;
            });

        $bundle = new CyberSpectrumI18NContaoBundle();

        $bundle->build($container);

        self::assertInstanceOf(CollectContaoExtractorsPass::class, $arguments[0][0]);
        self::assertInstanceOf(CollectExtractorConditionsPass::class, $arguments[1][0]);
        self::assertInstanceOf(CollectMetaModelAttributeHandlerPass::class, $arguments[2][0]);
        self::assertInstanceOf(SetDefaultsPass::class, $arguments[3][0]);
        self::assertInstanceOf(UpdateForRockSolidCustomElementPass::class, $arguments[4][0]);
        self::assertSame(PassConfig::TYPE_BEFORE_OPTIMIZATION, $arguments[4][1]);
        self::assertSame(10, $arguments[4][2]);
        self::assertInstanceOf(RegisterDictionaryTypesPass::class, $arguments[5][0]);
    }
}
