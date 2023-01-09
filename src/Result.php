<?php

namespace Certwatch;

/**
 * Class Result
 *
 * @package Certwatch
 */
class Result
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var \DateTime | null
     */
    protected $validUntil;

    /**
     * @var int
     */
    protected $validUntilDays = -1;

    /**
     * @var \DateTime | null
     */
    protected $validFrom;

    /**
     * @var string | null
     */
    protected $issuer;

    /**
     * @var bool
     */
    protected $valid = false;

    /**
     * @var string[]
     */
    protected $errors;


    /**
     * Result constructor.
     */
    public function __construct()
    {
        $this->errors = [];
    }


    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }


    /**
     * @param string $domain
     *
     * @return Result
     */
    public function setDomain(string $domain): Result
    {
        $this->domain = $domain;

        return $this;
    }


    /**
     * @return \DateTime|null
     */
    public function getValidUntil(): ?\DateTime
    {
        return $this->validUntil;
    }


    /**
     * @param \DateTime|null $validUntil
     *
     * @return Result
     */
    public function setValidUntil(?\DateTime $validUntil): Result
    {
        $this->validUntil = $validUntil;

        return $this;
    }


    /**
     * @return \DateTime|null
     */
    public function getValidFrom(): ?\DateTime
    {
        return $this->validFrom;
    }


    /**
     * @param \DateTime|null $validFrom
     *
     * @return Result
     */
    public function setValidFrom(?\DateTime $validFrom): Result
    {
        $this->validFrom = $validFrom;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }


    /**
     * @param string|null $issuer
     *
     * @return Result
     */
    public function setIssuer(?string $issuer): Result
    {
        $this->issuer = $issuer;

        return $this;
    }


    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }


    /**
     * @param bool $valid
     *
     * @return Result
     */
    public function setValid(bool $valid): Result
    {
        $this->valid = $valid;

        return $this;
    }


    /**
     * @param string $error
     *
     * @return Result
     */
    public function addError(string $error): Result
    {
        $this->errors[] = $error;

        return $this;
    }


    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }


    /**
     * @return int
     */
    public function getValidUntilDays(): int
    {
        return $this->validUntilDays;
    }


    /**
     * @param int $validUntilDays
     *
     * @return Result
     */
    public function setValidUntilDays(int $validUntilDays): Result
    {
        $this->validUntilDays = $validUntilDays;

        return $this;
    }
}