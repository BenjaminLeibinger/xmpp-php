<?php

namespace Norgul\Xmpp\Buffers;

interface Buffer
{
    /**
     * Write to buffer (add to array of values).
     */
    public function write(mixed $data): void;

    /**
     * Read from buffer and delete the data.
     */
    public function read(): string;
}
