<?php

namespace Bonnier\WP\Cxense\Services;

class Query
{
    private $widgetId;
    private $siteId;
    private $cxenseUserId;
    private $context;
    private $categories;
    private $tags;
    private $query;

    const POPULAR = 'trend';
    const RELATED = 'contextual'; // Articles similar to the current article.
    const SIMILAR_READS = 'collabctx'; // People who have read the current article have also read these articles.
    const RECENTLY_VIEWED = 'recent';

    public function __construct()
    {
        $this->setWidgetId(wp_cxense()->settings->get_setting_value('sortby_widget_id', get_locale()));
        $this->setSiteId( wp_cxense()->settings->get_setting_value("site_id", get_locale()));
    }

    public static function make(){
        return new static();
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

    public function getWidgetId(){
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



    public function addParameter($key, $value){
        if(!isset($this->query['parameters'])){
            $this->query['parameters'] = [];
        }
        array_push($this->query['parameters'],[
            'key' => $key,
            'value' => $value
        ]);
        return $this;
    }

    public function bySimilarReads() {
        $this->setContext(Query::SIMILAR_READS);
        return $this;
    }

    public function byRecentlyViewed() {
        $this->setContext(Query::RECENTLY_VIEWED);
        //Recently viewed by User requires cxense user ID.
        $this->setCxenseUserId();
        return $this;
    }

    public function byPopular() {
        $this->setContext(Query::POPULAR);
        return $this;
    }

    public function byRelated() {
        $this->setContext(Query::RELATED);
        return $this;
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
     * @return Query
     */
    public function setContext($context): Query
    {
        $this->context = $context;
        $this->query['categories'] = [ 'taxonomy' => $this->getContext()];
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
     * @return Query
     */
    public function setCategories(array $categories = null): Query
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
     * @return Query
     */
    public function setTags(Array $tags = null): Query
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

    public function get(){
        //ddHtml($this->query);
        return wp_cxense()->get_widget_documents($this->query);
    }

    public function getWpTerms($termsArray){
        $terms = "*";
        if(is_array($termsArray)) {
            foreach ($termsArray as $key => $item) {
                if (!$item instanceof \WP_Term) {
                    break;
                }
                $terms .= $item->name;
                if ($key < sizeof($termsArray) - 1) {
                    $terms .= ' ';
                }
            }
        }
        return $terms;
    }
}