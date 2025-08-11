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
        if (false === file_exists($this->getPathToDomains())) {
            return $this;
        }
        $domainText    = file_get_contents($this->getPathToDomains());
        $domains       = explode(PHP_EOL, $domainText);
        $this->results = [];
        foreach ($domains as $domain) {
            $domain = trim($domain);
            if ('' === $domain) {
                continue;
            }
            $this->results[$domain] = (new Result())
                ->setDomain($domain)
            ;
        }
        uasort($this->results, function (Result $a, Result $b) {
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
            $this->getIo()->writeln('scanning domains.');
            $this->getIo()->progressStart(sizeof($this->results));
        }
        foreach ($this->results as $result) {
            $this->checkDomain($result);
            if (null !== $this->getIo()) {
                $this->getIo()->progressAdvance();
            }
        }
        if (null !== $this->getIo()) {
            $this->getIo()->progressFinish();
            $this->getIo()->writeln('finished scanning domains.');
            $this->getIo()->writeln('ready to generate the results.');
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
                                                                 ->withVerifyPeer(false)
                                                                 ->withVerifyPeerName(true)
                                                                 ->setTimeout(5)
                                                                 ->forHost($result->getDomain())
            ;
            $result->setValid(true);
        } catch (\Exception $exception) {
            $result
                ->setValid(false)
                ->addError($exception->getMessage())
            ;
        }
        ob_get_clean();
        if (false === $result->isValid()) {
            return $this;
        }
        $result
            ->setIssuer($certificate->getIssuer())
            ->setValid($certificate->isValid())
            ->setValidFrom($certificate->validFromDate())
            ->setValidUntil($certificate->expirationDate())#; // returns an int
            ->setValidUntilDays((int)$certificate->expirationDate()->diffInDays(null, false) * -1)#$certificate->getSignatureAlgorithm(); // returns a string
        ;

        return $this;
    }
}
