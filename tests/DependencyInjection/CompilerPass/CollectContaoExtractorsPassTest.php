<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\Test\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\Extractor\StringExtractorInterface;
use CyberSpectrum\I18N\Contao\ExtractorFactory;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

use function array_keys;

/** @covers \CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass */
class CollectContaoExtractorsPassTest extends TestCase
{
    public function testCollectExtractors(): void
    {
        $container = new ContainerBuilder();

        $extractorFactory = new Definition(ExtractorFactory::class);
        $extractorFactory->setArguments([
            '%cyberspectrum_i18n.contao.extractors%',
            new Reference('cyberspectrum_i18n.contao.extractor_container')
        ]);
        $extractorContainer = new Definition(ServiceLocator::class);
        $extractorContainer->setArguments([[]]);

        $tagged = new Definition(StringExtractorInterface::class);
        $tagged->addTag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_something']);
        $container->addDefinitions(
            [
                ExtractorFactory::class => $extractorFactory,
                'cyberspectrum_i18n.contao.extractor_container' => $extractorContainer,
                'service' => $tagged
            ]
        );
        unset($tagged);

        $servicePass = new CollectContaoExtractorsPass();
        $servicePass->process($container);

        $extractorList = $extractorContainer->getArgument(0);

        self::assertSame(
            ['tl_something' => ['service']],
            $container->getParameter('cyberspectrum_i18n.contao.extractors')
        );
        self::assertSame(['service'], array_keys($extractorList));
        self::assertInstanceOf(Reference::class, $extractorList['service']);
        self::assertSame('service', (string) $extractorList['service']);
    }

    public function testCollectExtractorsThrowsForServiceWithoutTableName(): void
    {
        $container = new ContainerBuilder();

        $dictionaryProvider = new Definition(ExtractorFactory::class);
        $dictionaryProvider->setArguments([[], new Reference('cyberspectrum_i18n.contao.extractor_container')]);
        $extractorContainer = new Definition(ServiceLocator::class);
        $extractorContainer->setArguments([[]]);

        $tagged = new Definition(StringExtractorInterface::class);
        $tagged->addTag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR);
        $container->addDefinitions(
            [
                ExtractorFactory::class => $dictionaryProvider,
                'cyberspectrum_i18n.contao.extractor_container' => $extractorContainer,
                'service' => $tagged
            ]
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tagged service "service" has no table name.');

        $servicePass = new CollectContaoExtractorsPass();
        $servicePass->process($container);
    }
}
