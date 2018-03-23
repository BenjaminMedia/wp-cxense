<?php

namespace Bonnier\WP\Cxense\Services;

class WidgetDocumentQuery
{
    private $widgetId;
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

    public function __construct()
    {
        $this->setWidgetId(wp_cxense()->settings->get_setting_value('sortby_widget_id', get_locale()));
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
    public function setContext($context): void
    {
        $this->context = $context;
        $this->query['context'] = $this->getContext();
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
    public function getSiteId(): string
    {
        return $this->siteId;
    }

    /**
     * @param string $siteId
     */
    public function setSiteId(string $siteId): void
    {
        $this->siteId = $siteId;
        $this->addParameter('siteId', $this->getSiteId());
    }

    public function getWidgetId()
    {
        return $this->widgetId;
    }

    /**
     * @param string $widgetId
     */
    private function setWidgetId(string $widgetId): void
    {
        $this->widgetId = $widgetId;
        $this->query['widgetId'] = $this->getWidgetId();
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
    public function setMatchingMode($context): WidgetDocumentQuery
    {
        $this->matchingMode = $context;
        $this->query['categories'] = [ 'taxonomy' => $this->getMatchingMode()];
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
    public function setCategories(array $categories = null): WidgetDocumentQuery
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
     * @param String $tags
     * @return WidgetDocumentQuery
     */
    public function setTags(array $tags = null): WidgetDocumentQuery
    {
        $this->tags = $this->getWpTerms($tags);
        $this->addParameter('tag', $this->getTags());
        return $this;
    }

    public function setCxenseUserId()
    {
        if (isset($_COOKIE['cX_P'])) {
            $this->cxenseUserId = $_COOKIE['cX_P'];
            $this->query['user'] = ['ids' => ['usi' => $this->getCxUserId()]];
        } else {
            $this->cxenseUserId = '';
        }
    }

    public function getCxUserId()
    {
        return $this->cxenseUserId;
    }

    public function get()
    {
        return wp_cxense()->get_widget_documents($this->query);
    }

    public function getWpTerms($termsArray)
    {
        if (is_array($termsArray)) {
            return implode(' ', array_column($termsArray, 'name'));
        }
        return "*";
    }
}
