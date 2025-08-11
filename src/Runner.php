<?php

namespace Certwatch;

class Runner
{
    /**
     * @var string[]
     */
    protected array $domains = [];

    /**
     * @var \Certwatch\Result[]
     */
    protected array                                          $results = [];

    protected string                                         $pathToDomains;

    protected ?\Symfony\Component\Console\Style\SymfonyStyle $io;


    public function __construct()
    {
        $this->pathToDomains = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'domains.txt';
        $this->reloadConfiguration();
    }


    public function reloadConfiguration(): static
    {
        if (false === file_exists(filename: $this->getPathToDomains())) {
            return $this;
        }
        $domainText    = file_get_contents(filename: $this->getPathToDomains());
        $domains       = explode(separator: PHP_EOL, string: $domainText);
        $this->results = [];
        foreach ($domains as $domain) {
            $domain = trim(string: $domain);
            if ('' === $domain) {
                continue;
            }
            $this->results[$domain] = (new Result())
                ->setDomain(domain: $domain)
            ;
        }
        uasort(array: $this->results, callback: function (Result $a, Result $b) {
            return $a->getDomain() >= $b->getDomain() ? 1 : -1;
        });

        return $this;
    }


    public function getPathToDomains(): string
    {
        return $this->pathToDomains;
    }


    public function setPathToDomains(string $pathToDomains): static
    {
        $this->pathToDomains = $pathToDomains;

        return $this;
    }


    public function run(): static
    {
        if (null !== $this->getIo()) {
            $this->getIo()->writeln(messages: 'scanning domains.');
            $this->getIo()->progressStart(max: count(value: $this->results));
        }
        foreach ($this->results as $result) {
            $this->checkDomain(result: $result);
            if (null !== $this->getIo()) {
                $this->getIo()->progressAdvance();
            }
        }
        if (null !== $this->getIo()) {
            $this->getIo()->progressFinish();
            $this->getIo()->writeln(messages: 'finished scanning domains.');
            $this->getIo()->writeln(messages: 'ready to generate the results.');
        }

        return $this;
    }


    public function getIo(): ?\Symfony\Component\Console\Style\SymfonyStyle
    {

        return isset($this->io) ?  $this->io : null;
    }


    public function setIo(?\Symfony\Component\Console\Style\SymfonyStyle $io): static
    {
        $this->io = $io;

        return $this;
    }


    /**
     * @return \Certwatch\Result[]
     */
    public function getResults(): array
    {
        return $this->results;
    }


    public function clearResults(): static
    {
        $this->results = [];

        return $this;
    }


    public function addResult(Result $result): static
    {
        $this->results[] = $result;

        return $this;
    }


    protected function checkDomain(Result $result): static
    {
        ob_start();
        try {
            $certificate = @\Spatie\SslCertificate\SslCertificate::download()
                                                                 ->withVerifyPeer(verifyPeer: false)
                                                                 ->withVerifyPeerName(verifyPeerName: true)
                                                                 ->setTimeout(timeOutInSeconds: 5)
                                                                 ->forHost(hostName: $result->getDomain())
            ;
            $result->setValid(valid: true);
        } catch (\Exception $exception) {
            $result
                ->setValid(valid: false)
                ->addError(error: $exception->getMessage())
            ;
        }
        ob_get_clean();
        if (false === $result->isValid()) {
            return $this;
        }
        $result
            ->setIssuer(issuer: $certificate->getIssuer())
            ->setValid(valid: $certificate->isValid())
            ->setValidFrom(validFrom: $certificate->validFromDate())
            ->setValidUntil(validUntil: $certificate->expirationDate())#; // returns an int
            ->setValidUntilDays(validUntilDays: (int)$certificate->expirationDate()->diffInDays(date: null, absolute: false) * -1)#$certificate->getSignatureAlgorithm(); // returns a string
        ;

        return $this;
    }
}
