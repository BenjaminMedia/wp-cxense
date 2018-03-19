<?php
/**
 * WidgetDocument file class for cxense api
 */

namespace Bonnier\WP\Cxense\Services;

use Bonnier\WP\Cxense\Exceptions\HttpException;
use Bonnier\WP\Cxense\Exceptions\WidgetException;
use Bonnier\WP\Cxense\Exceptions\WidgetMissingId;
use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\Parsers\Document;

/**
 * WidgetDocument class
 */
class WidgetDocument
{
    
    /**
     * Instance object
     *
     * @var WidgetDocument $objInstance
     */
    private static $objInstance;
    
    /**
     * Input array
     *
     * @var array $arrInput
     */
    private $arrInput = [];
    
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
     * Constructor
     *
     * @param array $arrInput
     * @return WidgetDocument
     */
    private function __construct(array $arrInput)
    {
        $this->arrInput = $arrInput;
        $this->validate_widget_id();
    }
    
    /**
     * Singleton implementation
     *
     * @param array $arrInput
     * @return WidgetDocument
     */
    public static function get_instance(array $arrInput)
    {
        if (!isset(self::$objInstance)) {
            $obj = __CLASS__;
            self:: $objInstance = new $obj($arrInput);
        }

        self::set_arrayInput($arrInput);
        return self::$objInstance;
    }
    
    /**
     * Set settings object
     *
     * @param SettingsPage $objSettings
     * @return WidgetDocument
     */
    public function set_settings(SettingsPage $objSettings)
    {
        $this->objSettings = $objSettings;
        return $this;
    }

    /**
     * Set ArrayInput to update Singleton object if called again with different args
     *
     * @param array $arrInput
     */
    public static function set_arrayInput(array $arrInput)
    {
        self::$objInstance->validate_widget_id();
        self::$objInstance->arrInput = $arrInput;
    }
    
    /**
     * Get documents
     *
     * @return array
     */
    public function get_documents()
    {
        $objDocuments = isset($this->set_categories()->set_parameters()->set_contextualUrls()->set_contextUrl()->set_user()->get()->items)
            ? $this->set_categories()->set_parameters()->set_contextualUrls()->set_user()->get()->items
            : [];
        return [
            'totalCount' => count($objDocuments),
            'matches' => $this->parse_documents($objDocuments)
        ];
    }
    
    /**
     * Check for widget id presence
     *
     * @return null
     */
    private function validate_widget_id()
    {
        if (!isset($this->arrInput['widgetId']) && is_admin()) {
            throw new WidgetMissingId('Missing request "widgetId" key!');
        }
    }
    
    /**
     * Get documents
     *
     * @return \Illuminate\Support\Collection
     */
    private function get()
    {
        $this->set_widget_id();

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
     * Set widget_id to the request payload
     *
     * @return WidgetDocument
     */
    private function set_widget_id()
    {
        $this->arrPayload['widgetId'] = $this->arrInput['widgetId'];
        return $this;
    }
    
    /**
     * Set categories array to the request payload
     *
     * @return WidgetDocument
     */
    private function set_categories()
    {
        if (isset($this->arrInput['categories']) && is_array($this->arrInput['categories'])) {
            $this->arrPayload['context']['categories'] = $this->arrInput['categories'];
        }
        return $this;
    }
    
    /**
     * Set parameters array to the request payload
     *
     * @return WidgetDocument
     */
    private function set_parameters()
    {
        if (isset($this->arrInput['parameters']) && is_array($this->arrInput['parameters'])) {
            $this->arrPayload['parameters'] = $this->arrInput['parameters'];
        }
        return $this;
    }

    /**
     * Set contextualUrls array to the request payload
     *
     * @return WidgetDocument
     */
    private function set_contextualUrls()
    {
        if (isset($this->arrInput['contextualUrls']) && is_array($this->arrInput['contextualUrls'])) {
            $this->arrPayload['context']['contextualUrls'] = $this->arrInput['contextualUrls'];
        }
        return $this;
    }

    /**
     * Set contextualUrls array to the request payload
     *
     * @return WidgetDocument
     */
    private function set_contextUrl()
    {
        if (isset($this->arrInput['context']['url'])) {
            $this->arrPayload['context']['url'] = $this->arrInput['context']['url'];
        }
        return $this;
    }

    /**
     * Set user array to the request payload
     *
     * @return WidgetDocument
     */
    private function set_user()
    {
        if (isset($this->arrInput['user']) && is_array($this->arrInput['user'])) {
            $this->arrPayload['user']= $this->arrInput['user'];
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
