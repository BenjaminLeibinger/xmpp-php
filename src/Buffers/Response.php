<?php

namespace Norgul\Xmpp\Buffers;

class Response implements Buffer
{
    protected ?array $response = null;

    public function write(mixed $data): void
    {
        if ($data) {
            $this->response[] = $data;
        }
    }

    public function read(): string
    {
        $implodedResponse = $this->response ? implode('', $this->response) : '';
        $this->flush();

        return $implodedResponse;
    }

    protected function flush(): void
    {
        $this->response = null;
    }

    public function getCurrentBufferData(): ?array
    {
        return $this->response;
    }
}
