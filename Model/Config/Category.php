<?php

namespace Genmato\StoreSetup\Model\Config;

use Genmato\StoreSetup\Model\Config\Category\Reader;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;

class Category extends ConfigData
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        $cacheId = 'genmato_store_setup_category'
    ) {
        $this->reader = $reader;
        parent::__construct($reader, $cache, $cacheId);
    }
}
