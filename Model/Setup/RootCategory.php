<?php
namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Data as ConfigData;

use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;

class RootCategory extends AbstractCategory
{
    protected $identifier = 'rootcatalog';

    protected $addChildNodes = false;

    /**
     * @param ConfigData $source
     * @return array
     * @throws LocalizedException
     */
    public function setupData()
    {
        $this->init();

        $parentCategory = $this->categoryFactory->create()->load(Category::TREE_ROOT_ID);
        $categories = $this->categoryConfig->get();
        if (!is_array($categories)) {
            $categories = [];
        }

        if (!$this->forceAction && $this->checkChecksum($categories)) {
            return;
        }
        $this->saveChecksum($categories);

        $this->log->logInfo('Starting root category setup:');
        foreach ($categories as $rootCategory) {
            $this->createCategory($rootCategory, $parentCategory);
        }
    }
}