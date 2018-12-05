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

use CyberSpectrum\I18N\Contao\Extractor\StringExtractorInterface;
use CyberSpectrum\I18N\Contao\ExtractorFactory;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * This tests the service collector pass.
 *
 * @covers \CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass
 */
class CollectContaoExtractorsPassTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
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

        $this->assertSame(
            ['tl_something' => ['service']],
            $container->getParameter('cyberspectrum_i18n.contao.extractors')
        );
        $this->assertSame(['service'], \array_keys($extractorList));
        $this->assertInstanceOf(Reference::class, $extractorList['service']);
        $this->assertSame('service', (string) $extractorList['service']);
    }

    /**
     * Test.
     *
     * @return void
     */
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

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tagged service "service" has no table name.');

        $servicePass = new CollectContaoExtractorsPass();
        $servicePass->process($container);
    }
}
