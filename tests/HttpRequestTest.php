<?php

/**
 * Author:
 * anton.repin <ar@xdire.io>
 */

use PHPUnit\Framework\TestCase;
use Xdire\NetRequest\Models\Executors\NativeCertificator;
use Xdire\NetRequest\HttpRequest;

class HttpRequestTest extends TestCase
{

    public static $directory = null;

    /**
     * Prepare tests
     *
     * @throws Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        umask(0000);

        self::$directory = __DIR__."/test-directory";

        if(!file_exists(self::$directory))

            if(false === mkdir(self::$directory))

                throw new Exception(
                    "Wasn't able to create directory for test files at ".(self::$directory),
                    500);

    }

    /**
     *  Clean up after tests
     */
    public static function tearDownAfterClass()
    {

        parent::tearDownAfterClass();

        foreach (scandir(self::$directory) as $index => $file ) {

            if($file !== '.' && $file !== "..")
                unlink(self::$directory.'/'.$file);

        }

        rmdir(self::$directory);

    }

    /**
     *  Test default config creation
     */
    public function testDefaultConfig() {

        $config = HttpRequest::config()->setCertificateDirectory(self::$directory);

        $certModel = new NativeCertificator($config->getName());

        $certModel->createDefaultCertificate();

        $this->assertTrue(
            file_exists($config->getCertificateDirectory()."/".$config->getDefaultCertificateName().".pem"),
            "File: ".($config->getCertificateDirectory()."/".$config->getDefaultCertificateName().".pem").
            " can't be found");

        $this->assertTrue($certModel->checkDefaultCertificate());

        $this->assertTrue($certModel->getDefaultCertificateFullPath() ===
            ($config->getCertificateDirectory()."/".$config->getDefaultCertificateName().".pem"));

    }

    /**
     *  Test named config creation
     */
    public function testNamedConfig() {

        $config = HttpRequest::config("new-config")->setCertificateDirectory(self::$directory);

        $config->setDefaultCertificateName("defaulto");

        $certModel = new NativeCertificator($config->getName());

        $certModel->createDefaultCertificate();

        $this->assertTrue(file_exists(
            $config->getCertificateDirectory()."/".$config->getDefaultCertificateName().".pem"));

        $this->assertTrue($certModel->checkDefaultCertificate());

        $this->assertTrue($certModel->getDefaultCertificateFullPath() ===
            ($config->getCertificateDirectory()."/".$config->getDefaultCertificateName().".pem"));

    }

    /**
     *  Test GET Request
     */
    public function testGet() {

        $response = HttpRequest::get("http://google.com")->withDebug()->send();

        $this->assertTrue($response->getCode() > 100);

    }

    /**
     *  Test SSL GET Request
     */
    public function testSSLGet() {

        $response = HttpRequest::get("https://google.com")->withDebug()->send();

        $this->assertTrue($response->getCode() > 100);

    }

}
