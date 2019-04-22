<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\DataObject;

abstract class AbstractType extends DataObject
{
    protected $defaultValue = "";
    protected $frontend = 'text';
    protected $type = 'varchar';
    protected $scope = Attribute::SCOPE_STORE;
    protected $class = '';
    protected $backend = '';
    protected $source = '';
    protected $frontend_model = '';

    /**
     * AbstractType constructor.
     */
    public function __construct()
    {
        $this->addData([
            'frontend_input' => $this->frontend,
            'frontend_model' => $this->frontend_model,
            'backend_type' => $this->type,
            'is_global' => $this->scope,
            'frontend_class' => $this->class,
            'backend_model' => $this->backend,
            'source_model' => $this->source,
            'is_visible' => true,
            'is_required' => false,
            'is_searchable' => false,
            'is_visible_in_advanced_search' => false,
            'is_filterable' => false,
            'is_filterable_in_search' => false,
            'is_comparable' => false,
            'is_user_defined' => true,
            'group' => null,
            'is_visible_on_front' => false,
            'is_used_in_product_listing' => false,
            'is_unique' => false,
            'is_used_for_promo_rules' => false,
            'is_wysiwyg_enabled' => false,
            'is_html_allowed_on_front' => false,
            'is_used_for_sort_by' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'position' => null,
            'sort_order' => 1,
            'note' => '',
        ]);
    }

    /**
     * @param $input
     * @return array
     */
    public function buildAttributeData($input)
    {
        return array_merge($this->getData(), $input);
    }
}
