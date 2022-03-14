<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\ExtractorFactory;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function array_key_exists;

/**
 * This pass adds tagged services to the various factories.
 */
class CollectContaoExtractorsPass implements CompilerPassInterface
{
    /**
     * The tag name to use for attribute factories.
     */
    public const TAG_CONTAO_EXTRACTOR = 'cyberspectrum_i18n.contao_extractor';

    /**
     * Collect all tagged contao extractors.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @throws RuntimeException When a tag has no table name.
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ExtractorFactory::class)) {
            return;
        }
        if ([] === $services = $container->findTaggedServiceIds(self::TAG_CONTAO_EXTRACTOR)) {
            return;
        }

        /** @var array<string, list<string>> $extractorLists */
        $extractorLists     = [];
        $extractorContainer = $container->getDefinition('cyberspectrum_i18n.contao.extractor_container');
        /** @var array<string, Reference> $extractorList */
        $extractorList = $extractorContainer->getArgument(0);
        /**
         * @var string $serviceId
         * @var list<array{table?: string}> $tags
         */
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!array_key_exists('table', $tag)) {
                    throw new RuntimeException('Tagged service "' . $serviceId . '" has no table name.');
                }

                $tableName = $tag['table'];
                if (!array_key_exists($tableName, $extractorLists)) {
                    $extractorLists[$tableName] = [];
                }

                $extractorLists[$tableName][] = $serviceId;
                $extractorList[$serviceId]    = new Reference($serviceId);
            }
        }

        $container->setParameter('cyberspectrum_i18n.contao.extractors', $extractorLists);
        $extractorContainer->setArgument(0, $extractorList);
    }
}
