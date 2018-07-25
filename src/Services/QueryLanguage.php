<?php
/**
 * QueryLanguage file class for cxense api
 */

namespace Bonnier\WP\Cxense\Services;

/**
 * QueryLanguage class
 */
class QueryLanguage
{
    /**
     * Get query
     * @param $orgPrefix
     * @param $strQuery
     * @return string
     */
    public static function getQuery($orgPrefix, $strQuery)
    {
        //Default search field priority
        $strFieldPriority = 'title^3, '.$orgPrefix.'-taxo-cat^2, description^2, '.$orgPrefix.'-taxo-cat-top^1, body^1';
        $strQuery = '"' . stripslashes($strQuery) . '"';
        $strQuery = $strFieldPriority . ':' . $strQuery;
        $strQuery = 'query(' . $strQuery . ')';

        return $strQuery;
    }
}
