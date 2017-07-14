<?php
/**
 * QueryLanguage file class for cxense api
 */

namespace Bonnier\WP\Cxense\Services;

/**
 * QueryLanguage class
 */
class QueryLanguage {
	
	/**
	 * Default search field priority
	 *
	 * @var string $strFieldPriority
	 */
	private static $strFieldPriority = 'title^3, bod-taxo-cat^2, description^2, bod-taxo-cat-top^1, body^1';
	
	/**
	 * Get query
	 *
	 * @param string $strQuery
	 * @return string
	 */
	public static function getQuery($strQuery) {
		
		$strQuery = '"' . stripslashes($strQuery) . '"';
		$strQuery = self::$strFieldPriority . ':' . $strQuery;
		$strQuery = 'query(' . $strQuery . ')';
		
		
		return $strQuery;
	}
	
}