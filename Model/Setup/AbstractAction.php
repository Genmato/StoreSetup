<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Setup\ConsoleLogger;

use Magento\Framework\FlagManager;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class AbstractAction implements ActionInterface
{
    protected $identifier;
    protected $forceAction = true;

    /**
     * @var ConsoleLogger
     */
    protected $log;
    /**
     * @var FlagManager
     */
    protected $flagManager;

    public function __construct(FlagManager $flagManager)
    {
        $this->log = new ConsoleLogger(new ConsoleOutput());
        $this->flagManager = $flagManager;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return $this
     */
    public function init()
    {
        return $this;
    }

    public function runOnChanges()
    {
        $this->forceAction = false;
        return $this;
    }

    /**
     * Check if current (stored) checksum is the same as new checksum
     * @param array $data
     * @return bool
     */
    public function checkChecksum($data)
    {
        $currentChecksum = $this->flagManager->getFlagData($this->getFlagName());

        if ($this->getChecksum($data) !== $currentChecksum) {
            return false;
        }
        return true;
    }

    /**
     * @param $data
     */
    public function saveChecksum($data)
    {
        $this->flagManager->saveFlag($this->getFlagName(), $this->getChecksum($data));
    }

    /**
     * @param array $data
     * @return string
     */
    protected function getChecksum($data)
    {
        return sha1(json_encode($data));
    }

    /**
     * @return string
     */
    protected function getFlagName()
    {
        return 'genmato_storesetup_' . $this->getIdentifier();
    }
}
