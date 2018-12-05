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

namespace CyberSpectrum\I18N\ContaoBundle\Test\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\Extractor\ConditionalExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\ExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\MultiStringExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\StringExtractorInterface;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This tests the service collector pass.
 *
 * @covers \CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass
 */
class CollectExtractorConditionsPassTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testCollectStringExtractorInterface(): void
    {
        $container = new ContainerBuilder();

        $mock = $this->getMockForAbstractClass(StringExtractorInterface::class);

        $tagged = new Definition(get_class($mock));
        $tagged->addTag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'true']
        );
        $container->addDefinitions(['service' => $tagged]);
        unset($tagged);

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);

        $this->assertTrue($container->has('service.conditional'));
        $service = $container->getDefinition('service.conditional');
        $this->assertSame(['service', null, 0], $service->getDecoratedService());
        $this->assertInstanceOf(Reference::class, $service->getArgument(0));
        $this->assertSame('service.conditional.inner', (string) $service->getArgument(0));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCollectMultiStringExtractorInterface(): void
    {
        $container = new ContainerBuilder();

        $mock = $this->getMockForAbstractClass(MultiStringExtractorInterface::class);

        $tagged = new Definition(get_class($mock));
        $tagged->addTag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'true']
        );
        $container->addDefinitions(['service' => $tagged]);
        unset($tagged);

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);

        $this->assertTrue($container->has('service.conditional'));
        $service = $container->getDefinition('service.conditional');
        $this->assertSame(['service', null, 0], $service->getDecoratedService());
        $this->assertInstanceOf(Reference::class, $service->getArgument(0));
        $this->assertSame('service.conditional.inner', (string) $service->getArgument(0));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCollectUnknownExtractorInterfaceThrows(): void
    {
        $container = new ContainerBuilder();

        $mock = $this->getMockForAbstractClass(ExtractorInterface::class);

        $tagged = new Definition($class = get_class($mock));
        $tagged->addTag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'true']
        );
        $container->addDefinitions(['service' => $tagged]);
        unset($tagged);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Class ' . $class . ' does not implement '
            . ConditionalExtractorInterface::class . ' - can not apply conditions (service: service).');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNotDecorateTwice(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(MultiStringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'true']
        );
        $container->addDefinitions([
            'service' => $tagged,
            'service.conditional' => $definition = new Definition(),
        ]);
        unset($tagged);

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);

        $this->assertSame($definition, $container->getDefinition('service.conditional'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNotDecorateConditionalService(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(ConditionalExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'true']
        );
        $container->addDefinitions(['service' => $tagged]);

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);

        $this->assertFalse($container->hasDefinition('service.conditional'));
        $this->assertSame($tagged, $container->getDefinition('service'));
        $this->assertCount(1, $calls = $container->getDefinition('service')->getMethodCalls());
        $this->assertSame('setCondition', $calls[0][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCollectExtractorsThrowsForTagWithoutConditionType(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(StringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION);
        $container->addDefinitions(['service' => $tagged]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No type given for condition of service: service.');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCollectExtractorsThrowsForTagWithoutExpression(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(StringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION, ['type' => 'expression']);
        $container->addDefinitions(['service' => $tagged]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expression missing in tag for: service');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCollectExtractorsThrowsForTagWithUnknownType(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(StringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION, ['type' => '-unknown-']);
        $container->addDefinitions(['service' => $tagged]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown condition tag: {"type":"-unknown-"}');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }
}
