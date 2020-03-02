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
    public static function getQuery($orgPrefix, $searchString)
    {
        // Default search field priority
        $strFieldPriority = 'title^3, '.$orgPrefix.'-taxo-cat^2, description^2, '.$orgPrefix.'-taxo-cat-top^1, body^1';
        $query = 'query(' . $strFieldPriority . ':"' . stripslashes($searchString) . '")';

        // If the query is two words then also search for the words put together without a space between
        if (preg_match('/^(\S+) (\S+)$/', $searchString, $res)) {
            $searchStringSpaceRemoved = $res[1] . $res[2];
            $query .= ' or query(' . $strFieldPriority . ':"' . stripslashes($searchStringSpaceRemoved) . '")';
        }

        // Add query for phrase search with higher priority
        $strFieldPriorityPhrase = 'title^6, '.$orgPrefix.'-taxo-cat^4, description^4, '.$orgPrefix.'-taxo-cat-top^2, body^2';
        $query .= ' or query(' . $strFieldPriorityPhrase . ':"' . stripslashes($searchString) . '", token-op=phrase)';

        return $query;
    }
}
