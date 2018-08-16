<?php

namespace Bonnier\WP\Cxense\Tests;

use Bonnier\WP\Cxense\Exceptions\WidgetMissingId;
use Bonnier\WP\Cxense\Services\WidgetDocumentQuery;
use Bonnier\WP\Cxense\WpCxense;
use Codeception\TestCase\WPTestCase;

class WidgetDocumentQueryTest extends WPTestCase
{
    protected $widgetIdKey = 'sortby_widget_id';
    protected $widgetIdValue = 'e30ea28424568ac42178e75e67228f59c1a5a9ed';

    protected $siteIdKey = 'site_id';
    protected $siteIdValue = '9222363338076056876';
    protected $permalink = 'http://ivd.test';

    protected $arrPayload =[];

    /* @var WidgetDocumentQuery */
    protected $widgetDocumentQuery;

    public function setUp()
    {
        parent::setUp();
        //set current user to admin
        wp_set_current_user(1);
    }

    public function testThrowsErrorOnMissingWidgetId()
    {
        $this->expectException(WidgetMissingId::class);
        $this->expectExceptionMessage('Missing request "widgetId" key!');
        $this->widgetDocumentQuery = WidgetDocumentQuery::make();
    }

    private function setSiteAndWidgetIds()
    {
        update_option('wp_cxense_settings',
            array($this->widgetIdKey => $this->widgetIdValue,
                  $this->siteIdKey => $this->siteIdValue)
        );
    }

    private function newWidgetDocumentQuery()
    {
        $this->setSiteAndWidgetIds();
        $this->widgetDocumentQuery = WidgetDocumentQuery::make();
    }

    public function testWidgetDocumentQueryHasAWidgetId()
    {
        $this->setSiteAndWidgetIds();
        $this->newWidgetDocumentQuery();
        $this->assertArrayHasKey('widgetId', $this->widgetDocumentQuery->getArrayPayLoad());
    }

    public function testWidgetDocumentQueryHasASiteId()
    {
        $this->setSiteAndWidgetIds();
        $this->newWidgetDocumentQuery();
        var_dump($this->widgetDocumentQuery);
        $this->assertContains('siteId', $this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'][0]);
    }

    public function testWidgetDocumentQueryHasContext()
    {
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->addContext('url', $this->permalink);
        $this->assertArrayHasKey('url', $this->widgetDocumentQuery->getArrayPayLoad()['context']);
        $this->assertContains($this->permalink, $this->widgetDocumentQuery->getArrayPayLoad()['context']);
    }

    public function testWidgetDocumentQueryHasRelatedTax()
    {
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->byRelated();
        $this->assertArrayHasKey('taxonomy', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
        $this->assertContains('contextual', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
    }

    public function testWidgetDocumentQueryHasPopularTax()
    {
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->byPopular();
        $this->assertArrayHasKey('taxonomy', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
        $this->assertContains('trend', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
    }

    public function testWidgetDocumentQueryHasRecentlyViewedTax()
    {
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->byRecentlyViewed();
        $this->assertArrayHasKey('taxonomy', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
        $this->assertContains('recent', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
    }

    public function testWidgetDocumentQueryHasSimilarReadsTax()
    {
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->bySimilarReads();
        $this->assertArrayHasKey('taxonomy', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
        $this->assertContains('collabctx', $this->widgetDocumentQuery->getArrayPayLoad()['context']['categories']);
    }

    public function testWidgetDocumentQueryDoesntHaveTaxonomy()
    {
        $this->newWidgetDocumentQuery();
        $this->assertArrayNotHasKey('categories', $this->widgetDocumentQuery->getArrayPayLoad()['context']);
    }

    public function testWidgetDocumentQueryDefaultCategory()
    {
        $test = false;
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->setCategories();
        foreach($this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'] as $param){
                if($param['key'] === 'category' && $param['value'] === '*'){
                    $test = true;
                }
        }
        $this->assertTrue($test);
    }

    public function testWidgetDocumentQueryHasACategory()
    {
        $test = false;
        $this->newWidgetDocumentQuery();
        $category = get_term_by('id', 1, 'category');
        $this->widgetDocumentQuery->setCategories(array($category));

        foreach($this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'] as $param){
            if($param['key'] === 'category' && $param['value'] === $category->name){
                $test = true;
            }
        }
        $this->assertTrue($test);
    }

    public function testWidgetDocumentQueryHasPageTypes()
    {
        $test = false;
        $this->newWidgetDocumentQuery();
        $this->widgetDocumentQuery->addParameter('pageType', 'article gallery story');

        foreach($this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'] as $param){
            if($param['key'] === 'pageType' && $param['value'] === 'article gallery story'){
                $test = true;
            }
        }
        $this->assertTrue($test);
    }

}