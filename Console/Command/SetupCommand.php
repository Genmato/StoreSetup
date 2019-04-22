<?php

namespace Genmato\StoreSetup\Console\Command;

use Genmato\StoreSetup\Model\SetupList;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    /**
     * @var DataFactory
     */
    private $configData;
    /**
     * @var SetupList
     */
    private $setupList;
    /**
     * @var State
     */
    private $state;

    /**
     * SetupCommand constructor.
     * @param SetupList $setupList
     * @param State $state
     * @param null $name
     */
    public function __construct(
        SetupList $setupList,
        State $state,
        $name = null
    ) {
        parent::__construct($name);
        $this->setupList = $setupList;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('store:setup:process')
            ->setDescription('Run Store Setup configuration')
            ->addOption('process', 'p', InputOption::VALUE_OPTIONAL, 'Process to run');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $output->writeln("<info>Starting Store Setup</info>");

        $this->setupList->executeSetup(true, $input->getOption('process'));

        $output->writeln("<info>Done!</info>");
    }
}
