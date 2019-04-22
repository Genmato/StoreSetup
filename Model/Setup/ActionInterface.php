<?php
namespace Genmato\StoreSetup\Model\Setup;

interface ActionInterface
{
    /**
     * @return string
     */
    public function getIdentifier();


    /**
     * Run setup steps for identifier
     * @return mixed
     */
    public function setupData();
}
