<?php namespace Xdire\NetRequest\Models;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 3:36 PM
 */

use Xdire\NetRequest\Models\Exceptions\ConfigurationException;

class PreparedRequest
{

    const HTTP = 0;
    const HTTP_SSL = 1;

    private static $methods = [
        0 => "GET", 1 => "GET", 2 => "POST", 3 => "PUT", 4 => "DELETE"
    ];

    /** @var string|null  */
    private $configurationName = null;

    /** @var int| null */
    private $protocol = null;
    /** @var string|null */
    private $host = null;
    /** @var string|null */
    private $path = null;
    /** @var int|null */
    private $port = null;
    /** @var string|null */
    private $query = null;
    /** @var int|null */
    private $method = null;
    /** @var RequestHeaders|null */
    private $headers = null;
    /** @var string|null */
    private $payload = null;

    private $debugMode = false;

    /**
     * PreparedRequest constructor.
     * ---------------------------------------------------------------
     * @param string              $requestUrl
     * @param int                 $requestMethod
     * @param RequestHeaders|null $requestHeaders
     * @param string|null         $payload
     * @throws ConfigurationException
     */
    function __construct($requestUrl, $requestMethod = 1, RequestHeaders $requestHeaders = null, $payload = null)
    {
        $this->_parseURL($requestUrl);
        $this->method = $requestMethod;
        $this->headers = $requestHeaders;
        $this->payload = $payload;
    }

    /**
     * Will return prepared HTTP Protocol string without Payload
     * ---------------------------------------------------------------
     * @return string
     */
    public function createAndGetRequestData() {

        $path = $this->path !== null ? $this->path : "/";
        $queryData = $this->query !== null ? "?".$this->query : "";
        $pathData = strlen($path.$queryData) > 0 ? $path.$queryData." " : "";

        $requestData = "";

        $requestData .= self::$methods[$this->method]." ".$pathData."HTTP/1.0\r\n";
        $requestData .= "Host: ".$this->host."\r\n";

        foreach ($this->headers->getHeaders() as $name => $value) {
            $requestData .= $name.": ".$value."\r\n";
        }

        $requestData .= "User-Agent: netrequest/1.0.0\r\n";
        $requestData .= "Accept: */*\r\n";

        // Check if we providing Payload with our request
        if($this->payload !== null) {

            $requestData .= "Content-length: ".mb_strlen($this->payload);
            $requestData .= "\r\n\r\n";

        }
        // Check if we using POST request and Query string together which contents should go inside
        // a request body instead of header
        elseif ($this->method !== 1 && $this->query !== null) {

            $requestData .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $requestData .= "Content-length: ".mb_strlen($this->query);
            $requestData .= "\r\n\r\n";
            $this->payload = $this->query;

        }
        // If we not passing anything - just finalize the request header part
        else
            $requestData .= "\r\n\r\n";


        return $requestData;

    }

    /**
     * @return null|string
     */
    public function getConfigurationName()
    {
        return $this->configurationName;
    }

    /**
     * @param null|string $configurationName
     */
    public function setConfigurationName($configurationName)
    {
        $this->configurationName = $configurationName;
    }

    /**
     * If some payload is defined method will return it
     * ---------------------------------------------------------------
     * @return string|null
     */
    public function getRequestPayload() {
        return $this->payload;
    }

    /**
     * Will tell is current Request was created for SSL HTTPS instance
     * ---------------------------------------------------------------
     * @return bool
     */
    public function isHTTPSSL() {
        return $this->protocol === self::HTTP_SSL;
    }

    /**
     * Will return numeric description of current request method
     * ---------------------------------------------------------------
     * @return int|null
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Will return current host from URL
     * ---------------------------------------------------------------
     * @return string|null
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Will return current port from URL
     * ---------------------------------------------------------------
     * @return int|null
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param bool $debugMode
     */
    public function setDebugMode($debugMode)
    {
        $this->debugMode = $debugMode;
    }

    /**
     * Will Parse URL and determine request parameters based on that
     * ---------------------------------------------------------------
     * @param  string $url
     * @throws ConfigurationException
     */
    private function _parseURL($url) {

        $pu = parse_url($url);

        if($pu !== false) {

            if(isset($pu["scheme"])) {

                if ($pu["scheme"] === "http") {

                    $this->port = 80;
                    $this->protocol = self::HTTP;

                } elseif ($pu["scheme"] === "https") {

                    $this->port = 443;
                    $this->protocol = self::HTTP_SSL;

                }

            }

            if(isset($pu["port"])) {

                if ((int) $pu["port"] > 0) {
                    $this->port = (int) $pu["port"];
                }

            }

            if(isset($pu["host"])) {

                $this->host = $pu["host"];

            }

            if(isset($pu["path"])) {

                $this->path = $pu["path"];

            }

            if(isset($pu["query"])) {

                $this->query = $this->_sanitizeQueryPart($pu["query"]);

            }

            return;

        }

        throw new ConfigurationException("URL passed to HttpRequest is malformed, please use proper URL for request",
            400);

    }

    private function _sanitizeQueryPart($queryString) {

        $qLen = strlen($queryString);

        if($qLen > 0) {

            if($queryString[$qLen-1] === '?')
                $queryString = substr($queryString,0,-1);

            return $queryString;

        }

        return null;

    }

}