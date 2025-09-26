<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This pass updates the extractors for usage with rocksolid custom elements.
 */
final class UpdateForRockSolidCustomElementPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $extractor = $container->getDefinition('cyberspectrum_i18n.contao.extractor.tl_content.headline');
        $extractor->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION, [
            'type'       => 'expression',
            'expression' => 'row.type in [\'rsce_tao_feature_box\', \'rsce_tao_boxes\']'
        ]);
    }
}
