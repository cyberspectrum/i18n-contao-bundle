<?php

declare(strict_types=1);

use CyberSpectrum\I18N\Contao\Extractor\ArrayExtractor;
use CyberSpectrum\I18N\Contao\Extractor\JsonSerializingCompoundExtractor;
use CyberSpectrum\I18N\Contao\Extractor\TextExtractor;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Table tl_content

    //// tl_content.rsce_data
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data', JsonSerializingCompoundExtractor::class)
        ->arg('$colName', 'rsce_data')
        ->arg(
            '$subExtractors',
            [
                service('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.link_label'),
                service('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes'),
                service('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.text'),
            ]
        )
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_content'])
        ->tag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            [
                'type' => 'expression',
                'expression' => 'row.type in [\'rsce_tao_feature_box\', \'rsce_field_boxes\']'
            ]
        );
    ////// tl_content.rsce_data.link_label
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.link_label', TextExtractor::class)
        ->arg('$colName', 'linkLabel');

    ////// tl_content.rsce_data.
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.text', TextExtractor::class)
        ->arg('$colName', 'text');

    ////// tl_content.rsce_data.boxes
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes', ArrayExtractor::class)
        ->arg('$colName', 'boxes')
        ->arg(
            '$subExtractors',
            [
                service('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes.headline'),
                service('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes.text'),
                service('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes.link_label'),
            ]
        );

    ////// tl_content.rsce_data.headline
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes.headline', TextExtractor::class)
        ->arg('$colName', 'headline');

    ////// tl_content.rsce_data.
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes.text', TextExtractor::class)
        ->arg('$colName', 'text');

    ////// tl_content.rsce_data.
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.rsce_data.boxes.link_label', TextExtractor::class)
        ->arg('$colName', 'linkLabel');
};
