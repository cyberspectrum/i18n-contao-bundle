<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\Test\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\Extractor\ConditionalExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\ExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\MultiStringExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\StringExtractorInterface;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function get_class;

/** @covers \CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass */
class CollectExtractorConditionsPassTest extends TestCase
{
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

        self::assertTrue($container->has('service.conditional'));
        $service = $container->getDefinition('service.conditional');
        self::assertSame(['service', null, 0], $service->getDecoratedService());
        self::assertInstanceOf(Reference::class, $service->getArgument(0));
        self::assertSame('service.conditional.inner', (string) $service->getArgument(0));
    }

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

        self::assertTrue($container->has('service.conditional'));
        $service = $container->getDefinition('service.conditional');
        self::assertSame(['service', null, 0], $service->getDecoratedService());
        self::assertInstanceOf(Reference::class, $service->getArgument(0));
        self::assertSame('service.conditional.inner', (string) $service->getArgument(0));
    }

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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class ' . $class . ' does not implement '
            . ConditionalExtractorInterface::class . ' - can not apply conditions (service: service).');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }

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

        self::assertSame($definition, $container->getDefinition('service.conditional'));
    }

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

        self::assertFalse($container->hasDefinition('service.conditional'));
        self::assertSame($tagged, $container->getDefinition('service'));
        self::assertCount(1, $calls = $container->getDefinition('service')->getMethodCalls());
        self::assertSame('setCondition', $calls[0][0]);
        self::assertInstanceOf(Reference::class, $calls[0][1][0]);
    }

    public function testCollectExtractorsThrowsForTagWithoutConditionType(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(StringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION);
        $container->addDefinitions(['service' => $tagged]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No type given for condition of service: service.');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }

    public function testCollectExtractorsThrowsForTagWithoutExpression(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(StringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION, ['type' => 'expression']);
        $container->addDefinitions(['service' => $tagged]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expression missing in tag for: service');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }

    public function testCollectExtractorsThrowsForTagWithUnknownType(): void
    {
        $container = new ContainerBuilder();
        $mock      = $this->getMockForAbstractClass(StringExtractorInterface::class);
        $tagged    = new Definition(get_class($mock));
        $tagged->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION, ['type' => '-unknown-']);
        $container->addDefinitions(['service' => $tagged]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown condition tag: {"type":"-unknown-"}');

        $servicePass = new CollectExtractorConditionsPass();
        $servicePass->process($container);
    }
}
