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

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use CyberSpectrum\I18N\Contao\ExtractorFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
     * @return void
     *
     * @throws \RuntimeException When a tag has no table name.
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ExtractorFactory::class)) {
            return;
        }
        if ([] === $services = $container->findTaggedServiceIds(self::TAG_CONTAO_EXTRACTOR)) {
            return;
        }

        $extractorLists     = [];
        $extractorContainer = $container->getDefinition('cyberspectrum_i18n.contao.extractor_container');
        $extractorList      = $extractorContainer->getArgument(0);
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!array_key_exists('table', $tag)) {
                    throw new \RuntimeException('Tagged service "'. $serviceId .'" has no table name.');
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
