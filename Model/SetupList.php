<?php

namespace Genmato\StoreSetup\Model;

use Genmato\StoreSetup\Model\Config\Data as ConfigData;
use Genmato\StoreSetup\Model\Setup\ActionInterface;

class SetupList
{
    /**
     * @var string[]
     */
    protected $actions;

    /**
     * Constructor
     *
     * @param array $actions
     */
    public function __construct(
        array $actions = []
    ) {
        $this->actions = $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param bool $forceAction
     * @param bool|string $process
     * @return void
     */
    public function executeSetup($forceAction = false, $process = false)
    {
        /** ActionInterface $action */
        foreach ($this->actions as $name => $action) {
            if ($process && strtolower($name) !== strtolower($process)) {
                continue;
            }
            if (!$forceAction) {
                $action->runOnChanges();
            }
            $action->setupData();
        }
    }
}
