<?php
/**
 * DocumentSearchMissingCount exception file.
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * DocumentSearchMissingCount class.
 */
class DocumentSearchMissingCount extends \Exception
{
    /**
     * Constructor.
     *
     * @return DocumentSearchMissingCount
     */
    public function __construct($strMessage = '')
    {
        return parent::__construct($strMessage, 11);
    }
}
