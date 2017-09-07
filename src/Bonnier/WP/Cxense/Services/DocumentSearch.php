<?php
/**
 * DocumentSearch file class for cxense api
 */

namespace Bonnier\WP\Cxense\Services;

use Bonnier\WP\Cxense\Exceptions\DocumentSearchMissingSearch;
use Bonnier\WP\Cxense\Exceptions\DocumentSearchMissingCount;
use Bonnier\WP\Cxense\Exceptions\DocumentSearchWrongFacet;
use Bonnier\WP\Cxense\Exceptions\DocumentSearchWrongFilter;
use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\Parsers\Document;

/**
 * DocumentSearch class
 */
class DocumentSearch
{
    
    /**
     * Instance object
     *
     * @var DocumentSearch $objInstance
     */
    private static $objInstance;
    
    /**
     * Search array
     *
     * @var array $arrSearch
     */
    private $arrSearch = [];
    
    /**
     * Payload array
     *
     * @var array $arrPayload
     */
    private $arrPayload = [];

    /**
     * Settings object
     *
     * @var SettingsPage $objSettings
     */
    private $objSettings;
    
    /**
     * Returning fields
     *
     * @var array $arrFields
     */
    private $arrFields = [
        'organisation' => [
            'taxo-cat',
            'taxo-cat-top',
            'taxo-cat-url',
            'taxo-editorialtype',
            'pagetype'
        ],
        'generic' => [
            'author',
            'description',
            'title',
            'click_url',
            'url',
            'dominantthumbnail',
            'recs-publishtime'
        ]
    ];
    
    /**
     * Constructor
     */
    private function __construct()
    {
        //
    }

    /**
     * Singleton implementation
     *
     * @return DocumentSearch
     */
    public static function get_instance()
    {
        if (!isset(self::$objInstance)) {
            $obj = __CLASS__;
            self:: $objInstance = new $obj();
        }
        return self::$objInstance;
    }
    
    /**
     * Set settings object
     *
     * @param SettingsPage $objSettings
     * @return DocumentSearch
     */
    public function set_settings(SettingsPage $objSettings)
    {
        $this->objSettings = $objSettings;
        
        if ($strPrefix = $this->objSettings->get_organisation_prefix()) {
            $this->arrFields['organisation'] = array_map(function ($strValue) use ($strPrefix) {
                return $strPrefix . '-' . $strValue;
            }, $this->arrFields['organisation']);
        }
        return $this;
    }

    /**
     * Set search input array
     *
     * @param array $arrSearch
     * @return DocumentSearch
     */
    public function set_search(array $arrSearch)
    {
        $this->arrSearch = $arrSearch;

        return $this;
    }
    
    /**
     * Get documents from search result
     *
     * @return \stdClass
     */
    public function get_documents()
    {
        $this->validate_query_key();
        
        $objDocuments = $this
            ->set_per_page()
            ->set_page()
            ->set_facets()
            ->set_filter()
            ->set_spellcheck()
            ->set_highlights()
            ->set_result_fields()
            ->get();
        
        $objDocuments->matches = $this->parse_documents($objDocuments->matches);
        return $objDocuments;
    }
    
    /**
     * Get documents
     *
     * @return \Illuminate\Support\Collection
     */
    private function get()
    {
        $this->set_site_id();
        $this->set_log_query();
        $this->arrPayload['query'] = QueryLanguage::getQuery($this->arrSearch['query']);

        $objResponse = HttpRequest::get_instance()->set_auth($this->objSettings)->post('document/search', [
            'body' => json_encode($this->arrPayload)
        ]);
        
        return json_decode($objResponse->getBody());
    }

    /**
     * Validate the search request
     *
     * @return null
     * @throws DocumentSearchMissingSearch
     */
    private function validate_query_key()
    {
        if (!isset($this->arrSearch['query'])) {
            throw new DocumentSearchMissingSearch('Missing request "query" key!');
        }
    }
    
    /**
     * Set siteId to the search request
     *
     * @return DocumentSearch
     */
    private function set_site_id()
    {
        $this->arrPayload['siteId'] = $this->objSettings->get_site_id();
        return $this;
    }
    
    /**
     * Set token operator
     *
     * @todo Not used at the moment, check need of token operator
     * @return DocumentSearch
     */
    private function set_token_operator()
    {
        $this->arrPayload['token-op'] = 'and';
        return $this;
    }
    
    /**
     * Set highlights
     *
     * @return DocumentSearch
     */
    private function set_highlights()
    {
        if (isset($this->arrSearch['highlights'])) {
            $this->arrPayload['highlights'] = $this->arrSearch['highlights'];
        }
        
        return $this;
    }
    
    /**
     * Set facets
     *
     * @return DocumentSearch
     * @throws DocumentSearchWrongFacet
     */
    private function set_facets()
    {
        if (isset($this->arrSearch['facets'])) {
            if (!is_array($this->arrSearch['facets'])) {
                throw new DocumentSearchWrongFacet('"Facets" key is not an array');
            }
            
            $this->arrPayload['facets'] = $this->arrSearch['facets'];
        }
        
        return $this;
    }
    
    /**
     * Set filter
     *
     * @return DocumentSearch
     * @throws DocumentSearchWrongFilter
     */
    private function set_filter()
    {
        if (isset($this->arrSearch['filter'])) {
            if (!is_array($this->arrSearch['filter'])) {
                throw new DocumentSearchWrongFilter('"Filter" key is not an array');
            }
            
            $arrFilterLines = [];
            foreach ($this->arrSearch['filter'] as $arrFilter) {
                $arrFilterLines[] = 'filter(' . $arrFilter['field'] . ':"' . stripslashes($arrFilter['value']) . '")';
            }
            
            $strFilterOperator = 'OR';
            if (isset($this->arrSearch['filter_operator'])) {
                $strFilterOperator = $this->arrSearch['filter_operator'];
            }
            
            $this->arrPayload['filter'] = implode(' ' . $strFilterOperator . ' ', $arrFilterLines);
        }
        
        return $this;
    }
    
    /**
     * Set page limit to the search array
     *
     * @param integer $intCount Total items per page
     * @return Search
     */
    private function set_per_page($intCount = 10)
    {
        $this->arrPayload['count'] = $intCount;
        
        if (isset($this->arrSearch['count'])) {
            $this->arrPayload['count'] = $this->arrSearch['count'];
        }
        
        return $this;
    }

    /**
     * Set page number for calculating the correct starting offset
     *
     * @param integer $intPage Page number
     * @return Search
     * @throws DocumentSearchMissingCount
     */
    private function set_page($intPage = 1)
    {
        if (!isset($this->arrPayload['count']) || !$this->arrPayload['count']) {
            throw new DocumentSearchMissingCount('\DocumentSearch::setPerPage() is required before \DocumentSearch::setPage()');
        }
        
        // get the page
        if (isset($this->arrSearch['page'])) {
            $intPage = $this->arrSearch['page'];
        }
        
        // set the starting point
        $this->arrPayload['start'] = $this->arrPayload['count'] * ($intPage - 1);
        
        return $this;
    }
    
    /**
     * Set result fields
     *
     * @return DocumentSearch
     */
    private function set_result_fields()
    {
        $this->arrPayload['resultFields'] = array_merge($this->arrFields['generic'], $this->arrFields['organisation']);
        return $this;
    }
    
    /**
     * Set logQuery
     *
     * @return DocumentSearch
     */
    private function set_log_query()
    {
        $this->arrPayload['logQuery'] = $this->arrSearch['query'];
        return $this;
    }
    
    /**
     * Set spellcheck
     *
     * @return DocumentSearch
     */
    private function set_spellcheck()
    {
        if (isset($this->arrSearch['spellcheck']) && $this->arrSearch['spellcheck'] === true) {
            $this->arrPayload['spellcheck']['query'] = $this->arrSearch['query'];
        }
        return $this;
    }
    
    /**
     * Parse documents to cxense object
     *
     * @param array $arrDocuments
     * @return array
     */
    private function parse_documents(array $arrDocuments)
    {
        $arrCollection = [];
        
        foreach ($arrDocuments as $objDocument) {
            $arrCollection[] = new Document($objDocument);
        }
        
        return $arrCollection;
    }
}
