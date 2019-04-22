<?php

namespace Genmato\StoreSetup\Model\Config\Attribute;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\ValidationStateInterface;

class Reader extends Filesystem
{
    protected $idAttributes = [
        '/storesetup/product/attribute' => 'code',
        '/storesetup/product/attribute/label' => 'store',
        '/storesetup/product/attribute/values/value' => 'key',
        '/storesetup/product/attribute/values/value/label' => 'store',
        '/storesetup/category/attribute' => 'code',
        '/storesetup/category/attribute/label' => 'store',
        '/storesetup/category/attribute/values/value' => 'key',
        '/storesetup/category/attribute/values/value/label' => 'store',
        '/storesetup/customer/attribute' => 'code',
        '/storesetup/customer/attribute/label' => 'store',
        '/storesetup/customer/attribute/values/value' => 'key',
        '/storesetup/customer/attribute/values/value/label' => 'store',
        '/storesetup/address/attribute' => 'code',
        '/storesetup/address/attribute/label' => 'store',
        '/storesetup/address/attribute/values/value' => 'key',
        '/storesetup/address/attribute/values/value/label' => 'store',
    ];

    /**
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'storesetup/attribute.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
