<?php

namespace Norgul\Xmpp;

use Norgul\Xmpp\Exceptions\StreamError;
use Norgul\Xmpp\Xml\Stanzas\Auth;
use Norgul\Xmpp\Xml\Stanzas\Iq;
use Norgul\Xmpp\Xml\Stanzas\Message;
use Norgul\Xmpp\Xml\Stanzas\Presence;
use Norgul\Xmpp\Xml\Xml;

class XmppClient
{
    use Xml;

    protected Socket $socket;

    protected Options $options;

    public Auth $auth;

    public Iq $iq;

    public Presence $presence;

    public Message $message;

    public function __construct(Options $options)
    {
        $this->options = $options;
        $this->initDependencies();
    }

    protected function initDependencies(): void
    {
        $this->socket = $this->initSocket();
        $this->initStanzas($this->socket);
    }

    public function connect(): void
    {
        $this->openStream();
        $this->auth->authenticate();
        $this->iq->setResource($this->options->getResource());
        $this->sendInitialPresenceStanza();
    }

    public function send(string $xml): void
    {
        $this->socket->send($xml);
    }

    public function getResponse(): string
    {
        $this->socket->receive();
        $response = $this->socket->getResponseBuffer()->read();
        $finalResponse = $this->checkForErrors($response);

        return $finalResponse;
    }

    public function prettyPrint($response): void
    {
        if ($response) {
            $separator = "\n-------------\n";
            echo "{$separator} $response {$separator}";
        }
    }

    public function disconnect(): void
    {
        $this->socket->send(self::closeXmlStream());
        $this->socket->disconnect();
    }

    protected function openStream(): void
    {
        $openStreamXml = self::openXmlStream($this->options->getHost());
        $this->socket->send($openStreamXml);
    }

    protected function sendInitialPresenceStanza(): void
    {
        $this->socket->send('<presence/>');
    }

    protected function initStanzas($socket): void
    {
        $this->auth = new Auth($socket);
        $this->iq = new Iq($socket);
        $this->presence = new Presence($socket);
        $this->message = new Message($socket);
    }

    protected function initSocket(): Socket
    {
        return new Socket($this->options);
    }

    protected function checkForErrors(string $response): string
    {
        try {
            self::checkForUnrecoverableErrors($response);
        } catch (StreamError $e) {
            $this->options->getLogger()->info('RESPONSE '.__METHOD__.'::'.__LINE__, ['response' => $response]);
            $this->options->getLogger()->error(__METHOD__.'::'.__LINE__.' '.$e->getMessage());
            $this->reconnect();
            $response = '';
        }

        return $response;
    }

    protected function reconnect(): void
    {
        $this->disconnect();
        $this->initDependencies();
        $this->connect();
    }
}
