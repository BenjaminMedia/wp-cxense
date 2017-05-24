<?php
/**
 * WidgetMissingId exception file
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * WidgetMissingId class
 */
class WidgetMissingId extends \Exception {
	
	/**
	 * Constructor
	 *
	 * @return WidgetMissingId
	 */
	public function __construct($strMessage = '') {
		return parent::__construct($strMessage, 12);
	}
}