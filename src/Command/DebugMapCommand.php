<?php

declare(strict_types=1);

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
    /** The map builder. */
    private MapBuilderInterface $mapBuilder;

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

    #[\Override]
    protected function configure(): void
    {
        parent::configure();

        $this->setName('debug:i18n-map');
        $this->setDescription('Dump the mapping');
        $this->addArgument('table', InputArgument::REQUIRED, 'The table name');
        $this->addArgument('source-language', InputArgument::REQUIRED, 'The source language');
        $this->addArgument('target-language', InputArgument::REQUIRED, 'The target language');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tableName = $input->getArgument('table');
        assert(is_string($tableName));
        $sourceLanguage = $input->getArgument('source-language');
        assert(is_string($sourceLanguage));
        $targetLanguage = $input->getArgument('target-language');
        assert(is_string($targetLanguage));

        $tableMap = $this->mapBuilder->getMappingFor($tableName, $sourceLanguage, $targetLanguage);
        $keys     = $tableMap->sourceIds();
        $table    = new Table($output);
        $table->addRow(['source id', 'main id', 'target id']);
        foreach ($keys as $sourceId) {
            $table->addRow([$sourceId, $tableMap->getMainFromSource($sourceId), $tableMap->getTargetIdFor($sourceId)]);
        }

        $table->render();

        return 0;
    }
}
