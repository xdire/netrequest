<?php namespace Xdire\NetRequest\Models;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 12:15 PM
 */

use Xdire\NetRequest\HttpRequest;

class RequestGet extends Request
{

    function __construct($url = null, array $headers = null, $configName = null)
    {

        parent::__construct($url, Request::GET, $headers, null, $configName);
        $this->usingExecutor(HttpRequest::__whichDriver());

    }

}