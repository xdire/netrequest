<?php namespace Xdire\NetRequest;

use Xdire\NetRequest\Interfaces\ConfigurableWrapper;
use Xdire\NetRequest\Models\Configuration\Configuration;
use Xdire\NetRequest\Models\RequestGet;
use Xdire\NetRequest\Models\RequestPost;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 11:51 AM
 *
 * HttpRequest
 * ------------------------------------------------------------------------------------------
 *
 * Library which allows developer to use standard network communications with HTTP(HTTPS)
 * protocol.
 *
 * HttpRequest supports two types of network connection drivers:
 *
 * 1) Native PHP driver - built on top of PHP sockets
 * 2) Linux CURL Library
 *
 * If execution with current driver will fail to produce results, HttpRequest will fallback
 * to other version of driver which may produce successful results. However if both
 * drivers will not be able to produce any suitable result — it means that or system or OS
 * configuration failed.
 */

class HttpRequest implements ConfigurableWrapper
{

    const DEFAULT_DRIVER = 0;
    const CURL_DRIVER = 1;

    private static $executor = 0;

    private static $configurationName = null;

    public static function useDriver($driver = 0) {

        self::$executor = $driver;

    }

    public static function __whichDriver() {

        return self::$executor;

    }

    /**
     * Ouputs configuration for assigned name
     * which can be additionally tuned for
     * add some specific properties to your
     * request
     *
     * @param string $name
     * @return Configuration
     */
    public static function config($name = "default") {

        self::$configurationName = $name;

        return Configuration::get(self::$configurationName);

    }

    /**
     * Will make HTTP Get Request
     *
     * @param   string  $url
     * @param   array   $headers
     * @return  RequestGet
     */
    public static function get($url = null, $headers = []) {
        return new RequestGet($url, $headers, self::$configurationName);
    }

    /**
     * Will make HTTP POST Request
     *
     * @param string    $url
     * @param array     $headers
     * @param string    $payload
     * @return RequestPost
     */
    public static function post($url = null, $headers = [], $payload = null) {
        return new RequestPost($url, $headers, $payload, self::$configurationName);
    }

    /**
     * Will make HTTP PUT Request
     *
     * @param string    $url
     * @param array     $headers
     * @param string    $payload
     * @return RequestPost
     */
    public static function put($url = null, $headers = [], $payload = null) {
        return new RequestPost($url, $headers, $payload, self::$configurationName);
    }

}