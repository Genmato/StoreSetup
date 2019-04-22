<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

class MediaImage extends AbstractType
{
    protected $frontend = 'media_image';
    protected $type = 'varchar';
    protected $frontend_model = 'Magento\Catalog\Model\Product\Attribute\Frontend\Image';
}