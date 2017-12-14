<?php
/**
 * Document class file
 */

namespace Bonnier\WP\Cxense\Parsers;

/**
 * Document class
 */
class Document
{

    protected $data;

    /**
     * Constructor
     *
     * @param \stdClass $objData
     */
    public function __construct(\stdClass $objData)
    {
        $this->data = $objData;
    }

    /**
     * Assign fields as object properties
     *
     * @param string $strKey
     * @return null
     */
    public function __get($strKey)
    {
        return $this->data->{$strKey} ?? null;
    }
}
