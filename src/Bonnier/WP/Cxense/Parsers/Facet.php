<?php
/**
 * Facet class file
 */

namespace Bonnier\WP\Cxense\Parsers;

/**
 * Facet class
 */
class Facet {
	
	/**
	 * Constructor
	 *
	 * @param \stdClass $objData
	 * @return Facet
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
}