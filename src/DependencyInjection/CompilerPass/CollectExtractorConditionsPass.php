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

use CyberSpectrum\I18N\Contao\Extractor\Condition\ExpressionLanguageCondition;
use CyberSpectrum\I18N\Contao\Extractor\Condition\WhitelistCondition;
use CyberSpectrum\I18N\Contao\Extractor\ConditionalExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\ConditionalMultiStringExtractor;
use CyberSpectrum\I18N\Contao\Extractor\ConditionalStringExtractor;
use CyberSpectrum\I18N\Contao\Extractor\MultiStringExtractorInterface;
use CyberSpectrum\I18N\Contao\Extractor\StringExtractorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This pass adds tagged services to the various factories.
 */
class CollectExtractorConditionsPass implements CompilerPassInterface
{
    /**
     * The tag name to use for attribute factories.
     */
    public const TAG_CONTAO_EXTRACTOR_CONDITION = 'cyberspectrum_i18n.contao_condition';

    /**
     * Id of the expression language to use.
     *
     * @var string
     */
    private const EXPRESSION_LANGUAGE = 'cyberspectrum_i18n.expression_language';

    /**
     * Collect all tagged contao extractors.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return void
     *
     * @throws \RuntimeException When a tag has no table name.
     */
    public function process(ContainerBuilder $container): void
    {
        if ([] === $services = $container->findTaggedServiceIds(self::TAG_CONTAO_EXTRACTOR_CONDITION)) {
            return;
        }

        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $this->handleTag($serviceId, $tag, $container);
            }
        }
    }

    /**
     * Handle a tag.
     *
     * @param string           $serviceId The service id the tag belongs to.
     * @param array            $tag       The tag content.
     * @param ContainerBuilder $container The container builder.
     *
     * @return void
     *
     * @throws \RuntimeException On error.
     */
    private function handleTag(string $serviceId, array $tag, ContainerBuilder $container): void
    {
        $definition = $this->ensureConditionalService($serviceId, $container);
        if (!$definition->hasMethodCall('setCondition')) {
            $definition->addMethodCall('setCondition', [$this->createCondition($serviceId, $tag, $container)]);
            return;
        }

        $conditions = [];
        foreach ($definition->getMethodCalls() as $call) {
            if ('setCondition' === $call[0]) {
                if (!$call[1][0] instanceof Reference) {
                    throw new \RuntimeException('Expected a reference as argument');
                }
                $conditions[] = $call[1][0];
            }
        }
        $definition->removeMethodCall('setCondition');
        unset($call);

        $newCondition = $this->createCondition($serviceId, $tag, $container);

        // Scan if any of the conditions is a WhitelistCondition - if so, add all other to that one.
        foreach ($conditions as $index => $condition) {
            /** @var Reference $condition */
            $referenced = $container->getDefinition($condition);
            if ($referenced->getClass() === WhitelistCondition::class) {
                $arguments = $referenced->getArgument(0);

                $referenced->setArgument(
                    0,
                    array_merge(
                        \array_slice($arguments, 0, $index),
                        \array_slice($arguments, ($index + 1)),
                        [$newCondition]
                    )
                );
                return;
            }
        }
        unset($referenced, $condition);

        // Add a new whitelist for all conditions.
        $container->setDefinition(
            $conditionId = $serviceId . '.condition.whitelist',
            new Definition(
                WhitelistCondition::class,
                array_merge($conditions, [$newCondition])
            )
        );

        $definition->addMethodCall('setCondition', [new Reference($conditionId)]);
    }


    /**
     * Ensure that the service implements a conditional interface - if not, it will get decorated.
     *
     * @param string           $serviceId The service id.
     * @param ContainerBuilder $container The container.
     *
     * @return Definition
     *
     * @throws \RuntimeException On error.
     */
    private function ensureConditionalService(string $serviceId, ContainerBuilder $container): Definition
    {
        $definition = $container->getDefinition($serviceId);
        if ($this->classImplements($class = $definition->getClass(), ConditionalExtractorInterface::class)) {
            return $definition;
        }

        // Already decorated?
        if ($container->has($decoratedName = $serviceId . '.conditional')) {
            return $container->getDefinition($decoratedName);
        }

        // Try to decorate then.
        switch (true) {
            case $this->classImplements($class, StringExtractorInterface::class):
                return $container
                    ->register(
                        $decoratedName,
                        ConditionalStringExtractor::class
                    )
                    ->setDecoratedService($serviceId)
                    ->addArgument(new Reference($decoratedName . '.inner'));
            case $this->classImplements($class, MultiStringExtractorInterface::class):
                return $container
                    ->register(
                        $decoratedName,
                        ConditionalMultiStringExtractor::class
                    )
                    ->setDecoratedService($serviceId)
                    ->addArgument(new Reference($decoratedName . '.inner'));
            default:
        }

        throw new \RuntimeException(sprintf(
            'Class %1$s does not implement %2$s - can not apply conditions (service: %3$s).',
            $definition->getClass(),
            ConditionalExtractorInterface::class,
            $serviceId
        ));
    }

    /**
     * Test if a class implements an interface.
     *
     * @param string $class     The class to test.
     * @param string $interface The interface to test.
     *
     * @return bool
     */
    private function classImplements(string $class, string $interface): bool
    {
        return \in_array($interface, class_implements($class), true);
    }

    /**
     * Create a condition from a tag.
     *
     * @param string           $serviceId The service to which the tag applies to.
     * @param array            $tag       The tag to create the condition from.
     * @param ContainerBuilder $container The container builder.
     *
     * @return Reference
     *
     * @throws \RuntimeException When the condition type is unknown.
     */
    private function createCondition(string $serviceId, array $tag, ContainerBuilder $container): Reference
    {
        if (!isset($tag['type'])) {
            throw new \RuntimeException(sprintf(
                'No type given for condition of service: %1$s.',
                $serviceId
            ));
        }

        switch ($tag['type']) {
            case 'expression':
                if (!isset($tag['expression'])) {
                    throw new \RuntimeException('Expression missing in tag for: ' . $serviceId);
                }
                $container->setDefinition(
                    $conditionId = 'cyberspectrum_i18n.contao.extractor.condition.' .
                        hash('md5', serialize($tag)),
                    new Definition(
                        ExpressionLanguageCondition::class,
                        [new Reference(self::EXPRESSION_LANGUAGE), $tag['expression']]
                    )
                );
                return new Reference($conditionId);

            default:
        }

        throw new \RuntimeException('Unknown condition tag: ' . json_encode($tag));
    }
}
