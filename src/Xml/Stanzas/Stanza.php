<?php

namespace Norgul\Xmpp\Xml\Stanzas;

use Norgul\Xmpp\Socket;
use Norgul\Xmpp\Xml\Xml;

abstract class Stanza
{
    use Xml;

    protected $socket;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    protected function uniqueId(): string
    {
        return uniqid();
    }
}
