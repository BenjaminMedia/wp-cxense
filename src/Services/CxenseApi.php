<?php

namespace Bonnier\WP\Cxense\Services;

use Bonnier\WP\Cxense\Http\HttpRequest;
use Bonnier\WP\Cxense\Exceptions\HttpException;
use Bonnier\WP\Cxense\Models\Post;
use Bonnier\WP\Cxense\Settings\SettingsPage;
use Bonnier\WP\Cxense\WpCxense;
use Exception;

class CxenseApi
{
    const EXCEPTION_USER_NOT_DEFINED = 0;
    const EXCEPTION_UNAUTHORIZED = 1;
    const EXCEPTION_TIME_OUT = 2;
    const CXENSE_PROFILE_PUSH = '/profile/content/push';
    const CXENSE_PROFILE_DELETE = '/document/delete';
    const CXENSE_WIDGET_DATA = '/public/widget/data';

    const ACF_CATEGORY_ID = 'field_58e39a7118284';

    /**
     * @param $widgetId
     * @return null
     */
    public static function get_widget_data($widgetId)
    {
        $objResponse = HttpRequest::get_instance()->post(self::CXENSE_WIDGET_DATA, [
            'timeout' => 2,
            'redirection' => 1,
            'body' => json_encode([
                'widgetId' => $widgetId
            ])
        ]);

        if ($objResponse->getStatusCode() !== 200) {
            return null;
        };

        return json_decode($objResponse->getBody())->items;
    }


    /**
     * @param string|int $postId Either post ID or permalink
     * @param bool $delete
     * @return bool|null
     */
    public static function pingCrawler($postId, $delete = false)
    {
        if (!wp_is_post_revision($postId) && !wp_is_post_autosave($postId)) {
            $contentUrl = is_numeric($postId) ? get_permalink($postId) : $postId;

            $categoryId = $_REQUEST['acf'][static::ACF_CATEGORY_ID] ?? null;
            if ($categoryId) {
                $categorySlug = get_term($categoryId)->slug;
                $contentUrl = str_replace('/uncategorized/', "/$categorySlug/", $contentUrl);
            }

            try {
                if (WpCxense::instance()->settings->getSiteId()) {
                    if ($delete || !Post::is_published($postId)) {
                        $apiPath = self::CXENSE_PROFILE_DELETE;
                        return self::request($apiPath, [
                            'siteId' => WpCxense::instance()->settings->getSiteId(),
                            'url' => $contentUrl
                        ]);
                    }

                    $apiPath = self::CXENSE_PROFILE_PUSH;
                    return self::request($apiPath, ['url'=> $contentUrl]);
                }
            } catch (Exception $e) {
                if ($e instanceof HttpException) {
                    error_log('WP cXense: Failed calling cXense api: ' . $apiPath . ' response code: ' .
                        $e->getCode() .' error: ' . $e->getMessage());
                }

                if ($e->getCode() == self::EXCEPTION_USER_NOT_DEFINED) {
                    error_log('PHP Warning: To use CXense push you must define constants CXENSE_USER_NAME and CXENSE_API_KEY');
                } elseif ($e->getCode() == self::EXCEPTION_UNAUTHORIZED) {
                    error_log('PHP Warning: Could not authorize with defined CXENSE_USER_NAME and CXENSE_API_KEY');
                }
            }
        }
        return null;
    }

    /**
     * @param string $path
     * @param array|string $args
     * @param int $timeout Seconds until timeout
     * @return boolean
     * @throws Exception
     */
    public static function request($path, $args, $timeout=5)
    {
        self::getCxenseUser();

        $objResponse = HttpRequest::get_instance()->set_auth()->post($path, [
            'body' => json_encode($args),
            'timeout' => $timeout
        ]);

        if ($objResponse->getStatusCode() === 401) {
            throw new Exception('Authorization required', self::EXCEPTION_UNAUTHORIZED);
        } elseif ($objResponse->getStatusCode() < 200 || $objResponse->getStatusCode() >= 300) {
            throw new Exception('Unexpected response, code: '.$objResponse->getStatusCode().' message: '.$objResponse->getMessage(), -1);
        }
        return true;
    }

    /**
     * @throws Exception
     */
    private static function getCxenseUser()
    {
        $cxUser = WpCxense::instance()->settings->getApiUser();

        if (!$cxUser) {
            throw new Exception('You must define constants CXENSE_USER_NAME and CXENSE_API_KEY', self::EXCEPTION_USER_NOT_DEFINED);
        }
    }
}
