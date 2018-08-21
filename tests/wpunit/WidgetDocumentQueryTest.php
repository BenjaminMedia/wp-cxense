<?php

namespace Bonnier\WP\Cxense\Tests;

use Bonnier\WP\Cxense\Exceptions\WidgetMissingId;
use Bonnier\WP\Cxense\Parsers\Document;
use Bonnier\WP\Cxense\Services\WidgetDocumentQuery;
use Codeception\TestCase\WPTestCase;
use PHPUnit\Framework\Constraint\IsType;

class WidgetDocumentQueryTest extends WPTestCase
{
    protected $widgetIdKey = 'sortby_widget_id';
    protected $widgetIdValue = 'e12313b31424';
    protected $siteIdKey = 'site_id';
    protected $siteIdValue = '123456789';
    protected $permalink = 'http://ivd.test';
    protected $cxenseApiUrl = 'https://api.cxense.com/public/widget/data';

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

    private function newWidgetDocumentQueryWithParams()
    {
        $this->setSiteAndWidgetIds();
        $this->widgetDocumentQuery = WidgetDocumentQuery::make()
            ->addContext('url', $this->permalink)
            ->byRelated()
            ->addParameter('pageType', 'article gallery story')
            ->setCategories();

        return $this->widgetDocumentQuery;
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
        foreach ($this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'] as $param) {
            if ($param['key'] === 'category' && $param['value'] === '*') {
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

        foreach ($this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'] as $param) {
            if ($param['key'] === 'category' && $param['value'] === $category->name) {
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

        foreach ($this->widgetDocumentQuery->getArrayPayLoad()['context']['parameters'] as $param) {
            if ($param['key'] === 'pageType' && $param['value'] === 'article gallery story') {
                $test = true;
            }
        }
        $this->assertTrue($test);
    }

    public function testGetDocumentsHttpRequest()
    {
        //This is mocking a real Request and giving back a fake HTTP Response
        //It prevents wordpress from executing the API call and gives back the response you provide in the filter
        add_filter( 'pre_http_request', [$this, 'fakeApiCall'], 10, 3);

        $results = $this->newWidgetDocumentQueryWithParams()->get();
        $this->assertArrayHasKey('totalCount', $results);
        $this->assertArrayHasKey('matches', $results);
        $this->assertInternalType(IsType::TYPE_INT, $results['totalCount']);

        if (isset($results['totalCount']) && $results['totalCount'] > 0) {
            $this->assertInstanceOf(Document::class, $results['matches'][0]);
        }
    }

    public function fakeApiCall($false, $args, $url )
    {
        if($url !== $this->cxenseApiUrl){
            return null;
        }
        return $this->getFakeHttpResponse();
    }

    public function getFakeHttpResponse()
    {
        $fakeResponse['response']['code'] = 200;
        $fakeResults = new \stdClass();
        $fakeResults->items = array();

        for($i=0; $i<10;$i++){
            $fakeData = new \stdClass();
            $fakeData->{'recs-articleid'} = $i;
            $fakeData->dominantimage = 'https://images.bonnier.cloud/files/bob/production/2018/08/16121934/01_IMGL22721.jpg';
            $fakeData->dominantthumbnail = "http://content-thumbnail.cxpublic.com/content/dominantthumbnail/24256b39389b377fa03bc96ec72b647c8e0f3ef9.jpg?5b75505b";
            $fakeData->_type = "backfill";
            $fakeData->description = "Bliv inspireret af vores stylists måde at indrette med planter og dybe farver.";
            $fakeData->{'bod-taxo-cat-top'} = "Indretning";
            $fakeData->title = "Sådan har du aldrig før set Vinterhaven på Ordrupgaard";
            $fakeData->click_url = "http://api.cxense.com/public/widget/click/randomLongId";
            $fakeData->{'bod-taxo-cat'} = "Indretning";
            $fakeData->{'recs-publishtime'} = "2018-08-16T12:18:17.000Z";
            $fakeData->url = "https://bobedre.dk/indretning/sadan-har-du-aldrig-for-set-vinterhaven-pa-ordrupgaard";

            $fakeResults->items[] = $fakeData;
        }

        $fakeResponse['body'] = json_encode($fakeResults);

        return $fakeResponse;
    }
}
