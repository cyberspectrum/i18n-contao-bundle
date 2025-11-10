<?php

declare(strict_types=1);

use CyberSpectrum\I18N\Contao\Extractor\SerializingCompoundExtractor;
use CyberSpectrum\I18N\Contao\Extractor\TextExtractor;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectContaoExtractorsPass;
use CyberSpectrum\I18N\ContaoBundle\DependencyInjection\CompilerPass\CollectExtractorConditionsPass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // Table tl_page

    //// tl_page.title
    $services->set('cyberspectrum_i18n.contao.extractor.tl_page.title', TextExtractor::class)
        ->arg('$colName', 'title')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_page']);

    //// tl_page.
    $services->set('cyberspectrum_i18n.contao.extractor.tl_page.alias', TextExtractor::class)
        ->arg('$colName', 'alias')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_page']);

    //// tl_page.
    $services->set('cyberspectrum_i18n.contao.extractor.tl_page.page_title', TextExtractor::class)
        ->arg('$colName', 'pageTitle')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_page']);

    
    //// tl_page.description
    $services->set('cyberspectrum_i18n.contao.extractor.tl_page.description', TextExtractor::class)
        ->arg('$colName', 'description')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_page']);
    
    // Table tl_article

    //// tl_article.title
    $services->set('cyberspectrum_i18n.contao.extractor.tl_article.title', TextExtractor::class)
        ->arg('$colName', 'title')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_article']);

    //// tl_article.alias
    $services->set('cyberspectrum_i18n.contao.extractor.tl_article.alias', TextExtractor::class)
        ->arg('$colName', 'alias')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_article']);

    // Table tl_content

    //// tl_content.headline
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.headline', SerializingCompoundExtractor::class)
        ->arg('$colName', 'headline')
        ->arg('$subExtractors', [service('cyberspectrum_i18n.contao.extractor.tl_content.headline.value')])
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_content'])
        ->tag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'row.type in [\'headline\', \'text\']']
        );

    //// tl_content.headline.value
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.headline.value', TextExtractor::class)
        ->arg('$colName', 'value');

    //// tl_content.html
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.html', TextExtractor::class)
        ->arg('$colName', 'html')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_content'])
        ->tag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'row.type in [\'html\']']
        );

    //// tl_content.tableitems
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.tableitems', TextExtractor::class)
        ->arg('$colName', 'tableitems')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_content'])
        ->tag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'row.type in [\'table\']']
        );

    //// tl_content.text
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.text', TextExtractor::class)
        ->arg('$colName', 'text')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_content'])
        ->tag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'row.type in [\'text\']']
        );

    //// tl_content.mooHeadline
    $services->set('cyberspectrum_i18n.contao.extractor.tl_content.moo_headline', TextExtractor::class)
        ->arg('$colName', 'mooHeadline')
        ->tag(CollectContaoExtractorsPass::TAG_CONTAO_EXTRACTOR, ['table' => 'tl_content'])
        ->tag(
            CollectExtractorConditionsPass::TAG_CONTAO_EXTRACTOR_CONDITION,
            ['type' => 'expression', 'expression' => 'row.type in [\'accordionStart\', \'accordionSingle\']']
        );
};
