<?php
/**
 * DocumentSearchMissingFacet exception file
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * DocumentSearchMissingFacet class
 */
class DocumentSearchMissingFacet extends \Exception {
	
	/**
	 * Constructor
	 *
	 * @return DocumentSearchMissingFacet
	 */
	public function __construct($strMessage = '') {
		return parent::__construct($strMessage, 12);
	}
}