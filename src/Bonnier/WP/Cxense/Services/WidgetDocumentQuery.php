<?php

namespace Bonnier\WP\Cxense\Services;

use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Exceptions\HttpException;
use Bonnier\WP\Cxense\Exceptions\WidgetException;
use Bonnier\WP\Cxense\Exceptions\WidgetMissingId;
use Bonnier\WP\Cxense\Parsers\Document;

class WidgetDocumentQuery
{
    private $siteId;
    private $cxenseUserId;
    private $matchingMode;
    private $categories;
    private $tags;
    private $query;
    private $context;

    const POPULAR = 'trend';
    const RELATED = 'contextual'; // Articles similar to the current article.
    const SIMILAR_READS = 'collabctx'; // People who have read the current article have also read these articles.
    const RECENTLY_VIEWED = 'recent';

    /**
     * Payload array
     *
     * @var array $arrPayload
     */
    private $arrPayload = [];

    public function __construct()
    {
        //TODO remove
        header('Content-Type: text/html');
        $this->validateWidgetId(wp_cxense()->settings->get_setting_value('sortby_widget_id', get_locale()));
        $this->setSiteId(wp_cxense()->settings->get_setting_value("site_id", get_locale()));
    }

    public static function make()
    {
        return new static();
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
        if (isset($context['url'])) {
            $this->arrPayload['context'] = $context;
        }
//        $this->query['context'] = $this->getContext();
    }

    public function addContext(string $key, $value)
    {
        if (!isset($this->query['context'])) {
            $this->query['context'] = [];
        }
        $context = $this->getContext();
        $context[$key] = $value;
        $this->setContext($context);
        return $this;
    }

    /**
     * @return string
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param string $siteId
     */
    public function setSiteId(string $siteId)
    {
        $this->siteId = $siteId;
        $this->addParameter('siteId', $this->getSiteId());
    }


    /**
     * Check for widget id presence
     * @param string $widgetId
     * @return null
     * @throws WidgetMissingId
     */
    private function validateWidgetId(string $widgetId)
    {
        if (!isset($widgetId) && is_admin()) {
            throw new WidgetMissingId('Missing request "widgetId" key!');
        }
        $this->setWidgetId($widgetId);
    }

    /**
     * @param string $widgetId
     */
    private function setWidgetId(string $widgetId)
    {
        $this->query['widgetId'] = $widgetId;
        $this->arrPayload['widgetId'] = $widgetId;
    }


    public function addParameter($key, $value)
    {
        if (!isset($this->query['parameters'])) {
            $this->query['parameters'] = [];
        }
        array_push($this->query['parameters'], [
            'key' => $key,
            'value' => $value
        ]);
        $this->arrPayload['context']['parameters'] = $this->query['parameters'];
        return $this;
    }

    public function bySimilarReads()
    {
        $this->setMatchingMode(self::SIMILAR_READS);
        return $this;
    }

    public function byRecentlyViewed()
    {
        $this->setMatchingMode(self::RECENTLY_VIEWED);
        //Recently viewed by User requires cxense user ID.
        $this->setCxenseUserId();
        return $this;
    }

    public function byPopular()
    {
        $this->setMatchingMode(self::POPULAR);
        return $this;
    }

    public function byRelated()
    {
        $this->setMatchingMode(self::RELATED);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMatchingMode()
    {
        return $this->matchingMode;
    }

    /**
     * @param mixed $context
     * @return WidgetDocumentQuery
     */
    public function setMatchingMode($context)
    {
        $this->matchingMode = $context;
        $this->query['categories'] = [ 'taxonomy' => $context];

        $this->arrPayload['context']['categories'] = [ 'taxonomy' => $context];
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param $categories
     * @return WidgetDocumentQuery
     */
    public function setCategories(array $categories = null)
    {
        $this->categories = $this->getWpTerms($categories);
        $this->addParameter('category', $this->getCategories());
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array|null $tags
     * @return $this
     */
    public function setTags(array $tags = null)
    {
        $this->tags = $this->getWpTerms($tags);
        $this->addParameter('tag', $this->getTags());
        return $this;
    }

    public function setCxenseUserId()
    {
        if (isset($_COOKIE['cX_P'])) {
            $this->cxenseUserId = $_COOKIE['cX_P'];
            $this->arrPayload['user']= ['ids' => ['usi' => $this->cxenseUserId]];
            $this->query['user'] = ['ids' => ['usi' => $this->cxenseUserId]];
        } else {
            $this->cxenseUserId = '';
        }
    }


    /**
     * Return An array with total documents and matches
     * @return array
     */
    public function get()
    {
        var_dump($this->arrPayload);

        $result = $this->get_documents();
        dd($result);
        $objDocuments = isset($result->items) ? $result->items : [];
        return [
            'totalCount' => count($objDocuments),
            'matches' => $this->parse_documents($objDocuments)
        ];
    }


    /**
     * Get documents
     * @return mixed|null
     * @throws WidgetException
     */
    public function get_documents()
    {
        try {
            $objResponse = HttpRequest::get_instance()->post('public/widget/data', [
                'body' => json_encode($this->arrPayload)
            ]);
        } catch (HttpException $exception) {
            if (is_admin()) {
                throw new WidgetException('Failed to load widget:' . $exception->getMessage());
            }
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
    private function parse_documents(array $arrDocuments)
    {
        $arrCollection = [];

        foreach ($arrDocuments as $objDocument) {
            $arrCollection[] = new Document($objDocument);
        }

        return $arrCollection;
    }
}
