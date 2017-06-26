<?php namespace Xdire\NetRequest\Models\Runners;

/**
 * Author:
 * anton.repin <ar@xdire.io>
 * 1/26/17
 *
 *
 * Native Runner
 * -----------------------------------------------------------------
 * Native Runner is a module of NetRequest library.
 *
 * Allows NetRequest to make network HTTP protocol requests to
 * desired hosts.
 *
 * Supports both HTTP and HTTPS (SSL) connections
 *
 */

use Xdire\NetRequest\Interfaces\RequestRunner;
use Xdire\NetRequest\Models\Exceptions\ConfigurationException;
use Xdire\NetRequest\Models\Exceptions\ConnectionException;
use Xdire\NetRequest\Models\Executors\NativeCertificator;
use Xdire\NetRequest\Models\PreparedRequest;

class NativeRunner implements RequestRunner
{

    /** @var NativeCertificator | null */
    private $certificator = null;

    private $timeOut = 30;
    private $keepAlivePacketsUntilDisconnect = 99999;

    private $address = "";
    private $port = "";

    private $debugMode = false;
    private $debugLog = "";

    /**
     * @return string
     */
    public function getDebugLog()
    {
        return $this->debugLog;
    }

    /**
     * @param   PreparedRequest|null $req
     * @return  string
     * @throws  ConnectionException
     */
    public function connectAndGet(PreparedRequest $req = null) {

        if($req !== null) {

            if ($req->isDebugMode())
                $this->debugMode = true;

            $this->address = gethostbyname($req->getHost());
            $this->port = (int)$req->getPort();

            // If request is not an SSL request
            if (!$req->isHTTPSSL()) {

                return $this->_connectAndGetNoSSL($req);

            }
            // If request is SSL request
            else {

                // Check if certificator can be created
                if ($this->certificator === null)

                    $this->certificator = new NativeCertificator($req->getConfigurationName());

                // Check if you can create ceritficate or certificate is created
                try {

                    $this->certificator->checkDefaultCertificate();

                } catch (ConfigurationException $e) {

                    $this->_createCertificate();

                }

                return $this->_connectAndGetSSL($req);

            }

        }

        throw new ConnectionException("Request passed to NetRequest executor has wrong type", 400);

    }

    private function _connectAndGetNoSSL(PreparedRequest $req) {

        $this->debugLog .= "\n> Starting Connection to ".$req->getHost();

        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($sock === false)
            throw new ConnectionException("NetRequest executor failed to create new TCP connection, reason: "
                . socket_strerror(socket_last_error()), 500);

        if (socket_connect($sock, $this->address, $this->port) === false)
            throw new ConnectionException("NetRequest executor failed to connect to address: "
                .$this->address." at this port ".$this->port. ""
                ." possible reason: " .socket_strerror(socket_last_error($sock)), 403);

        $this->debugLog .= "\n> Successfully connected to: " . $req->getHost() . " at port: " . $req->getPort();

        /*
         *  Flush header data to server
         * -----------------------------
         */
        $data = $req->createAndGetRequestData();
        socket_write($sock, $data, mb_strlen($data));

        $this->debugLog .= "\n> Wrote header data:\n" . $data;

        /*
         *  Flush payload data to server
         * ------------------------------
         */
        if($payload = $req->getRequestPayload()) {
            socket_write($sock, $payload, mb_strlen($payload));
            $this->debugLog .= "\n> Wrote payload data:\n" . $payload;
        }

        $response = "";

        while ($r = socket_read($sock, 1024)) {

            $response .= $r;

        }

        $this->debugLog .= "\n> Read incoming data:\n" . $response;

        socket_close($sock);

        return $response;

    }

    private function _connectAndGetSSL(PreparedRequest $req) {

        $this->debugLog .= "\n> Starting SSL Connection to ".$req->getHost();

        /*
         * Create SSL context
         * ------------------------------------------------------------------------
         */
        $ctx = stream_context_create([
            "ssl"=> [
                "local_cert"        => $this->certificator->getDefaultCertificateFullPath(),
                "passphrase"        => $this->certificator->getDefaultCertificatePPRContents(),
                "allow_self_signed" => true,
                "verify_peer"       => true,
                "verify_peer_name"  => false,
                "SNI_enabled"       => true,
                "SNI_server_name"   => $req->getHost()
            ]
        ]);

        /*
         * Create socket connection in SSL context
         * ------------------------------------------------------------------------
         */
        if($sock = stream_socket_client(
            "ssl://".$req->getHost().':'.$this->port,
            $errorNumber,
            $errorMessage,
            $this->timeOut,
            STREAM_CLIENT_CONNECT,
            $ctx)) {

            $this->debugLog .= "\n> Successfully connected to: " . $req->getHost() . " at port: " . $req->getPort();

            /*
             *  Flush header data to server
             * -----------------------------
             */
            $data = $req->createAndGetRequestData();
            fwrite($sock, $data, strlen($data));

            $this->debugLog .= "\n> Wrote header data:\n" . $data;

            /*
             *  Flush payload data to server
             * ------------------------------
             */
            if ($payload = $req->getRequestPayload()) {

                fwrite($sock, $payload, mb_strlen($payload));
                $this->debugLog .= "\n> Wrote payload data:\n" . $payload;

            }

            $response = "";

            /*
             *  False read as well can be represented as a keep-alive packet,
             *  we'll be using tracking of how much of keep-alive requests
             *  we want to hold without actual data to be received
             */
            $falseReads = 0;
            /*
             *  Read until other side will drop a connection
             */
            while (!feof($sock)) {

                if (false !== ($imRsp = fgets($sock))) {

                    $response .= $imRsp;
                    $falseReads = 0;

                } elseif ($falseReads++ > $this->keepAlivePacketsUntilDisconnect)
                    break;

            }

            $this->debugLog .= "\n> Read incoming data:\n" . $response;

            fclose($sock);

            return $response;

        }

        throw new ConnectionException("NetRequest executor failed to create new SSL connection, reason: ["
            .$errorNumber."] ".$errorMessage, $errorNumber);

    }

    private function _createCertificate() {

        $this->certificator->createDefaultCertificate();

    }

}