<?php
/**
 * WidgetMissingId exception file
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * WidgetMissingId class
 */
class WidgetException extends \Exception
{
    
    /**
     * Constructor
     *
     * @return \Bonnier\WP\Cxense\Exceptions\WidgetException
     */
    public function __construct($strMessage = '')
    {
        return parent::__construct($strMessage, 20);
    }
}
