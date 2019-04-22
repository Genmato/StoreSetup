<?php

namespace Genmato\StoreSetup\Model\Config\Category;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\ValidationStateInterface;

class Reader extends Filesystem
{
    protected $_idAttributes = [
        '/storesetup/category/rootcatalog' => 'id',
        '/storesetup/category/rootcatalog/category' => 'id',
        '/storesetup/category/rootcatalog/category/category' => 'id',
        '/storesetup/category/rootcatalog/category/category/category' => 'id',
        '/storesetup/category/rootcatalog/category/category/category/category' => 'id',
        '/storesetup/category/rootcatalog/category/category/category/category/category' => 'id',
        '/storesetup/category/rootcatalog/category/category/category/category/category/category' => 'id',
        '/storesetup/category/rootcatalog/category/category/category/category/category/category/category' => 'id',
        '/storesetup/category/rootcatalog/category/category/category/category/category/category/category/category' => 'id',
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
        $fileName = 'storesetup/category.xml',
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
