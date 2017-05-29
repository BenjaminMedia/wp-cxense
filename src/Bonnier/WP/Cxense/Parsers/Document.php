<?php
/**
 * Document class file
 */

namespace Bonnier\WP\Cxense\Parsers;

/**
 * Document class
 */
class Document {
	
	/**
	 * Constructor
	 *
	 * @param \stdClass $objData
	 * @return Document
	 */
	public function __construct(\stdClass $objData) {
		$this->assignData($objData);
	}
	
	/**
	 * Assign data to the document
	 *
	 * @param \stdClass $objData
	 * @return null
	 */
	private function assignData(\stdClass $objData) {
		foreach ($objData as $strKey => $mixData) {
			$this->$strKey = $mixData;
		}
	}
	
	/**
	 * Assign fields as object properties
	 *
	 * @param string $strKey
	 * @return null
	 */
	public function __get($strKey) {
		foreach ($this->fields as $objField) {
			if ($objField->field == $strKey) {
				return $objField->value;
			}
		}
	}
}