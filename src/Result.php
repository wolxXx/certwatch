<?php

declare(strict_types = 1);

namespace Certwatch;


final class Result
{
    protected string     $domain;

    protected ?\DateTime $validUntil     = null;

    protected int        $validUntilDays = -1;

    protected ?\DateTime $validFrom      = null;

    protected ?string    $issuer         = null;

    protected bool       $valid          = false;

    /**
     * @var string[]
     */
    protected array $errors = [];


    public final function __construct()
    {
        $this->errors = [];
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }


    public function getValidUntil(): ?\DateTime
    {
        return $this->validUntil;
    }


    public function setValidUntil(?\DateTime $validUntil): self
    {
        $this->validUntil = $validUntil;

        return $this;
    }


    public function getValidFrom(): ?\DateTime
    {
        return $this->validFrom;
    }


    public function setValidFrom(?\DateTime $validFrom): self
    {
        $this->validFrom = $validFrom;

        return $this;
    }


    public function getIssuer(): ?string
    {
        return $this->issuer;
    }


    public function setIssuer(?string $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }


    public function isValid(): bool
    {
        return $this->valid;
    }


    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }


    public function addError(string $error): self
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


    public function getValidUntilDays(): int
    {
        return $this->validUntilDays;
    }


    public function setValidUntilDays(int $validUntilDays): self
    {
        $this->validUntilDays = $validUntilDays;

        return $this;
    }
}