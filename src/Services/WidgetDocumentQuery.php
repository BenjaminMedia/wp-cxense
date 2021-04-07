<?php

namespace Bonnier\WP\Cxense\Services;

use Bonnier\WP\Cxense\Exceptions\WidgetMissingId;
use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Exceptions\HttpException;
use Bonnier\WP\Cxense\Exceptions\WidgetException;
use Bonnier\WP\Cxense\Parsers\Document;
use Bonnier\WP\Cxense\WpCxense;

class WidgetDocumentQuery
{
    private $cxenseUserId;
    private $matchingMode;
    private $arrPayload = [];

    const POPULAR = 'trend';
    const RELATED = 'contextual'; // Articles similar to the current article.
    const RELATED_MAX_1_YEARS = 'contextual_max_1_y'; // Similar max 1 year old articles
    const RELATED_MAX_2_YEARS = 'contextual_max_2_y'; // Similar max 2 years old articles
    const SIMILAR_READS = 'collabctx'; // People who have read the current article have also read these articles.
    const RECENTLY_VIEWED = 'recent';

    /**
     * WidgetDocumentQuery constructor.
     */
    public function __construct()
    {
        $this->validateWidgetId(WpCxense::instance()->settings->getSortbyWidgetId());
        $this->setSiteId(WpCxense::instance()->settings->getSiteId());
    }

    /**
     * Create instance
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function addContext(string $key, $value) : self
    {
        if (!isset($this->arrPayload['context'])) {
            $this->arrPayload['context'] = [];
        }

        $this->arrPayload['context'][$key] = $value;
        return $this;
    }

    /**
     * @param string $siteId
     */
    public function setSiteId(string $siteId)
    {
        $this->addParameter('siteId', $siteId);
    }


    /**
     * Check for widget id presence
     * @param string $widgetId
     * @return null
     * @throws WidgetMissingId
     */
    private function validateWidgetId(?string $widgetId)
    {
        if (!isset($widgetId) && current_user_can('administrator')) {
            throw new WidgetMissingId('Missing request "widgetId" key!');
        }
        $this->arrPayload['widgetId'] = $widgetId;
    }


    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addParameter($key, $value)
    {
        if (!isset($this->arrPayload['context']['parameters'])) {
            $this->arrPayload['context']['parameters'] = [];
        }

        array_push($this->arrPayload['context']['parameters'], [
            'key' => $key,
            'value' => $value
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    public function bySimilarReads()
    {
        $this->setMatchingMode(self::SIMILAR_READS);
        return $this;
    }

    /**
     * @return $this
     */
    public function byRecentlyViewed()
    {
        $this->setMatchingMode(self::RECENTLY_VIEWED);
        //Recently viewed by User requires cxense user ID.
        $this->setCxenseUserId();
        return $this;
    }

    /**
     * @return $this
     */
    public function byShuffle()
    {
        $this->arrPayload['widgetId'] = WpCxense::instance()->settings->getShuffleWidgetId();
        return $this;
    }

    /**
     * @return $this
     */
    public function byPopular()
    {
        $this->setMatchingMode(self::POPULAR);
        return $this;
    }

    /**
     * @return $this
     */
    public function byRelated()
    {
        $this->setMatchingMode(self::RELATED);

        return $this;
    }

    /**
     * @param mixed $context
     * @return WidgetDocumentQuery
     */
    public function setMatchingMode($context)
    {
        $this->matchingMode = $context;
        $this->arrPayload['context']['categories'] = [ 'taxonomy' => $context];
        return $this;
    }

    /**
     * @param $categories
     * @return WidgetDocumentQuery
     */
    public function setCategories(array $categories = null)
    {
        $this->addParameter('category', $this->getWpTerms($categories));
        return $this;
    }

    /**
     * @param array|null $tags
     * @return $this
     */
    public function setTags(array $tags = null)
    {
        $this->addParameter('tag', $this->getWpTerms($tags));
        return $this;
    }

    /**
     * @param array|null $editorialTypes
     * @return $this
     */
    public function setEditorialTypes(array $editorialTypes = null)
    {
        $this->addParameter('editorialType', $this->getWpTerms($editorialTypes));
        return $this;
    }

    /**
     * Set cxenseUserId from Cookie. Might not work on WILLOW FRONTEND
     */
    public function setCxenseUserId()
    {
        if (!isset($_COOKIE['cX_P'])) {
            $this->cxenseUserId = '';
            return;
        }

        $this->cxenseUserId = $_COOKIE['cX_P'];
        $this->arrPayload['user']= ['ids' => ['usi' => $this->cxenseUserId]];
    }

    /**
     * @return array
     */
    public function getArrayPayLoad()
    {
        return $this->arrPayload;
    }

    /**
     * Return An array with total documents and matches
     * @return array
     */
    public function get()
    {
        $result = $this->getDocuments();
        $objDocuments = isset($result->items) ? $result->items : [];
        return [
            'totalCount' => count($objDocuments),
            'matches' => $this->parseDocuments($objDocuments)
        ];
    }

    /**
     * Get documents
     * @return mixed|null
     * @throws WidgetException
     */
    private function getDocuments()
    {
        try {
            $cacheKey = md5(json_encode($this->arrPayload));
            $expiresIn = 10 * HOUR_IN_SECONDS;

            if ($cachedResults = wp_cache_get($cacheKey, 'cxense_plugin')) {
                return $cachedResults;
            }

            $objResponse = HttpRequest::get_instance()->post('public/widget/data', [
                'body' => json_encode($this->arrPayload)
            ]);
            wp_cache_add($cacheKey, json_decode($objResponse->getBody()), 'cxense_plugin', $expiresIn);
        } catch (HttpException $exception) {
            if (is_admin()) {
                throw new WidgetException('Failed to load widget:' . $exception->getMessage());
            }
            return null;
        } catch (\Exception $exception) {
            return null;
        }

        return json_decode($objResponse->getBody());
    }


    /**
     * @param $termsArray
     * @return string
     */
    public function getWpTerms($termsArray)
    {
        //The second check to make sure we don't have null
        if (!is_array($termsArray) || !isset($termsArray[0])) {
            return "*";
        }

        return implode(' ', array_column($termsArray, 'name'));
    }


    /**
     * Parse documents to cxense object
     *
     * @param array $arrDocuments
     * @return array
     */
    public function parseDocuments(array $arrDocuments)
    {
        $arrCollection = [];

        foreach ($arrDocuments as $objDocument) {
            $arrCollection[] = new Document($objDocument);
        }

        return $arrCollection;
    }
}
