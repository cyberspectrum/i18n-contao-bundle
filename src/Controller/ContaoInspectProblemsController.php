<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\ContaoBundle\Controller;

use CyberSpectrum\I18N\Contao\Mapping\MapBuilderInterface;
use CyberSpectrum\I18N\Contao\Mapping\Terminal42ChangeLanguage\ArticleMap;
use CyberSpectrum\I18N\ContaoBundle\FormType\InspectMappingFormType;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Templating\EngineInterface;

use function in_array;
use function strtr;

/**
 * This allows to inspect dictionary problems in the Contao backend.
 */
class ContaoInspectProblemsController extends AbstractController
{
    /** The twig engine. */
    private EngineInterface $templating;

    /** The Contao map builder. */
    private MapBuilderInterface $mapBuilder;

    /** The CSRF token manager. */
    private CsrfTokenManager $csrfTokenManager;

    /** The CSRF token name. */
    private string $csrfTokenName;

    /**
     * Create a new instance.
     *
     * @param EngineInterface     $templating       The twig engine.
     * @param MapBuilderInterface $mapBuilder       The database.
     * @param CsrfTokenManager    $csrfTokenManager The CSRF token manager to use.
     * @param string              $csrfTokenName    The CSRF token name to use.
     */
    public function __construct(
        EngineInterface $templating,
        MapBuilderInterface $mapBuilder,
        CsrfTokenManager $csrfTokenManager,
        string $csrfTokenName
    ) {
        $this->templating       = $templating;
        $this->mapBuilder       = $mapBuilder;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->csrfTokenName    = $csrfTokenName;
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
        switch ($request->query->get('act')) {
            case 'inspect-map':
                return $this->inspectMap($request);
            default:
        }

        return new Response($this->templating->render('CyberSpectrumI18NContaoBundle::contao-backend/main.html.twig'));
    }

    /**
     * Inspect Terminal42 mappings.
     *
     * @param Request $request The request.
     */
    private function inspectMap(Request $request): Response
    {
        if (empty($map = $request->query->get('map'))) {
            $map = 'tl_page';
        }
        assert(is_string($map));

        $form = $this->createForm(InspectMappingFormType::class);
        $form->handleRequest($request);

        /** @var array{source: string, target: string} $data */
        $data   = $form->getData();
        $logger = new BufferingLogger();
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->mapBuilder instanceof LoggerAwareInterface) {
                $this->mapBuilder->setLogger($logger);
            }
            $this->mapBuilder->getMappingFor($map, $data['source'], $data['target']);
            /** @var list<array{0: string, 1: string, 2: array<string, string>}> $logs */
            $logs = $logger->cleanLogs();
            foreach ($logs as $message) {
                if ([] !== ($converted = $this->convertMessage($message))) {
                    $errors[] = $converted;
                }
            }
        }

        return $this->render(
            'CyberSpectrumI18NContaoBundle::contao-backend/terminal42-map-problems.html.twig',
            [
                'form'   => $form->createView(),
                'errors' => $errors,
            ]
        );
    }

    /**
     * Convert an error message to the correct error array.
     *
     * @param array{0: string, 1: string, 2: array<string, string>} $message The error message to convert.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function convertMessage(array $message): array
    {
        [$level, $text, $context] = $message;
        if (!in_array($level, ['warning', 'error'])) {
            return [];
        }

        switch (($context['msg_type'] ?? '')) {
            case 'no_source_for_target':
                $hrefMain   = null;
                $hrefSource = null;
                $hrefTarget = null;
                $origin     = null;
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
                        break;
                    default:
                        break 2;
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
        }
        return [
            'type'    => ($context['msg_type'] ?? '?'),
            'level'   => $level,
            'message' => $text,
            'context' => $context,
            'processed' => $this->transformMessage($text, $context)
        ];
    }

    /**
     * Merge the request token into the link parameters.
     *
     * @param array $params The parameters.
     *
     * @return array
     */
    private function link(array $params): array
    {
        return ($params + ['rt' => $this->csrfTokenManager->getToken($this->csrfTokenName)->getValue()]);
    }

    /**
     * Transform the message by replacing the context parameters.
     *
     * @param string                $message The message template.
     * @param array<string, string> $context The context parameters.
     */
    private function transformMessage(string $message, array $context): string
    {
        $params = [];
        foreach ($context as $key => $value) {
            $params['{' . $key . '}'] = $value;
        }

        return strtr($message, $params);
    }
}
