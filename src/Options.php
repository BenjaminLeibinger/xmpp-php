<?php

namespace Norgul\Xmpp;

use Norgul\Xmpp\AuthTypes\Authenticable;
use Norgul\Xmpp\AuthTypes\Plain;
use Norgul\Xmpp\Loggers\Logger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class Options
{
    /**
     * Hostname of XMPP server.
     */
    protected string $host;
    /**
     * XMPP server port. Usually 5222.
     */
    protected int $port = 5222;
    /**
     * Protocol used for socket connection, defaults to TCP.
     */
    protected string $protocol = 'tcp';
    /**
     * Username to authenticate on XMPP server.
     */
    protected string $username;
    /**
     * Password to authenticate on XMPP server.
     */
    protected string $password;
    /**
     * XMPP resource.
     */
    protected string $resource;
    /**
     * Custom logger interface.
     */
    protected ?LoggerInterface $logger = null;
    /**
     * Use TLS if available.
     */
    protected bool $useTls = true;
    /**
     * Auth type (Authentication/AuthTypes/).
     */
    protected Authenticable $authType;

    public function getHost(): string
    {
        if (!$this->host) {
            $this->getLogger()->error(__METHOD__.'::'.__LINE__.
                ' No host found, please set the host variable');
            throw new InvalidArgumentException();
        }

        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = trim($host);

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getUsername(): string
    {
        if (!$this->username) {
            $this->getLogger()->error(__METHOD__.'::'.__LINE__.
                ' No username found, please set the username variable');
            throw new InvalidArgumentException();
        }

        return $this->username;
    }

    /**
     * Try to assign a resource if it exists. If bare JID is forwarded, this will default to your username.
     */
    public function setUsername(string $username): self
    {
        $usernameResource = explode('/', $username);

        if (count($usernameResource) > 1) {
            $this->setResource($usernameResource[1]);
            $username = $usernameResource[0];
        }

        $this->username = trim($username);

        return $this;
    }

    public function getPassword(): string
    {
        if (!$this->password) {
            $this->getLogger()->error(__METHOD__.'::'.__LINE__.
                ' No password found, please set the password variable');
            throw new InvalidArgumentException();
        }

        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getResource(): string
    {
        if (!$this->resource) {
            $this->resource = 'norgul_machine_'.time();
        }

        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = trim($resource);

        return $this;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function fullSocketAddress(): string
    {
        $protocol = $this->getProtocol();
        $host = $this->getHost();
        $port = $this->getPort();

        return "$protocol://$host:$port";
    }

    public function fullJid(): string
    {
        $username = $this->getUsername();
        $resource = $this->getResource();
        $host = $this->getHost();

        return "$username@$host/$resource";
    }

    public function bareJid(): string
    {
        $username = $this->getUsername();
        $host = $this->getHost();

        return "$username@$host";
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    public function setUseTls(bool $enable): self
    {
        $this->useTls = $enable;

        return $this;
    }

    public function usingTls(): bool
    {
        return $this->useTls;
    }

    public function getAuthType(): Authenticable
    {
        if (empty($this->authType)) {
            $this->setAuthType(new Plain($this));
        }

        return $this->authType;
    }

    public function setAuthType(Authenticable $authType): self
    {
        $this->authType = $authType;

        return $this;
    }
}
