<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Category as CategoryConfig;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\FlagManager;

abstract class AbstractCategory extends AbstractAction
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    protected $currentCategories = false;

    /**
     * @var CategoryResource
     */
    protected $categoryResource;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;
    /**
     * @var CategoryConfig
     */
    protected $categoryConfig;

    /**
     * CategoryAbstract constructor.
     * @param FlagManager $flagManager
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param CollectionFactory $categoryCollection
     * @param CategoryConfig $categoryConfig
     * @internal param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        FlagManager $flagManager,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        CollectionFactory $categoryCollection,
        CategoryConfig $categoryConfig
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->categoryCollection = $categoryCollection->create();
        $this->categoryConfig = $categoryConfig;
        parent::__construct($flagManager);
    }

    /**
     * @return AbstractAction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function init()
    {
        if (!$this->currentCategories) {
            $collection = $this->categoryCollection;
            $collection->addAttributeToSelect(['storesetup_id', 'storesetup_checksum']);
            /** @var Category $category */
            foreach ($collection as $category) {
                if (!$category->getStoresetupId()) {
                    continue;
                }
                $this->currentCategories[$category->getStoresetupId()]['id'] = $category->getId();
                $this->currentCategories[$category->getStoresetupId()]['checksum'] = $category->getStoresetupChecksum();
            }
        }
        return parent::init();
    }

    /**
     * @param $categoryData
     * @param bool $parentCategory
     * @return Category
     * @throws \Exception
     */
    public function createCategory($categoryData, $parentCategory = false)
    {
        $checksum = sha1(json_encode($categoryData));
        $categoryData['storesetup_checksum'] = $checksum;

        if (isset($this->currentCategories[$categoryData['storesetup_id']])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            $category->setStoreId(0)->load($this->currentCategories[$categoryData['storesetup_id']]['id']);
            if ($categoryData['storesetup_checksum'] == $category->getStoresetupChecksum()) {
                return $category;
            }
            $this->log->logLine(' - Updating Category: %storesetup_id$s (changes found)!', $categoryData);
        } else {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if ($parentCategory) {
                $category->setParentId($parentCategory->getId());
                $category->setPath($parentCategory->getPath());
            }
            $this->log->logLine(' - Creating new Category: %storesetup_id$s!', $categoryData);
        }
        $category->addData($categoryData);

        try {
            $storeCategory = $category;
            foreach ($categoryData['store_data'] as $storeId => $data) {
                $storeCategory->addData($data);

                $storeCategory->setStoreId($storeId);
                $this->categoryResource->save($storeCategory);
            }
            $this->currentCategories[$categoryData['storesetup_id']] = [
                'id' => $category->getId(),
                'checksum' => $checksum
            ];
        } catch (Exception $ex) {
            $categoryData->setError($ex->getMessage());
            $this->log->logException(' - Error saving Root Category: %name$s (%error$s)', $categoryData);
        }
        return $category;
    }
}
