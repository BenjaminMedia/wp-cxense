<?php
/**
 * WidgetDocument file class for cxense api
 */

namespace Bonnier\WP\Cxense\Services;

use Bonnier\WP\Cxense\Exceptions\WidgetMissingId;
use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\Parsers\Document;

/**
 * WidgetDocument class
 */
class WidgetDocument {
	
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
	private function __construct(array $arrInput) {
		$this->arrInput = $arrInput;
		$this->validate_widget_id();
	}
	
	/**
	 * Singleton implementation
	 *
	 * @param array $arrInput
	 * @return WidgetDocument
	 */
	public static function get_instance(array $arrInput) {
		if (!isset(self::$objInstance)) {
			$obj = __CLASS__;
			self:: $objInstance = new $obj($arrInput);
		}
		return self::$objInstance;
	}
	
	/**
	 * Set settings object
	 *
	 * @param SettingsPage $objSettings
	 * @return WidgetDocument
	 */
	public function set_settings(SettingsPage $objSettings) {
		$this->objSettings = $objSettings;
		return $this;
	}
	
	/**
	 * Get documents
	 *
	 * @return array
	 */
	public function get_documents() {
		
		$objDocuments = $this->set_categories()->set_parameters()->get();

		return [
			'totalCount' => count($objDocuments->items),
			'matches' => $this->parse_documents($objDocuments->items)
		];
	}
	
	/**
	 * Check for widget id presence
	 *
	 * @return null
	 */
	private function validate_widget_id() {
		if (!isset($this->arrInput['widgetId'])) {
			throw new WidgetMissingId('Missing request "widgetId" key!');
		}
	}
	
	/**
	 * Get documents
	 *
	 * @return \Illuminate\Support\Collection
	 */
	private function get() {
		
		$this->set_widget_id();

		$objResponse = HttpRequest::get_instance()->post('public/widget/data', [
			'body' => json_encode($this->arrPayload)
		]);
		
		return json_decode($objResponse->getBody());
	}
	
	/**
	 * Set widget_id to the request payload
	 *
	 * @return WidgetDocument
	 */
	private function set_widget_id() {
		$this->arrPayload['widgetId'] = $this->arrInput['widgetId'];
		return $this;
	}
	
	/**
	 * Set categories array to the request payload
	 *
	 * @return WidgetDocument
	 */
	private function set_categories() {
		
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
	private function set_parameters() {
		
		if (isset($this->arrInput['parameters']) && is_array($this->arrInput['parameters'])) {
			$this->arrPayload['context']['parameters'] = $this->arrInput['parameters'];
		}
		return $this;
	}
	
	/**
	 * Parse documents to cxense object
	 *
	 * @param array $arrDocuments
	 * @return array
	 */
	private function parse_documents(array $arrDocuments) {
		$arrCollection = [];
		
		foreach ($arrDocuments as $objDocument) {
			$arrCollection[] = new Document($objDocument);
		}
		
		return $arrCollection;
	}
}