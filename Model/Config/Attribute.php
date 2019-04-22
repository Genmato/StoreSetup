<?php

namespace Genmato\StoreSetup\Model\Config;

use Genmato\StoreSetup\Model\Config\Attribute\Reader;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;

class Attribute extends ConfigData
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CATEGORY = 'category';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_ADDRESS = 'address';

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
        $cacheId = 'genmato_store_setup_attribute'
    ) {
        $this->reader = $reader;
        parent::__construct($reader, $cache, $cacheId);
    }
}
