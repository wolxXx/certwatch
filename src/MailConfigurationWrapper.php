<?php

namespace Certwatch;

/**
 * Class MailConfigurationWrapper
 *
 * @package Certwatch
 */
class MailConfigurationWrapper
{
    /**
     * @var string
     */
    protected $pathToConfigurationFile;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $encryption;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var string | string[]
     */
    protected $to;

    /**
     * @var string | string[]
     */
    protected $bcc;

    /**
     * @var string | string[]
     */
    protected $cc;


    /**
     * MailConfigurationWrapper constructor.
     */
    public function __construct()
    {
        $this->setPathToConfigurationFile(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mail-config.php');
    }


    /**
     * @return $this
     */
    public function init(): MailConfigurationWrapper
    {

        if (false === file_exists($this->getPathToConfigurationFile())) {
            throw new \InvalidArgumentException('could not find mail config under "' . $this->getPathToConfigurationFile() . '"!');
        }
        $mailConfiguration = require $this->getPathToConfigurationFile();
        if (false === is_array($mailConfiguration)) {
            throw new \InvalidArgumentException('content in config file under "' . $this->getPathToConfigurationFile() . '" is not an array!');
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
            if (false === array_key_exists($expectedKey, $mailConfiguration)) {
                $missingKeys[] = $expectedKey;
            }
        }
        if (0 !== sizeof($missingKeys)) {
            throw new \InvalidArgumentException('content in config file under "' . $this->getPathToConfigurationFile() . '" is missing the keys ' . implode(', ', $missingKeys));
        }
        $this
            ->setUsername($mailConfiguration['username'])
            ->setPassword($mailConfiguration['password'])
            ->setServer($mailConfiguration['server'])
            ->setEncryption($mailConfiguration['encryption'])
            ->setPort((int) $mailConfiguration['port'])
            ->setFrom($mailConfiguration['from'])
            ->setTo((array) $mailConfiguration['to'])
        ;
        if (true === array_key_exists('fromName', $mailConfiguration)) {
            $this->setFromName($mailConfiguration['fromName']);
        }
        if (true === array_key_exists('bcc', $mailConfiguration)) {
            $this->setBcc((array) $mailConfiguration['bcc']);
        }
        if (true === array_key_exists('cc', $mailConfiguration)) {
            $this->setCc((array) $mailConfiguration['cc']);
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getPathToConfigurationFile(): string
    {
        return $this->pathToConfigurationFile;
    }


    /**
     * @param string $pathToConfigurationFile
     *
     * @return MailConfigurationWrapper
     */
    public function setPathToConfigurationFile(string $pathToConfigurationFile): MailConfigurationWrapper
    {
        $this->pathToConfigurationFile = $pathToConfigurationFile;

        return $this;
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }


    /**
     * @param string $username
     *
     * @return MailConfigurationWrapper
     */
    public function setUsername(string $username): MailConfigurationWrapper
    {
        $this->username = $username;

        return $this;
    }


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    /**
     * @param string $password
     *
     * @return MailConfigurationWrapper
     */
    public function setPassword(string $password): MailConfigurationWrapper
    {
        $this->password = $password;

        return $this;
    }


    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }


    /**
     * @param string $server
     *
     * @return MailConfigurationWrapper
     */
    public function setServer(string $server): MailConfigurationWrapper
    {
        $this->server = $server;

        return $this;
    }


    /**
     * @return string
     */
    public function getEncryption(): string
    {
        return $this->encryption;
    }


    /**
     * @param string $encryption
     *
     * @return MailConfigurationWrapper
     */
    public function setEncryption(string $encryption): MailConfigurationWrapper
    {
        $this->encryption = $encryption;

        return $this;
    }


    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }


    /**
     * @param int $port
     *
     * @return MailConfigurationWrapper
     */
    public function setPort(int $port): MailConfigurationWrapper
    {
        $this->port = $port;

        return $this;
    }


    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }


    /**
     * @param string $from
     *
     * @return MailConfigurationWrapper
     */
    public function setFrom(string $from): MailConfigurationWrapper
    {
        $this->from = $from;

        return $this;
    }


    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }


    /**
     * @param string $fromName
     *
     * @return MailConfigurationWrapper
     */
    public function setFromName(string $fromName): MailConfigurationWrapper
    {
        $this->fromName = $fromName;

        return $this;
    }


    /**
     * @return string|string[]
     */
    public function getTo()
    {
        return $this->to;
    }


    /**
     * @param string|string[] $to
     *
     * @return MailConfigurationWrapper
     */
    public function setTo($to)
    {
        $this->to = $to;

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
     *
     * @return MailConfigurationWrapper
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }


    /**
     * @return string|string[]
     */
    public function getCc()
    {
        return $this->cc;
    }


    /**
     * @param string|string[] $cc
     *
     * @return MailConfigurationWrapper
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }
}