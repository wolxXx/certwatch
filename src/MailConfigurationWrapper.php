<?php

declare(strict_types = 1);

namespace Certwatch;

final class MailConfigurationWrapper
{
    protected string $pathToConfigurationFile;

    protected string $username;

    protected string $password;

    protected string $server;

    protected string $encryption;

    protected int    $port;

    protected string $from;

    protected string $fromName;

    /**
     * @var string[]
     */
    protected array $to = [];

    /**
     * @var string[]
     */
    protected array $bcc = [];

    /**
     * @var string[]
     */
    protected array $cc = [];


    public final function __construct()
    {
        $this->setPathToConfigurationFile(pathToConfigurationFile: __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mail-config.php');
    }


    public function init(): self
    {

        if (false === file_exists(filename: $this->getPathToConfigurationFile())) {
            throw new \InvalidArgumentException(message: 'could not find mail config under "' . $this->getPathToConfigurationFile() . '"!');
        }
        $mailConfiguration = require $this->getPathToConfigurationFile();
        if (false === is_array(value: $mailConfiguration)) {
            throw new \InvalidArgumentException(message: 'content in config file under "' . $this->getPathToConfigurationFile() . '" is not an array!');
        }
        $expectedKeys = [
            'username',
            'password',
            'server',
            'encryption',
            'port',
            'from',
            'to',
        ];
        $missingKeys  = [];
        foreach ($expectedKeys as $expectedKey) {
            if (false === array_key_exists(key: $expectedKey, array: $mailConfiguration)) {
                $missingKeys[] = $expectedKey;
            }
        }
        if (0 !== count(value: $missingKeys)) {
            throw new \InvalidArgumentException(message: 'content in config file under "' . $this->getPathToConfigurationFile() . '" is missing the keys ' . implode(separator: ', ',
                                                                                                                                                                     array    : $missingKeys));
        }
        $this
            ->setUsername(username: $mailConfiguration['username'])
            ->setPassword(password: $mailConfiguration['password'])
            ->setServer(server: $mailConfiguration['server'])
            ->setEncryption(encryption: $mailConfiguration['encryption'])
            ->setPort(port: (int)$mailConfiguration['port'])
            ->setFrom(from: $mailConfiguration['from'])
            ->setTo(to: (array)$mailConfiguration['to'])
        ;
        if (true === array_key_exists(key: 'fromName', array: $mailConfiguration)) {
            $this->setFromName(fromName: $mailConfiguration['fromName']);
        }
        if (true === array_key_exists(key: 'bcc', array: $mailConfiguration)) {
            $this->setBcc(bcc: (array)$mailConfiguration['bcc']);
        }
        if (true === array_key_exists(key: 'cc', array: $mailConfiguration)) {
            $this->setCc(cc: (array)$mailConfiguration['cc']);
        }

        return $this;
    }


    public function getPathToConfigurationFile(): string
    {
        return $this->pathToConfigurationFile;
    }

    public function setPathToConfigurationFile(string $pathToConfigurationFile): static
    {
        $this->pathToConfigurationFile = $pathToConfigurationFile;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }


    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }


    public function getPassword(): string
    {
        return $this->password;
    }


    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }


    public function getServer(): string
    {
        return $this->server;
    }


    public function setServer(string $server): static
    {
        $this->server = $server;

        return $this;
    }


    public function getEncryption(): string
    {
        return $this->encryption;
    }


    public function setEncryption(string $encryption): static
    {
        $this->encryption = $encryption;

        return $this;
    }


    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): static
    {
        $this->port = $port;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }


    public function setFrom(string $from): static
    {
        $this->from = $from;

        return $this;
    }


    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): static
    {
        $this->fromName = $fromName;

        return $this;
    }


    /**
     * @return string|string[]
     */
    public function getTo(): array|string
    {
        return $this->to;
    }


    /**
     * @param string|string[] $to
     */
    public function setTo(array|string $to): static
    {
        $this->to = (array)$to;

        return $this;
    }


    /**
     * @return string|string[]
     */
    public function getBcc()
    {
        return $this->bcc;
    }


    /**
     * @param string|string[] $bcc
     */
    public function setBcc(array|string $bcc): static
    {
        $this->bcc = (array)$bcc;

        return $this;
    }


    /**
     * @return string|string[]
     */
    public function getCc(): array|string
    {
        return $this->cc;
    }


    /**
     * @param string|string[] $cc
     */
    public function setCc(array|string $cc): static
    {
        $this->cc = (array)$cc;

        return $this;
    }
}