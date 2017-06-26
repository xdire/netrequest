<?php namespace Xdire\NetRequest\Interfaces;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 3:32 PM
 */

use Xdire\NetRequest\Models\Exceptions\ConfigurationException;
use Xdire\NetRequest\Models\Exceptions\ConnectionException;
use Xdire\NetRequest\Models\PreparedRequest;


interface RequestRunner
{

    /**
     * @param   PreparedRequest     $url
     * @return  string
     * @throws  ConnectionException
     * @throws  ConfigurationException
     */
    public function connectAndGet(PreparedRequest $url = null);

    /**
     * @return string
     */
    public function getDebugLog();

}