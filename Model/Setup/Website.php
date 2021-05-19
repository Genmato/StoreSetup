<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Website as WebsiteConfig;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Framework\FlagManager;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\GroupInterfaceFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;

class Website extends AbstractAction
{
    protected $identifier = 'website';

    /**
     * @var WebsiteInterfaceFactory $websiteFactory
     */
    protected $websiteFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var WebsiteResource
     */
    protected $websiteResource;

    /**
     * @var GroupInterfaceFactory $storeGroupFactory
     */
    protected $groupFactory;

    /**
     * @var GroupResource
     */
    protected $groupResource;

    /**
     * @var StoreInterfaceFactory $storeFactory
     */
    protected $storeFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreResource
     */
    protected $storeResource;
    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;

    protected $currentCategories;
    /**
     * @var WebsiteConfig
     */
    protected $websiteConfig;

    /**
     * Constructor
     *
     * @param FlagManager $flagManager
     * @param WebsiteInterfaceFactory $websiteFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param WebsiteResource $websiteResource
     * @param GroupInterfaceFactory $storeGroup
     * @param GroupResource $groupResource
     * @param StoreInterfaceFactory $storeFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreResource $storeResource
     * @param CollectionFactory $categoryCollection
     * @param WebsiteConfig $websiteConfig
     */
    public function __construct(
        FlagManager $flagManager,
        WebsiteInterfaceFactory $websiteFactory,
        WebsiteRepositoryInterface $websiteRepository,
        WebsiteResource $websiteResource,
        GroupInterfaceFactory $storeGroup,
        GroupResource $groupResource,
        StoreInterfaceFactory $storeFactory,
        StoreRepositoryInterface $storeRepository,
        StoreResource $storeResource,
        CollectionFactory $categoryCollection,
        WebsiteConfig $websiteConfig
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->websiteRepository = $websiteRepository;
        $this->websiteResource = $websiteResource;
        $this->groupFactory = $storeGroup;
        $this->groupResource = $groupResource;
        $this->storeFactory = $storeFactory;
        $this->storeRepository = $storeRepository;
        $this->storeResource = $storeResource;
        $this->categoryCollection = $categoryCollection->create();
        $this->websiteConfig = $websiteConfig;
        parent::__construct($flagManager);
    }

    /**
     * @return AbstractAction
     * @throws LocalizedException
     */
    public function init()
    {
        if (!$this->currentCategories) {
            $collection = $this->categoryCollection;
            $collection->addAttributeToSelect(['storesetup_id']);
            /** @var Category $category */
            foreach ($collection as $category) {
                if (!$category->getStoresetupId()) {
                    continue;
                }
                $this->currentCategories[$category->getStoresetupId()] = $category->getId();
            }
        }
        return parent::init();
    }

    /**
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function setupData()
    {
        $this->init();

        $websites = $this->websiteConfig->get();
        if (!is_array($websites)) {
            $websites = [];
        }

        if (!$this->forceAction && $this->checkChecksum($websites)) {
            return;
        }
        $this->saveChecksum($websites);

        $this->log->logInfo('Starting website setup:');
        foreach ($websites as $website) {
            $parentWebsite = $this->createStoreWebsite($website);
            if (isset($website['groups'])) {
                foreach ($website['groups'] as $group) {
                    $parentGroup = $this->createStoreGroup($group, $parentWebsite);
                    if (isset($group['stores'])) {
                        foreach ($group['stores'] as $store) {
                            $this->createStoreView($store, $parentWebsite, $parentGroup);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $websiteData
     * @return WebsiteInterface|null
     * @throws AlreadyExistsException
     */
    protected function createStoreWebsite($websiteData)
    {
        $latestCode = $websiteData['code'];

        try {
            $website = $this->websiteRepository->get($latestCode);
            $this->log->logLine(' - Updating website: %code$s!', $websiteData);
        } catch (NoSuchEntityException $e) {
            $website = null;
        }

        if ($website === null && isset($websiteData['previous']) && is_array($websiteData['previous'])) {
            try {
                $previousCode = '';
                foreach ($websiteData['previous'] as $previousCode) {
                    try {
                        $website = $this->websiteRepository->get($previousCode);
                        if ($website->getId()) {
                            $this->log->logLine(sprintf(" - Renaming website from: %s to %s !", $previousCode, $latestCode));
                            break;
                        }
                    } catch (NoSuchEntityException $e) {
                        $website = null;
                    }
                }
            } catch (NoSuchEntityException $e) {
                $website = null;
            }
        }

        if ($website === null) {
            $website = $this->websiteFactory->create();
            $this->log->logLine(' - Creating new website: %code$s!', $websiteData);
        }

        $website->setName($websiteData['name'])
            ->setIsDefault($websiteData['is_default'])
            ->setSortOrder($websiteData['sort_order'])
            ->setCode($latestCode);

        $this->websiteResource->save($website);
        return $website;
    }

    /**
     * @param $groupData
     * @param $parentWebsite
     * @return GroupInterface
     * @throws AlreadyExistsException
     */
    protected function createStoreGroup($groupData, $parentWebsite)
    {
        $latestCode = $groupData['code'];
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, $latestCode, 'code');

        if (!$group->getId() && isset($groupData['previous']) && is_array($groupData['previous'])) {
            $previousCode = '';
            foreach ($groupData['previous'] as $previousCode) {
                try {
                    $this->groupResource->load($group, $previousCode, 'code');
                    if ($group->getId()) {
                        break;
                    }
                } catch (NoSuchEntityException $e) {
                }
            }
            $this->log->logLine(sprintf("  - Renaming store group from: %s to %s", $previousCode, $latestCode));
        } else {
            $this->log->logLine('  - Updating store group: %name$s', $groupData);
        }
        if (!$group->getId()) {
            $this->log->logLine('  - Creating new store group: %name$s', $groupData);
        }

        $group->setName($groupData['name'])
            ->setRootCategoryId($this->getCatalogId($groupData['catalog']))
            ->setCode($latestCode)
            ->setWebsiteId($parentWebsite->getId());
        $this->groupResource->save($group);
        return $group;
    }

    /**
     * @param $catalogName
     */
    protected function getCatalogId($catalogName)
    {
        if ($this->currentCategories[$catalogName]) {
            return $this->currentCategories[$catalogName];
        }
        return;
    }

    /**
     * @param $storeData
     * @param $parentWebsite
     * @param $parentGroup
     * @return StoreInterface|null
     * @throws AlreadyExistsException
     */
    protected function createStoreView($storeData, $parentWebsite, $parentGroup)
    {
        $latestCode = $storeData['code'];
        try {
            $store = $this->storeRepository->get($latestCode);
            $this->log->logLine('   - Updating store view: %code$s', $storeData);
        } catch (NoSuchEntityException $e) {
            $store = null;
        }

        if ($store === null && isset($storeData['previous']) && is_array($storeData['previous'])) {
            try {
                $previousCode = '';
                foreach ($storeData['previous'] as $previousCode) {
                    try {
                        $store = $this->storeRepository->get($previousCode);
                        if ($store->getId()) {
                            $this->log->logLine(sprintf(" - Renaming store view from: %s to %s !", $previousCode, $latestCode));
                            break;
                        }
                    } catch (NoSuchEntityException $e) {
                        $store = null;
                    }
                }
            } catch (NoSuchEntityException $e) {
                $store = null;
            }
        }
        if ($store === null) {
            $store = $this->storeFactory->create(['code' => $latestCode]);
            $this->log->logLine('   - Creating new store view: %code$s', $storeData);
        }
        
        $store->setName($storeData['name'])
            ->setStoreGroupId($parentGroup->getId())
            ->setWebsiteId($parentWebsite->getId())
            ->setSortOrder($storeData['sort_order'])
            ->setCode($storeData['code'])
            ->setIsActive($storeData['is_active']);
        $this->storeResource->save($store);
        return $store;
    }

}
