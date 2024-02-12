<?php
/**
 * DocumentSearchWrongFacet exception file
 */

namespace Bonnier\WP\Cxense\Exceptions;

/**
 * DocumentSearchWrongFacet class
 */
class DocumentSearchWrongFacet extends \Exception
{
    /**
     * Constructor
     *
     * @return DocumentSearchWrongFacet
     */
    public function __construct($strMessage = '')
    {
        return parent::__construct($strMessage, 12);
    }
}
