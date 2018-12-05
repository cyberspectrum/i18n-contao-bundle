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

namespace CyberSpectrum\I18N\ContaoBundle\Controller;

use CyberSpectrum\I18N\Contao\Mapping\MapBuilderInterface;
use CyberSpectrum\I18N\Contao\Mapping\Terminal42ChangeLanguage\ArticleMap;
use CyberSpectrum\I18N\ContaoBundle\FormType\InspectMappingFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * This allows to inspect dictionary problems in the Contao backend.
 */
class ContaoInspectProblemsController extends AbstractController
{
    /**
     * The twig engine.
     *
     * @var EngineInterface
     */
    private $templating;

    /**
     * The Contao map builder.
     *
     * @var MapBuilderInterface
     */
    private $mapBuilder;

    /**
     * Create a new instance.
     *
     * @param EngineInterface     $templating The twig engine.
     * @param MapBuilderInterface $mapBuilder The database.
     */
    public function __construct(EngineInterface $templating, MapBuilderInterface $mapBuilder)
    {
        $this->templating = $templating;
        $this->mapBuilder = $mapBuilder;
    }

    /**
     * Invoke this.
     *
     * @param Request $request The request.
     *
     * @return Response The template data.
     */
    public function __invoke(Request $request): Response
    {
        switch ($request->get('act')) {
            case 'inspect-map':
                return $this->inspectMap($request);
            default:
        }

        return new Response($this->templating->render('CyberSpectrumTranslationBundle::contao-backend/main.html.twig'));
    }

    /**
     * Inspect Terminal42 mappings.
     *
     * @param Request $request The request.
     *
     * @return Response
     */
    private function inspectMap(Request $request): Response
    {
        if (empty($map = $request->get('map'))) {
            $map = 'tl_page';
        }

        $data = [
            'source' => $request->get('map'),
            'target' => $request->get('map'),
        ];

        $form = $this->createForm(InspectMappingFormType::class, $data);

        $logger = new BufferingLogger();
        $this->mapBuilder->setLogger($logger);

        $this->mapBuilder->getMappingFor($map, 'en', 'tr');

        $errors = [];
        foreach ($logger->cleanLogs() as $message) {
            if ([] !== ($converted = $this->convertMessage($message))) {
                $errors[] = $converted;
            }
        }

        return $this->render(
            'CyberSpectrumTranslationBundle::contao-backend/terminal42-map-problems.html.twig',
            [
                'form'   => $form->createView(),
                'errors' => $errors,
            ]
        );
    }

    /**
     * Convert an error message to the correct error array.
     *
     * @param array $message The error message to convert.
     *
     * @return array
     */
    private function convertMessage(array $message): array
    {
        [$level, $text, $context] = $message;
        if (!\in_array($level, ['warning', 'error'])) {
            return [];
        }

        switch ($context['msg_type'] ?? '') {
            case 'no_source_for_target':
                $hrefMain = null;
                $hrefSource = null;
                $hrefTarget = null;
                $origin = null;
                switch ($context['class']) {
                    case ArticleMap::class:
                        $origin = 'article';
                        if ($context['mainId']) {
                            $hrefMain = $this->link([
                                'do' => 'article',
                                'table' => 'tl_content',
                                'id'    => $context['mainId'],
                            ]);
                        }
                        if ($context['targetId']) {
                            $hrefSource = $this->link([
                                'do' => 'article',
                                'table' => 'tl_content',
                                'id'    => $context['targetId'],
                            ]);
                        }
                        if ($context['sourceId']) {
                            $hrefTarget = $this->link([
                                'do' => 'article',
                                'table' => 'tl_content',
                                'id'    => $context['sourceId'],
                            ]);
                        }
                }

                return [
                    'type'        => $level,
                    'level'       => $level,
                    'message'     => $text,
                    'context'     => $context,
                    'href_main'   => $hrefMain,
                    'href_source' => $hrefSource,
                    'href_target' => $hrefTarget,
                    'processed'   => $this->transformMessage($text, $context),
                    'origin'      => $origin,
                ];

            case 'page_no_articles':
                return [
                    'type'         => $level,
                    'level'        => $level,
                    'message'      => $text,
                    'context'      => $context,
                    'href_element' => $this->link([
                        'do'       => 'article',
                        'pn'       => $context['id'],
                    ]),
                    'processed'    => $this->transformMessage($text, $context),
                    'origin'       => 'article',
                ];
            case 'article_no_mapping_in_main':
                return [
                    'type'    => $level,
                    'level'   => $level,
                    'message' => $text,
                    'context' => $context,
                    'href'    => $this->link([
                        'do'    => 'article',
                        'table' => 'tl_content',
                        'id'    => $context['mainId'],
                    ]),
                    'processed' => $this->transformMessage($text, $context),
                    'origin'       => 'article',
                ];
            case 'article_content_type_mismatch':
                return [
                    'type'    => $level,
                    'level'   => $level,
                    'message' => $text,
                    'context' => $context,
                    'href_main'    => $this->link([
                        'do'    => 'article',
                        'table' => 'tl_content',
                        'id'    => $context['mainArticle'],
                    ]),
                    'href_source' => $this->link([
                        'do'    => 'article',
                        'table' => 'tl_content',
                        'id'    => $context['srcArticle'],
                    ]),
                    'href_target' => $this->link([
                        'do'    => 'article',
                        'table' => 'tl_content',
                        'id'    => $context['tgtArticle'],
                    ]),
                    'processed' => $this->transformMessage($text, $context),
                    'origin'       => 'article',
                ];

            default:
                return [
                    'type'    => $context['msg_type'] ?? '?',
                    'level'   => $level,
                    'message' => $text,
                    'context' => $context,
                    'processed' => $this->transformMessage($text, $context)
                ];
        }
    }

    private function link(array $params): array
    {
        // FIXME: fetch request token via service.
        return $params + ['rt' => REQUEST_TOKEN];
    }

    private function transformMessage(string $message, array $context): string
    {
        $params = [];
        foreach ($context as $key => $value) {
            $params['{' . $key . '}'] = $value;
        }

        return strtr($message, $params);
    }

    private function showContents()
    {

    }
}
