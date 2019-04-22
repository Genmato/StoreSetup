<?php
namespace Genmato\StoreSetup\Model\Setup;

use Magento\Framework\Exception\LocalizedException;

class Category extends AbstractCategory
{
    protected $identifier = 'category';

    /**
     * @return array
     * @throws LocalizedException
     */
    public function setupData()
    {
        $this->init();

        $categories = $this->categoryConfig->get();
        if (!is_array($categories)) {
            $categories = [];
        }

        if (!$this->forceAction && $this->checkChecksum($categories)) {
            return;
        }
        $this->saveChecksum($categories);

        $this->log->logInfo('Starting category setup:');
        foreach ($categories as $category) {
            if (isset($category['children']) && isset($this->currentCategories[$category['storesetup_id']]['id'])) {
                $parentCategory = $this->categoryFactory->create()->load($this->currentCategories[$category['storesetup_id']]['id']);

                foreach ($category['children'] as $subCategory) {
                    $this->createSubCategory($subCategory, $parentCategory);
                }
            }
        }
    }

    /**
     * @param $category
     * @param $parentCategory
     * @throws \Exception
     */
    public function createSubCategory($category, $parentCategory)
    {
        $parentCategory = $this->createCategory($category, $parentCategory);
        if (isset($category['children'])) {
            foreach ($category['children'] as $subCategory) {
                $this->createSubCategory($subCategory, $parentCategory);
            }
        }
    }
}
