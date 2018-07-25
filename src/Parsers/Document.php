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

        if (isset($objData->fields, $objData->highlights)) {
            $this->setFormattedFields($objData->fields);
            $this->setFormattedHighlights($objData->highlights);
        }
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
