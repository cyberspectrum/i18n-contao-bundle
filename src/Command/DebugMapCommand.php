<?php

/**
 * This file is part of cyberspectrum/i18n-contao-bundle.
 *
 * (c) 2019 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/i18n-contao-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2019 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/i18n-contao-bundle/blob/master/LICENSE MIT
 * @filesource
 */

declare(strict_types = 1);

namespace CyberSpectrum\I18N\ContaoBundle\Command;

use CyberSpectrum\I18N\Contao\Mapping\MapBuilderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class aids in debugging a map.
 */
class DebugMapCommand extends Command
{
    /**
     * The map builder.
     *
     * @var MapBuilderInterface
     */
    private $mapBuilder;

    /**
     * Create a new instance.
     *
     * @param MapBuilderInterface $mapBuilder The map builder.
     */
    public function __construct(MapBuilderInterface $mapBuilder)
    {
        parent::__construct();
        $this->mapBuilder = $mapBuilder;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('debug:i18n-map');
        $this->setDescription('Dump the mapping');
        $this->addArgument('table', InputArgument::OPTIONAL, 'The table name');
        $this->addArgument('source-language', InputArgument::OPTIONAL, 'The source language');
        $this->addArgument('target-language', InputArgument::OPTIONAL, 'The target language');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tableName      = $input->getArgument('table');
        $sourceLanguage = $input->getArgument('source-language');
        $targetLanguage = $input->getArgument('target-language');

        $tableMap = $this->mapBuilder->getMappingFor($tableName, $sourceLanguage, $targetLanguage);
        $keys     = $tableMap->sourceIds();
        $table    = new Table($output);
        $table->addRow(['source id', 'main id', 'target id']);
        foreach ($keys as $sourceId) {
            $table->addRow([$sourceId, $tableMap->getMainFromSource($sourceId), $tableMap->getTargetIdFor($sourceId)]);
        }

        $table->render();
    }
}
