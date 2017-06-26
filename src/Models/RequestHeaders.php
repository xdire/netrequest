<?php namespace Xdire\NetRequest\Models;

/**
 * anton.repin <ar@xdire.io>
 * Date: 1/26/17
 * Time: 1:33 PM
 */

class RequestHeaders
{

    /**
     * @var string[]
     */
    private $headers = [];

    function __construct(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value) {

        $this->headers[$name] = (string) $value;

    }

    /**
     * @param string $name
     * @param string $value
     */
    public function replaceHeader($name, $value) {

        $this->headers[$name] = $value;

    }

    /**
     * @param string $name
     */
    public function removeHeader($name) {

        if(array_key_exists($name,$this->headers)) {
            $this->headers[$name] = null;
        }

    }

    /**
     * @param string $name
     * @return null|string
     */
    public function getHeader($name) {

        if(isset($this->headers[$name]))
            return $this->headers[$name];
        return null;

    }

    /**
     * @return string[]
     */
    public function getHeaders() {

        $a = [];
        foreach ($this->headers as $name => $value) {
            if($value !== null)
                $a[$name] = $value;
        }
        $this->headers = $a;
        return $this->headers;

    }

}