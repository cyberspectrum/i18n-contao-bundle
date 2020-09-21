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

namespace CyberSpectrum\I18N\ContaoBundle;

use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectMetaModelAttributeHandlerPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\RegisterDictionaryTypesPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\SetDefaultsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\UpdateForRockSolidCustomElementPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * This provides the bundle entry point.
 */
class CyberSpectrumI18NContaoBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CollectContaoExtractorsPass());
        $container->addCompilerPass(new CollectExtractorConditionsPass());
        $container->addCompilerPass(new CollectMetaModelAttributeHandlerPass());
        // Priority lowered, so other extensions may set defaults before we add ours as last resort.
        $container->addCompilerPass(
            new SetDefaultsPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            -10
        );
        // Priority must be higher than that of the CollectExtractorConditionsPass as otherwise the extractors won't be
        // decorated with the conditions.
        $container->addCompilerPass(
            new UpdateForRockSolidCustomElementPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            10
        );
        $container->addCompilerPass(new RegisterDictionaryTypesPass());
    }
}
