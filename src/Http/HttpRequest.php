<?php
/**
 * HttpRequest class file
 */

namespace Bonnier\WP\Cxense\Http;

use Bonnier\WP\Cxense\Services\DocumentSearch;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\WpCxense;

/**
 * HttpRequest class
 */
class HttpRequest
{
    const DEFAULT_OPTIONS = [
        'timeout' => 15,
        'redirection' => 15,
    ];

    /**
     * Cxense main url
     *
     * @var string $strBaseUrl
     */
    private $strBaseUri = 'https://api.cxense.com';

    /**
     * Instance object
     *
     * @var DocumentSearch $objInstance
     */
    private static $objInstance;

    /**
     * Headers array
     *
     * @var array $arrHeaders
     */
    private $arrHeaders = [
        'Content-Type' => 'application/json'
    ];

    /**
     * Singleton implementation
     *
     * @return HttpRequest
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
     * Get http request
     *
     * @param string $strPath
     * @param array $arrOptions
     * @return HttpResponse
     */
    public function get($strPath, array $arrOptions = [])
    {
        $request = wp_remote_get(
            $this->build_uri($strPath),
            array_merge(
            self::DEFAULT_OPTIONS,
            $arrOptions,
            [
                'headers' => $this->arrHeaders
            ]
        )
        );

        return new HttpResponse($request);
    }

    /**
     * Post http request
     *
     * @param string $strPath
     * @param array $arrOptions
     * @return HttpResponse
     */
    public function post($strPath, array $arrOptions = [])
    {
        $request = wp_remote_post(
            $this->build_uri($strPath),
            array_merge(
            self::DEFAULT_OPTIONS,
            $arrOptions,
            [
                'headers' => $this->arrHeaders
            ]
        )
        );

        return new HttpResponse($request);
    }

    /**
     * Build url
     *
     * @param string $strPath
     */
    private function build_uri($strPath)
    {
        return rtrim($this->strBaseUri, '/') . '/' . ltrim($strPath, '/');
    }

    /**
     * Set auth header
     *
     * @return null
     */
    public function set_auth()
    {
        $date = date("Y-m-d\TH:i:s.000O");
        $this->arrHeaders['X-cXense-Authentication'] = sprintf(
            'username=%s date=%s hmac-sha256-hex=%s',
            WpCxense::instance()->settings->getApiUser(),
            $date,
            hash_hmac("sha256", $date, WpCxense::instance()->settings->getApiKey())
        );

        return $this;
    }
}
