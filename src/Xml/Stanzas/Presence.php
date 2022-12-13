<?php

namespace Norgul\Xmpp\Xml\Stanzas;

class Presence extends Stanza
{
    public const PRIORITY_UPPER_BOUND = 127;
    public const PRIORITY_LOWER_BOUND = -128;

    public function subscribe(string $to): void
    {
        $this->setPresence($to, 'subscribe');
    }

    public function unsubscribe(string $from): void
    {
        $this->setPresence($from, 'unsubscribe');
    }

    public function acceptSubscription(string $from): void
    {
        $this->setPresence($from, 'subscribed');
    }

    public function declineSubscription(string $from): void
    {
        $this->setPresence($from, 'unsubscribed');
    }

    protected function setPresence(string $to, string $type = 'subscribe'): void
    {
        $xml = "<presence from='{$this->socket->getOptions()->bareJid()}' to='{$to}' type='{$type}'/>";
        $this->socket->send($xml);
    }

    public function setStatus(string $status): void
    {
        $xml = "<presence><show>{$status}</show></presence>";
        $this->socket->send($xml);
    }

    /**
     * Set priority to current resource by default, or optional other resource tied to the
     * current username.
     */
    public function setPriority(int $value, string $forResource = null): void
    {
        $from = self::quote($this->socket->getOptions()->fullJid());

        if ($forResource) {
            $from = $this->socket->getOptions()->getUsername()."/$forResource";
        }

        $priority = "<priority>{$this->limitPriority($value)}</priority>";
        $xml = "<presence from='{$from}'>{$priority}</presence>";

        $this->socket->send($xml);
    }

    protected function limitPriority(int $value): int
    {
        if ($value > self::PRIORITY_UPPER_BOUND) {
            return self::PRIORITY_UPPER_BOUND;
        } elseif ($value < self::PRIORITY_LOWER_BOUND) {
            return self::PRIORITY_LOWER_BOUND;
        }

        return $value;
    }
}
