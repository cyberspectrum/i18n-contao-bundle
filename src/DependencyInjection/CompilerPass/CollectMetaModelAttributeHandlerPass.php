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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This pass adds the tagged handler factories to the MetaModels handler factory.
 */
class CollectMetaModelAttributeHandlerPass implements CompilerPassInterface
{
    /**
     * The tag name to use for attribute handler factories.
     */
    public const TAG_ATTRIBUTE_HANDLER_FACTORY = 'cyberspectrum_i18n.metamodels.attribute_handler';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cyberspectrum_i18n.metamodels.attribute_handler_factories')) {
            return;
        }

        $factoryList = $container->getDefinition('cyberspectrum_i18n.metamodels.attribute_handler_factories');
        $factories   = $factoryList->getArgument(0);

        foreach ($container->findTaggedServiceIds(self::TAG_ATTRIBUTE_HANDLER_FACTORY) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if ($type = ($tag['type'] ?? null)) {
                    $factories[$type] = new Reference($serviceId);
                }
            }
        }
        $factoryList->setArgument(0, $factories);
    }
}
