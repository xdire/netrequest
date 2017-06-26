<?php namespace Xdire\NetRequest\Models;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 1:16 PM
 */

use Xdire\NetRequest\HttpRequest;

class RequestPost extends Request
{

    function __construct($url = null, array $headers = null, $payload = null, $configName = null)
    {

        parent::__construct($url, Request::POST, $headers, $payload, $configName);
        $this->usingExecutor(HttpRequest::__whichDriver());

    }

    /**
     * Add override on Payload method which is available for POST requests
     *
     * @param $payload
     * @return $this
     */
    public function withPayload($payload)
    {
        parent::withPayload($payload);
        return $this;
    }

}