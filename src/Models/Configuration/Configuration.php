<?php namespace Xdire\NetRequest\Models\Configuration;

/**
 * Author:
 * anton.repin <ar@xdire.io>
 * 6/25/17
 */

use Xdire\NetRequest\Models\Exceptions\ConfigurationException;

class Configuration
{

    /*  ------------------------------------------------------------------------------
     *
     *                               Static instance storage
     *
     *  ------------------------------------------------------------------------------
     */

    /** @var     Configuration[] */
    private static $configInstanceStorage = [];

    /**
     * Get storage with name or default storage
     *
     * @param   int|string $name
     * @return  Configuration
     */
    public static function get($name = null) {

        if($name === null)
            $name = "_d";

        if(!isset(self::$configInstanceStorage[$name]))
            self::$configInstanceStorage[$name] = new Configuration($name);

        $c = self::$configInstanceStorage[$name];

        return $c;

    }

    /*  ------------------------------------------------------------------------------
     *
     *                                  Instance properties
     *
     *  ------------------------------------------------------------------------------
     */

    /** @var string */
    private $name = "_d";

    /** @var string|null  */
    private $certificateStorageDirectory = null;

    /** @var string */
    private $useThisCertificateAsDefault = "default";

    /** @var string  */
    private $defaultEmail = "nobody@nobody.com";

    /** @var string  */
    private $defaultCountry = "US";

    /** @var string  */
    private $defaultState = "CA";

    /** @var string  */
    private $defaultLocality = "SCV";

    function __construct($name = null)
    {
        if($name !== null)
            $this->name = $name;
    }

    /**
     * Set new name for configuration (if not existing - copy will be created)
     *
     * @param $name
     * @return $this|Configuration
     * @throws ConfigurationException
     */
    public function setName($name) {

        if($this->name !== null)
            throw new ConfigurationException("Configuration name can be set only once", 400);

        $this->name = $name;
        /*
         *  Check if configuration existing, if not - create new and reset everywhere
         */
        if(!isset(self::$configInstanceStorage[$this->name])) {

            $c = clone($this);
            /*
             *  Reset previous instance linkage
             */
            $this->wrapperInstance = null;
            /*
             *  Assign configuration to storage
             */
            self::$configInstanceStorage[$c->getName()] = $c;

            return $c;

        }

        return $this;

    }

    /**
     * @return string
     */
    public function getName() {

        return $this->name;

    }

    /**
     * @param   $path
     * @return  $this
     * @throws  ConfigurationException
     */
    public function setCertificateDirectory($path){

        if(file_exists($path)) {
            $this->certificateStorageDirectory = $path;
            return $this;
        }

        throw new ConfigurationException(
            "Can't set directory ".$path." as certificate storage directory, presented directory doesn't exist",
            400);

    }

    /**
     * @return null|string
     */
    public function getCertificateDirectory() {

        if($this->certificateStorageDirectory !== null)
            return $this->certificateStorageDirectory;

        return __DIR__."/../../Certificates";

    }

    /**
     * @param   string $name
     * @return  $this
     */
    public function setDefaultCertificateName($name) {

        $this->useThisCertificateAsDefault = $name;
        return $this;

    }

    /**
     * @return string
     */
    public function getDefaultCertificateName() {

        return $this->useThisCertificateAsDefault;

    }

    /**
     * @return string
     */
    public function getDefaultEmail()
    {
        return $this->defaultEmail;
    }

    /**
     * @param string $defaultEmail
     */
    public function setDefaultEmail($defaultEmail)
    {
        $this->defaultEmail = $defaultEmail;
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->defaultCountry;
    }

    /**
     * @param string $defaultCountry
     */
    public function setDefaultCountry($defaultCountry)
    {
        $this->defaultCountry = $defaultCountry;
    }

    /**
     * @return string
     */
    public function getDefaultState()
    {
        return $this->defaultState;
    }

    /**
     * @param string $defaultState
     */
    public function setDefaultState($defaultState)
    {
        $this->defaultState = $defaultState;
    }

    /**
     * @return string
     */
    public function getDefaultLocality()
    {
        return $this->defaultLocality;
    }

    /**
     * @param string $defaultLocality
     */
    public function setDefaultLocality($defaultLocality)
    {
        $this->defaultLocality = $defaultLocality;
    }

}