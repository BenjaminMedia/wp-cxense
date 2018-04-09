<?php
/**
 * Document class file
 */

namespace Bonnier\WP\Cxense\Parsers;

/**
 * Document class
 */
class Document implements \JsonSerializable
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
        return isset($this->data->{$strKey}) ? $this->data->{$strKey} : null;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
