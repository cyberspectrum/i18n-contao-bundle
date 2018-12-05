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

/**
 * This pass updates the extractors for usage with rocksolid custom elements.
 */
class UpdateForRockSolidCustomElementPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $extractor = $container->getDefinition('cyberspectrum_i18n.contao.extractor.tl_content.headline');
        $extractor->addTag(CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION, [
            'type'       => 'expression',
            'expression' => 'row.type in [\'rsce_tao_feature_box\', \'rsce_tao_boxes\']'
        ]);
    }
}
