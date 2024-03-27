<?php

declare(strict_types=1);

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

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('cyberspectrum_i18n.metamodels.attribute_handler_factories')) {
            return;
        }

        $factoryList = $container->getDefinition('cyberspectrum_i18n.metamodels.attribute_handler_factories');
        $factories   = $factoryList->getArgument(0);
        assert(\is_array($factories));

        /**
         * @var string $serviceId
         * @var list<array{type?: string}> $tags
         */
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
