<?php
/**
 * DocumentSearchWrongFilter exception file
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * DocumentSearchWrongFilter class
 */
class DocumentSearchWrongFilter extends \Exception {
	
	/**
	 * Constructor
	 *
	 * @return DocumentSearchWrongFilter
	 */
	public function __construct($strMessage = '') {
		return parent::__construct($strMessage, 13);
	}
}