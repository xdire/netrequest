<?php namespace Xdire\NetRequest\Models;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 8:47 PM
 */

class Response
{

    /** @var string */
    private $protocol;
    /** @var string */
    private $protocolRaw;
    /** @var string */
    private $protocolVer;

    /** @var int */
    private $statusCode = 0;
    /** @var string */
    private $statusExtra = "";

    /** @var int */
    private $contentLength;
    /** @var int */
    private $contentType;

    /** @var string */
    private $date = null;

    /** @var * */
    private $response = null;
    /** @var string */
    private $responseType = null;

    /** @var int */
    private $errorCode = 0;
    /** @var string */
    private $errorMessage = "";

    private $debugLog = "";

    function __construct(PreparedResponse $response) {

        $this->statusCode = $response->statusCode;
        $this->statusExtra = $response->statusExtra;

        $this->contentLength = $response->contentLength;
        $this->contentType = $response->contentType;

        $this->response = $response->response;

        $this->errorCode = $response->error;
        $this->errorMessage = $response->errorMessage;

        $this->debugLog = $response->debugLog;

    }

    /**
     * @return string
     */
    public function getDate(){
        return $this->date;
    }

    /**
     * @return int
     */

    public function getCode(){
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getCodeExtra(){
        return $this->statusExtra;
    }

    /**
     * @return int
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * @return int
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return null | mixed
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getResponseType(){
        return $this->responseType;
    }

    /**
     * @return int
     */
    public function getErrorCode(){
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(){
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getDebugLog()
    {
        return $this->debugLog;
    }

}