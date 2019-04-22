<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

class TypeList
{
    /**
     * @var string[]
     */
    protected $types;

    /**
     * Constructor
     *
     * @param array $type
     */
    public function __construct(
        array $type = []
    ) {
        $this->types = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param $type
     * @return bool|mixed|string
     */
    public function getType($type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }
        return false;
    }
}
