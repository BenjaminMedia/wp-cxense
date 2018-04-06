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
    protected $formattedFields;
    protected $formattedHighlights;

    /**
     * Constructor
     *
     * @param \stdClass $objData
     */
    public function __construct(\stdClass $objData)
    {
        $this->data = $objData;
        $this->setFormattedFields($objData->fields);
        $this->setFormattedHighlights($objData->highlights);
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

    public function getField($fieldKey)
    {
        return isset($this->formattedFields->{$fieldKey}) ? $this->formattedFields->{$fieldKey} : null;
    }

    public function getHighlight($fieldKey)
    {
        return isset($this->formattedHighlights->{$fieldKey}) ? $this->formattedHighlights->{$fieldKey} : null;
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

    private function setFormattedHighlights($fields)
    {
        $this->formattedHighlights = new \stdClass();
        foreach ($fields as $cxField) {
            $this->formattedHighlights->{$cxField->field} = $cxField->highlight;
        }
    }

    private function setFormattedFields($fields)
    {
        $this->formattedFields = new \stdClass();
        foreach ($fields as $cxField) {
            $this->formattedFields->{$cxField->field} = $cxField->value;
        }
    }
}
