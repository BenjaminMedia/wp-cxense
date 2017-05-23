<?php
/**
 * DocumentSearchMissingSearch exception file
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * DocumentSearchMissingSearch class
 */
class DocumentSearchMissingSearch extends \Exception {
	
	/**
	 * Constructor
	 *
	 * @return DocumentSearchMissingSearch
	 */
	public function __construct($strMessage = '') {
		return parent::__construct($strMessage, 10);
	}
}