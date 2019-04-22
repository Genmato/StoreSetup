<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\AttributeSet as AttributeSetConfig;
use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Attribute\GroupRepository;
use Magento\Eav\Model\AttributeSetManagement;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupdCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\FlagManager;

class ProductAttributeSet extends AbstractAction
{
    protected $identifier = 'attribute_set';

    protected $entityTypeCode = Product::ENTITY;
    /**
     * @var TypeFactory
     */
    protected $typeFactory;
    /**
     * @var SetFactory
     */
    protected $setFactory;
    /**
     * @var AttributeSetManagement
     */
    protected $attributeSetManagement;

    /**
     * @var AttributeSetCollectionFactory
     */
    protected $attributeSetCollectionFactory;
    protected $attributeSets;
    protected $entityType;
    protected $groupFactory;
    protected $groupRepository;
    protected $groupCollectionFactory;
    /**
     * @var ProductAttributeManagementInterface
     */
    protected $productAttributeManagement;
    /**
     * @var AttributeSetConfig
     */
    protected $attributeSetConfig;

    /**
     * AttributeSet constructor.
     * @param FlagManager $flagManager
     * @param TypeFactory $typeFactory
     * @param SetFactory $setFactory
     * @param AttributeSetCollectionFactory $attributeSetCollectionFactory
     * @param AttributeSetManagement $attributeSetManagement
     * @param GroupFactory $groupFactory
     * @param GroupRepository $groupRepository
     * @param GroupdCollectionFactory $groupCollectionFactory
     * @param ProductAttributeManagementInterface $productAttributeManagement
     * @param AttributeSetConfig $attributeSetConfig
     */
    public function __construct(
        FlagManager $flagManager,
        TypeFactory $typeFactory,
        SetFactory $setFactory,
        AttributeSetCollectionFactory $attributeSetCollectionFactory,
        AttributeSetManagement $attributeSetManagement,
        GroupFactory $groupFactory,
        GroupRepository $groupRepository,
        GroupdCollectionFactory $groupCollectionFactory,
        ProductAttributeManagementInterface $productAttributeManagement,
        AttributeSetConfig $attributeSetConfig
    ) {
        parent::__construct($flagManager);
        $this->typeFactory = $typeFactory;
        $this->setFactory = $setFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->productAttributeManagement = $productAttributeManagement;
        $this->attributeSetConfig = $attributeSetConfig;
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

        parent::init();
    }

    /**
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function setupData()
    {
        $this->init();

        $attributeSets = $this->attributeSetConfig->get();
        if (!is_array($attributeSets)) {
            $attributeSets = [];
        }

        if (!$this->forceAction && $this->checkChecksum($attributeSets)) {
            return;
        }
        $this->saveChecksum($attributeSets);

        $this->log->logInfo('Starting attribute set setup:');
        foreach ($attributeSets as $attributeSet) {
            $attributeSetId = $this->createAttributeSet($attributeSet);
            foreach ($attributeSet['groups'] as $group) {
                $groupId = $this->createAttributeGroup($group, $attributeSetId);
                foreach ($group['attributes'] as $attribute) {
                    $this->assignAttributeToGroup($attribute, $attributeSetId, $groupId);
                }
            }
        }
    }

    /**
     * @param $attributeData
     * @param $attributeSetId
     * @param $groupId
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function assignAttributeToGroup($attributeData, $attributeSetId, $groupId)
    {
        $this->log->logLine('    - Assigned Attribute %attribute_code$s to group!', $attributeData);
        $this->productAttributeManagement->assign(
            $attributeSetId,
            $groupId,
            $attributeData['attribute_code'],
            $attributeData['sort_order']
        );
    }

    /**
     * @param $groupData
     * @param $attributeSetId
     * @return bool
     * @throws NoSuchEntityException
     * @throws StateException
     */
    protected function createAttributeGroup($groupData, $attributeSetId)
    {
        unset($groupData['attributes']);

        $check = $attributeSetId . '-' . $groupData['attribute_group_code'];
        if (isset($this->attributeGroups[$check])) {
            $this->log->logLine('  - Updating Attribute group %attribute_group_name$s!', $groupData);
            $attributeGroup = $this->groupRepository->get($this->attributeGroups[$check]);
        } else {
            $this->log->logLine('  - Creating Attribute group %attribute_group_name$s!', $groupData);

            $attributeGroup = $this->groupFactory->create();
            $groupData['attribute_set_id'] = $attributeSetId;
            $groupData['attributes'] = [];
        }
        $attributeGroup->addData($groupData);

        try {
            $group = $this->groupRepository->save($attributeGroup);
        } catch (Exception $ex) {
            $this->log->logException(" - Error saving Attribute group: {$groupData['attribute_group_name']} ({$ex->getMessage()})!");
            return false;
        }
        return $group->getId();
    }

    /**
     * @param $attributeSetData
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function createAttributeSet($attributeSetData)
    {
        if (isset($this->attributeSets[$attributeSetData['name']])) {
            $this->log->logLine(' - AttributeSet %name$s exists!', $attributeSetData);
            return $this->attributeSets[$attributeSetData['name']];
        }
        if (isset($this->attributeSets[$attributeSetData['copy_from']])) {
            $defaultSetId = $this->attributeSets[$attributeSetData['copy_from']];
        } else {
            $defaultSetId = $this->entityType->getDefaultAttributeSetId();
        }

        $attributeSet = $this->setFactory->create();
        $data = [
            'attribute_set_name'    => $attributeSetData['name'],
            'entity_type_id'        => $this->entityType->getId(),
            'sort_order'            => $attributeSetData['sort_order'],
        ];
        $attributeSet->setData($data);

        try {
            $attrSet = $this->attributeSetManagement->create($this->entityTypeCode, $attributeSet, $defaultSetId);
            $this->log->logLine(' - AttributeSet %name$s created!', $attributeSetData);
        } catch (Exception $ex) {
            $this->log->logException(" - Error saving AttributeSet: {$attributeSetData['name']} ({$ex->getMessage()})!");
            return false;
        }
        return $attrSet->getId();
    }
}
