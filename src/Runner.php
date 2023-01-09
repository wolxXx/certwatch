<?php

namespace Certwatch;

/**
 * Class Runner
 *
 * @package Certwatch
 */
class Runner
{
    /**
     * @var string[]
     */
    protected $domains = [];

    /**
     * @var \Certwatch\Result[]
     */
    protected $results = [];

    /**
     * @var string
     */
    protected $pathToDomains;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle | null
     */
    protected $io;


    /**
     * Runner constructor.
     */
    public function __construct()
    {
        $this->pathToDomains = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'domains.txt';
        $this->reloadConfiguration();
    }


    /**
     * @return $this
     */
    public function reloadConfiguration(): Runner
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
                ->setDomain($domain);
        }
        uasort($this->results, function (Result $a, Result $b) {
            return $a->getDomain() >= $b->getDomain();
        });

        return $this;
    }


    /**
     * @return string
     */
    public function getPathToDomains(): string
    {
        return $this->pathToDomains;
    }


    /**
     * @param string $pathToDomains
     *
     * @return Runner
     */
    public function setPathToDomains(string $pathToDomains): Runner
    {
        $this->pathToDomains = $pathToDomains;

        return $this;
    }


    /**
     * @return $this
     */
    public function run(): Runner
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


    /**
     * @return \Symfony\Component\Console\Style\SymfonyStyle|null
     */
    public function getIo(): ?\Symfony\Component\Console\Style\SymfonyStyle
    {
        return $this->io;
    }


    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle|null $io
     *
     * @return Runner
     */
    public function setIo(?\Symfony\Component\Console\Style\SymfonyStyle $io): Runner
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


    /**
     * @return $this
     */
    public function clearResults(): Runner
    {
        $this->results = [];

        return $this;
    }


    /**
     * @param \Certwatch\Result $result
     *
     * @return $this
     */
    public function addResult(Result $result): Runner
    {
        $this->results[] = $result;

        return $this;
    }


    /**
     * @param \Certwatch\Result $result
     *
     * @return $this
     */
    protected function checkDomain(Result $result): Runner
    {
        ob_start();
        try {
            $certificate = @\Spatie\SslCertificate\SslCertificate::download()
                                                                 ->withVerifyPeer(false)
                                                                 ->withVerifyPeerName(false)
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
            ->setValidUntilDays($certificate->expirationDate()->diffInDays(null, false) * -1)
            #$certificate->getSignatureAlgorithm(); // returns a string
        ;

        return $this;
    }
}
