<?php

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * Class DocumentSearchWrongSorting
 *
 * @package \Bonnier\WP\Cxense\Exceptions
 */
class DocumentSearchWrongSorting extends \Exception
{
    /**
     * DocumentSearchWrongSorting constructor.
     *
     * @param string $strMessage
     *
     */
    public function __construct($strMessage = '')
    {
        return parent::__construct($strMessage, 11);
    }
}
