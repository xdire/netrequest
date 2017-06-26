<?php namespace Xdire\NetRequest\Models;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 9:46 PM
 */

class PreparedResponse
{

    /** @var string */
    public $protocol;
    /** @var string */
    public $protocolRaw;
    /** @var string */
    public $protocolVer;
    /** @var int */
    public $statusCode = 0;
    /** @var string */
    public $statusExtra = "";

    /** @var array */
    public $headers = [];

    /** @var string */
    public $contentType = "";
    /** @var int */
    public $contentLength = 0;

    /** @var string */
    public $date = null;

    /** @var * */
    public $response = null;
    /** @var string */
    public $responseType = null;
    /** @var bool */
    public $responseExist = false;

    /** @var int */
    public $error = 0;
    /** @var string */
    public $errorMessage = "";

    public $responseRaw = null;

    public $debugLog = "";

    function __construct($responseString)
    {
        $this->responseRaw = $responseString;
    }

    public function getResponse() {

        $this->_parseResponse($this->responseRaw);

        $r = new Response($this);
        return $r;

    }

    private function _parseResponse($response) {

        $r = explode("\n",$response);
        $startContent = false;
        $i = 0;

        foreach($r as $p) {

            if($startContent) {

                $this->_parseContent($p);

            }
            else
            {
                if($i == 0){
                    $this->_parseStatus($p);
                    if($this->statusCode==100) continue;
                } else {
                    $this->_parseHeader($p);
                }
            }

            if(strlen($p) < 2 && $i>1){
                $startContent = true;
            }

            $i++;

        }

        if($startContent && $this->response != null){
            $this->responseExist = true;
        }

        if($i > 0) {
            $return = true;
        }

    }

    private function _parseHeader($header) {

        $header = explode(":", $header);
        $name = null;

        if(isset($header[0])) {

            $headerName = $header[0];
            $value = isset($header[1]) ? trim($header[1]) : "";

            switch($headerName) {

                case "Content-Length":
                    $this->contentLength = (int) $value;
                    break;
                case "Content-Type":
                    $this->contentType = $value;
                    break;
                default: break;

            }

            $this->headers[$headerName] = $value;

        }

    }

    private function _parseStatus($status) {

        $sh = explode(" ", $status);
        $i=0;

        foreach($sh as $st) {

            if($i==0) {

                $http = explode("/", $st);

                if(strpos($http[0], 'HTTP') !== false) {

                    $this->protocol = 'HTTP';
                    $this->protocolRaw = $http[0];

                } else {

                    $this->protocol = 'undefined';
                    $this->protocolRaw = $http[0];

                }

                if(isset($http[1])) {
                    $this->protocolVer = $http[1];
                }

            } elseif($i == 1) {

                $stat = (int)$st;
                if($stat > 0){
                    $this->statusCode = $stat;
                }

            } else
                $this->statusExtra .= " ".$st;

            $i++;

        }

        $this->statusExtra = trim($this->statusExtra);

    }

    private function _parseContent($c) {

        $this->response .= $c;

    }

}