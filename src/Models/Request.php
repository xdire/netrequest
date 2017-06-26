<?php namespace Xdire\NetRequest\Models;

/**
 *  anton.repin <ar@xdire.io>
 *  Date: 1/26/17
 *  Time: 1:16 PM
 *
 *  Request
 *  ------------------------------------------------------------------------------------------
 *  Abstracted Request for descendant classes be able to extend default Request functionality
 *
 */

use Xdire\NetRequest\Interfaces\RequestRunner;
use Xdire\NetRequest\Models\Runners\NativeRunner;

abstract class Request
{

    const GET = 1;
    const POST = 2;
    const PUT = 3;
    const DELETE = 4;

    protected $configurationName = null;

    /** @var string  */
    protected $url;

    /** @var int  */
    protected $method;

    /** @var RequestHeaders */
    protected $headers;

    /** @var string|null  */
    protected $payload;

    /** @var bool */
    protected $debugMode = false;

    /** @var RequestRunner|null */
    protected $executor = null;

    /**
     * Request constructor.
     * @param string|null   $url
     * @param int|null      $method
     * @param string[]      $headers
     * @param null|string   $payload
     * @param null|string   $configName
     */
    function __construct(
        $url = null,
        $method = null,
        array $headers = [],
        $payload = null,
        $configName = null)
    {

        $this->url = $url;
        $this->method = $method;
        $this->headers = new RequestHeaders($headers);
        $this->payload = $payload;
        $this->configurationName = $configName;

    }

    protected function usingMethod($method) {
        $this->method = $method;
    }

    protected function usingExecutor($executor = 0) {

        if($executor === 0) {
            $this->executor = new NativeRunner();
        }
        elseif ($executor === 1) {
            $this->executor = null;
        }

    }

    /**
     * Add URL string, can be full-type URL string with Query parameters included
     * ------------------------------------------------------------------------------------------
     * @param  string $url
     * @return $this
     */
    public function withUrl($url) {

        $this->url = $url;
        return $this;

    }

    /**
     * Add header
     * ------------------------------------------------------------------------------------------
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function withHeader($name, $value) {

        $this->headers->addHeader($name, $value);
        return $this;

    }

    /**
     * Add headers
     * ------------------------------------------------------------------------------------------
     * @param string[] $headers
     * @return $this
     */
    public function withHeaders(array $headers) {

        $this->headers = new RequestHeaders($headers);
        return $this;

    }

    /**
     * Add or replace Payload at POST requests
     * ------------------------------------------------------------------------------------------
     * @param $payload
     * @return $this
     */
    protected function withPayload($payload) {

        $this->payload = $payload;
        return $this;

    }

    /**
     * Debug log will be included in response
     * ------------------------------------------------------------------------------------------
     * @return $this
     */
    public function withDebug() {

        $this->debugMode = true;
        return $this;

    }

    /**
     * Will immediately execute request
     * ------------------------------------------------------------------------------------------
     * @return Response
     */
    public function send() {

        $parsedRequest = new PreparedRequest($this->url, $this->method, $this->headers, $this->payload);

        if($this->debugMode)
            $parsedRequest->setDebugMode(true);

        $content = $this->executor->connectAndGet($parsedRequest);
        $response = new PreparedResponse($content);

        if($this->debugMode)
            $response->debugLog = $this->executor->getDebugLog();

        return $response->getResponse();

    }

}