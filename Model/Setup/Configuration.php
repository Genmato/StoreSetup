<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Configuration as ConfigurationConfig;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\FlagManager;
use Magento\Store\Model\StoreManagerInterface;

class Configuration extends AbstractAction
{
    protected $identifier = 'configuration';

    protected $websites;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    protected $stores;
    /**
     * @var Config
     */
    protected $resourceConfig;
    /**
     * @var ConfigurationConfig
     */
    protected $configurationConfig;
    /**
     * @var string
     */
    protected $envName = false;

    /**
     * Configuration constructor.
     * @param FlagManager $flagManager
     * @param Config $resourceConfig
     * @param StoreManagerInterface $stores
     * @param ConfigurationConfig $configurationConfig
     * @param array $envNames
     */
    public function __construct(
        FlagManager $flagManager,
        Config $resourceConfig,
        StoreManagerInterface $stores,
        ConfigurationConfig $configurationConfig,
        array $envNames = []
    ) {
        parent::__construct($flagManager);
        $this->stores = $stores->getStores(true, true);
        $this->websites = $stores->getWebsites(true, true);
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $stores;
        $this->configurationConfig = $configurationConfig;

        foreach ($envNames as $name) {
            if (getenv($name)) {
                $this->envName = (string)getenv($name);
            }
        }
    }

    /**
     * @return array|void
     */
    public function setupData()
    {
        $configuration = $this->configurationConfig->get();
        if (!is_array($configuration)) {
            $configuration = [];
        }

        if (!$this->forceAction && $this->checkChecksum($configuration)) {
            return;
        }
        $this->saveChecksum($configuration);

        $this->log->logInfo('Starting configuration setup:');
        foreach ($configuration as $config) {
            $path = $config['path'];
            $scopeId = $config['scope_id'];
            $scope = $config['scope'];

            switch ($scope) {
                case 'stores':
                    if (!is_numeric($scopeId)) {
                        if (isset($this->stores[$scopeId])) {
                            $scopeId = $this->stores[$scopeId]->getId();
                        }
                    }
                    break;
                case 'websites':
                    if (!is_numeric($scopeId)) {
                        if (isset($this->websites[$scopeId])) {
                            $scopeId = $this->websites[$scopeId]->getId();
                        }
                    }
                    break;
                default:
                    $scopeId = 0;
                    $scope = 'default';
            }
            // Get environment based value
            $value = $config['value'];
            if ($this->envName && isset($config['environment'][$this->envName])) {
                $value = $config['environment'][$this->envName];
            }
            try {
                $this->resourceConfig->saveConfig(
                    $path,
                    $value,
                    $scope,
                    $scopeId
                );
                $this->log->logLine(sprintf(" - Saving configuration: %s with value: %s for scope: %s (%s)", $path, $value, $scope, $scopeId));
            } catch (\Exception $ex) {
                $this->log->logError(sprintf("Error saving configuration:%s:%s", $config['path'], $ex->getMessage()));
            }
        }
    }
}
