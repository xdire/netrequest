<?php namespace Xdire\NetRequest\Models\Executors;

/**
 * Author:
 * anton.repin <ar@xdire.io>
 * 1/26/17
 *
 * Native Certificator
 * ----------------------------------------------------------------------------------
 * Require OPENSSL extension to be installed in the OS environment and PHP to be
 * compiled with openssl library module.
 *
 * Module of NetRequest. will create and manage individual self-issued certificates
 * which can be used for SSL connections or other type of Sign requests by NetRequest.
 *
 * Certificator allows NetRequest library to not to depend on standard set of OS
 * certificates which can be inaccessible or accidentally deleted
 */

use Xdire\NetRequest\Models\Configuration\Configuration;
use Xdire\NetRequest\Models\Exceptions\ConfigurationException;

class NativeCertificator
{

    private $useConfiguration = null;

    private $certificateStorage = null;

    private $defaultCertificateName = null;

    private $certificateIssueTimeFrame = 730;

    function __construct($configurationName = null)
    {

        if(!function_exists("openssl_pkey_new"))
            throw new ConfigurationException("NetRequest SSL capabilities is limited by absence of SSL capabilities".
                " in your version of PHP, please add openssl library for PHP".
                " and you will be able to use NetRequest for SSL connections", 400);

        $this->useConfiguration = Configuration::get($configurationName);

        $this->certificateStorage = $this->useConfiguration->getCertificateDirectory();

    }

    public function checkDefaultCertificate() {

        if(file_exists($this->getDefaultCertificateFullPath())) {
            return true;
        }

        throw new ConfigurationException("Default certificate doesn't exist, please create one for SSL capabilities",
            400);

    }

    public function checkCertificateWithName($name) {

        if(file_exists($this->getNamedCertificateFullPath($name))) {
            return true;
        }

        throw new ConfigurationException("Named certificate ".$name." doesn't exist, ".
            "please create one for SSL capabilities",
            400);

    }

    public function getDefaultCertificateFullPath() {

        if($this->certificateStorage === null)

            return __FILE__."../../Certificates/".$this->useConfiguration->getDefaultCertificateName().".pem";

        else

            return $this->certificateStorage."/".$this->useConfiguration->getDefaultCertificateName().".pem";

    }

    public function getDefaultCertificatePPRContents() {

        if($this->certificateStorage === null)

            return file_get_contents(
                __FILE__."../../Certificates/".$this->useConfiguration->getDefaultCertificateName().".ppr");

        else

            return file_get_contents(
                $this->certificateStorage."/".$this->useConfiguration->getDefaultCertificateName().".ppr");

    }

    public function getNamedCertificateFullPath($name) {

        if($this->certificateStorage === null)

            return __FILE__."../../Certificates/".$name.".pem";

        else

            return $this->certificateStorage."/".$name.".pem";

    }

    public function createDefaultCertificate() {

        $this->createCertificate(
            $this->useConfiguration->getDefaultCertificateName(),
            $this->generatePasspharse(),
            $this->useConfiguration->getDefaultEmail(),
            $this->useConfiguration->getDefaultCountry(),
            $this->useConfiguration->getDefaultState(),
            $this->useConfiguration->getDefaultLocality());

    }

    public function createNamedCertificate($name,
                                           $passPhrase = null,
                                           $email,
                                           $countryName = null,
                                           $stateOrProvinceName = null,
                                           $localityName = null,
                                           $organizationName = null,
                                           $organizationUnitName = null,
                                           $commonName = null) {

        if($passPhrase === null)
            $passPhrase = $this->generatePasspharse();

        $this->createCertificate(
            $name,
            $passPhrase,
            $email,
            $countryName,
            $stateOrProvinceName,
            $localityName,
            $organizationName,
            $organizationUnitName,
            $commonName);

    }

    /**
     * Will create and save certificate in a NetRequest library
     * Certificates folder.
     *
     * @param string $name
     * @param string $passPhrase
     * @param        $email
     * @param null   $countryName
     * @param null   $stateOrProvinceName
     * @param null   $localityName
     * @param null   $organizationName
     * @param null   $organizationUnitName
     * @param null   $commonName
     * @throws ConfigurationException
     */
    private function createCertificate(
        $name = "default",
        $passPhrase = null,
        $email,
        $countryName = null,
        $stateOrProvinceName = null,
        $localityName = null,
        $organizationName = null,
        $organizationUnitName = null,
        $commonName = null) {

        if($name === null)
            throw new ConfigurationException("For generate Certificate you must provide certificate name", 400);

        if ($passPhrase === null)
            throw new ConfigurationException("For generate Certificate you must provide pass-phrase", 400);

        $dn = ["emailAddress" => $email];

        if($countryName)
            $dn["countryName"] = $countryName;
        if($stateOrProvinceName)
            $dn["stateOrProvinceName"] = $stateOrProvinceName;
        if($localityName)
            $dn["localityName"] = $localityName;
        if($organizationName)
            $dn["organizationName"] = $organizationName;
        if($organizationUnitName)
            $dn["organizationalUnitName"] = $organizationUnitName;
        if($commonName)
            $dn["commonName"] = $commonName;

        $privateKey = openssl_pkey_new();

        $csr = openssl_csr_new($dn, $privateKey);

        $certificate = openssl_csr_sign($csr, null, $privateKey, $this->certificateIssueTimeFrame);


        $pemX509 = "";
        $pemPrivateKey = "";

        openssl_x509_export($certificate, $pemX509);
        openssl_pkey_export($privateKey, $pemPrivateKey, $passPhrase);

        $pemKey = $pemX509.$pemPrivateKey;

        $pemPath = $this->certificateStorage."/".$name.".pem";
        $pprPath = $this->certificateStorage."/".$name.".ppr";

        if(false === file_put_contents($pemPath, $pemKey))
            throw new ConfigurationException("Cannot create ceritificate file: ".$pemPath.
                ", check this folder exists and have appropriate permissions");

        if(false === file_put_contents($pprPath, $passPhrase))
            throw new ConfigurationException("Cannot create ceritificate file ppr file: ".$pprPath.
                ", check this folder exists and have appropriate permissions");

    }

    /**
     * Will return string with randomly generated pass-phrase
     *
     * @return string
     */
    public function generatePasspharse() {

        return md5(microtime().((string)rand(9999,99999)));

    }

}