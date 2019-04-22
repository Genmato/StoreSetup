<?php
namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Attribute as AttributeConfig;
use Genmato\StoreSetup\Model\Setup\Attribute\TypeList;

use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory as AttributeFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\FlagManager;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractAttribute extends AbstractAction
{

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * @var AttributeSetCollectionFactory
     */
    protected $attributeSetCollectionFactory;
    protected $attributeSets;
    protected $entityType;

    protected $attributeGroups;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var TypeList
     */
    protected $typeList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $stores;
    /**
     * @var AttributeConfig
     */
    protected $attributeConfig;

    /**
     * AttributeSet constructor.
     * @param FlagManager $flagManager
     * @param TypeFactory $typeFactory
     * @param AttributeSetCollectionFactory $attributeSetCollectionFactory
     * @param CollectionFactory|GroupCollectionFactory $groupCollectionFactory
     * @param AttributeFactory $attributeFactory
     * @param StoreManagerInterface $storesManager
     * @param AttributeConfig $attributeConfig
     */
    public function __construct(
        FlagManager $flagManager,
        TypeFactory $typeFactory,
        AttributeSetCollectionFactory $attributeSetCollectionFactory,
        GroupCollectionFactory $groupCollectionFactory,
        AttributeFactory $attributeFactory,
        StoreManagerInterface $storesManager,
        AttributeConfig $attributeConfig
    ) {
        $this->typeFactory = $typeFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->attributeFactory = $attributeFactory;
        $this->storeManager = $storesManager;
        $this->attributeConfig = $attributeConfig;

        parent::__construct($flagManager);
    }

    /**
     * @return AbstractAction|void
     */
    public function init()
    {
        $this->entityType = $this->typeFactory->create()->loadByCode($this->entityTypeCode);
        $this->attributeSets = [];
        $collection = $this->attributeSetCollectionFactory->create();
        $collection->addFieldToFilter('entity_type_id', ['eq'=>$this->entityType->getId()]);
        foreach ($collection as $set) {
            $this->attributeSets[$set->getAttributeSetName()] = $set->getId();
        }

        $collection = $this->groupCollectionFactory->create();
        foreach ($collection as $group) {
            $this->attributeGroups[$group->getAttributeSetId() . '-' . $group->getAttributeGroupCode()] = $group->getId();
        }
        $this->stores = $this->storeManager->getStores(true, true);

        parent::init();
    }

    /**
     * @param ConfigData $source
     * @return array
     */
    public function setupData()
    {
        $this->init();

        $attributes = $this->attributeConfig->get($this->attributeType);
        if (!is_array($attributes)) {
            $attributes = [];
        }

        if (!$this->forceAction && $this->checkChecksum($attributes)) {
            return;
        }
        $this->saveChecksum($attributes);

        $this->log->logInfo(sprintf("Starting %s attribute setup:", $this->attributeType));
        foreach ($attributes as $attribute) {
            $this->createAttribute($attribute);
        }
    }

    /**
     * @param $attributeData
     */
    protected function createAttribute($attributeData)
    {
        unset($attributeData['attribute_id']);
        $attributeData['entity_type_id'] = $this->entityType->getId();

        $attributeExists = false;
        try {
            $attribute = $this->attributeFactory->create()->loadByCode($this->entityTypeCode, $attributeData['attribute_code']);
            if ($attribute->getId()) {
                $this->log->logLine(' - Updating attribute %attribute_code$s!', $attributeData);
                $attributeExists = true;
            }
        } catch (NoSuchEntityException $ex) {
            $attribute = $this->attributeFactory->create();
        }

        if (!$attributeExists) {
            $this->log->logLine(' - Creating new attribute %attribute_code$s!', $attributeData);
            $attribute = $this->attributeFactory->create();
        }

        $attribute->addData($attributeData);

        $attribute->save();
    }
}