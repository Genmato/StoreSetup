<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Theme as ThemeConfig;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Theme\Model\Data\Design\ConfigFactory;

class Theme extends AbstractAction
{
    protected $identifier = 'themes';

    protected $websites;
    protected $stores;

    /**
     * @var Config
     */
    protected $resourceConfig;

    /**
     * @var DesignConfigRepositoryInterface
     */
    protected $designConfigRepository;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;
    /**
     * @var Genmato\StoreSetup\Model\Config\Theme
     */
    protected $themeConfig;

    /**
     * Configuration constructor.
     * @param FlagManager $flagManager
     * @param Config $resourceConfig
     * @param StoreManagerInterface $stores
     * @param DesignConfigRepositoryInterface $designConfigRepository
     * @param ConfigFactory $configFactory
     * @param ThemeProviderInterface $themeProvider
     * @param ThemeConfig $themeConfig
     */
    public function __construct(
        FlagManager $flagManager,
        Config $resourceConfig,
        StoreManagerInterface $stores,
        DesignConfigRepositoryInterface $designConfigRepository,
        ConfigFactory $configFactory,
        ThemeProviderInterface $themeProvider,
        ThemeConfig $themeConfig
    ) {
        parent::__construct($flagManager);
        $this->stores = $stores->getStores(true, true);
        $this->websites = $stores->getWebsites(true, true);
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $stores;
        $this->designConfigRepository = $designConfigRepository;
        $this->themeProvider = $themeProvider;
        $this->configFactory = $configFactory;
        $this->themeConfig = $themeConfig;
    }

    /**
     * @return AbstractAction|void
     */
    public function init()
    {
        $this->stores = $this->storeManager->getStores(true, true);
        $this->websites = $this->storeManager->getWebsites(true, true);

        parent::init();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function setupData()
    {
        $this->init();

        $themes = $this->themeConfig->get();
        if (!is_array($themes)) {
            $themes = [];
        }

        if (!$this->forceAction && $this->checkChecksum($themes)) {
            return;
        }
        $this->saveChecksum($themes);

        $this->log->logInfo('Starting theme setup:');
        foreach ($themes as $theme) {
            $themeName = $theme['name'];
            $themeId = $this->themeProvider->getThemeByFullPath($themeName)->getId();
            if ($themeId) {
                if (isset($theme['stores'])) {
                    foreach ($theme['stores'] as $store) {
                        if (isset($this->stores[$store])) {
                            $storeId = $this->stores[$store]->getId();
                            $this->assignTheme($themeId, 'stores', $storeId);
                            $this->log->logLine(" - Assigned theme: {$themeName} to store: $store!");
                        } else {
                            $this->log->logException(" - Invalid store: {$store} for theme: {$themeName}!");
                        }
                    }
                }
                if (isset($theme['websites'])) {
                    foreach ($theme['websites'] as $website) {
                        if (isset($this->websites[$website])) {
                            $websiteId = $this->websites[$website]->getId();
                            $this->assignTheme($themeId, 'websites', $websiteId);
                            $this->log->logLine(" - Assigned theme: {$themeName} to website: {$website}!");
                        } else {
                            $this->log->logException(" - Invalid website: {$website} for theme: {$themeName}!");
                        }
                    }
                }
            } else {
                $this->log->logException(" - Invalid theme: {$themeName}!");
            }
        }
    }

    /**
     * @param $themeId
     * @param $scope
     * @param $scopeId
     * @throws LocalizedException
     */
    protected function assignTheme($themeId, $scope, $scopeId)
    {
        $data['theme_theme_id'] = $themeId;
        $designConfigData = $this->configFactory->create($scope, $scopeId, $data);
        $this->designConfigRepository->save($designConfigData);
    }
}
