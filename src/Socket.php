<?php

namespace Norgul\Xmpp;

use Exception;
use Norgul\Xmpp\Buffers\Response;
use Norgul\Xmpp\Exceptions\DeadSocket;

class Socket
{
    public $connection;

    protected $responseBuffer;
    protected $options;

    /**
     * Period in microseconds for imposed timeout while doing socket_read().
     */
    protected $timeout = 150000;

    /**
     * Socket constructor.
     *
     * @throws DeadSocket
     */
    public function __construct(Options $options)
    {
        $this->responseBuffer = new Response();
        $this->connection = stream_socket_client($options->fullSocketAddress());

        if (!$this->isAlive($this->connection)) {
            throw new DeadSocket();
        }

        // stream_set_blocking($this->connection, true);
        stream_set_timeout($this->connection, 0, $this->timeout);
        $this->options = $options;
    }

    public function disconnect()
    {
        fclose($this->connection);
    }

    /**
     * Sending XML stanzas to open socket.
     *
     * @param $xml
     */
    public function send(string $xml)
    {
        try {
            fwrite($this->connection, $xml);
            $this->options->getLogger()->info(sprintf('REQUEST %s', $this->getMethodMessage()), ['xml' => $xml]);
            // $this->checkSocketStatus();
        } catch (Exception $e) {
            $this->options->getLogger()->error(sprintf('REQUEST %s fwrite() failed %s', $this->getMethodMessage(), $e->getMessage()));

            return;
        }

        $this->receive();
    }

    public function receive()
    {
        $response = '';
        while ($out = fgets($this->connection)) {
            $response .= $out;
        }

        if (!$response) {
            return;
        }

        $this->responseBuffer->write($response);
        $this->options->getLogger()->info(sprintf('RESPONSE %s', $this->getMethodMessage()), ['response' => $response]);
    }

    protected function isAlive($socket)
    {
        return false !== $socket;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getResponseBuffer(): Response
    {
        return $this->responseBuffer;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    protected function checkSocketStatus(): void
    {
        $status = socket_get_status($this->connection);

        // echo print_r($status);

        if ($status['eof']) {
            $message = sprintf('REQUEST %s ---Probably a broken pipe, restart connection', $this->getMethodMessage());
            $this->options->getLogger()->info($message, ['status' => $status]);
        }
    }

    protected function getMethodMessage(): string
    {
        return sprintf('%s::%s', __METHOD__, __LINE__);
    }
}
