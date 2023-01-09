<?php

namespace Certwatch\Generator;

/**
 * Class GeneratorAbstract
 *
 * @package Certwatch\Generator
 */
abstract class GeneratorAbstract implements GeneratorInterface
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle | null
     */
    protected $io;

    /**
     * @var \Certwatch\Result[]
     */
    protected $results;


    /**
     * @return \Certwatch\Result[]
     */
    public function getResults(): array
    {
        return $this->results;
    }


    /**
     * @param \Certwatch\Result[] $results
     *
     * @return $this
     */
    public function setResults(array $results): GeneratorInterface
    {
        $this->results = $results;

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
     * @return $this|\Certwatch\Generator\GeneratorInterface
     */
    public function setIo(?\Symfony\Component\Console\Style\SymfonyStyle $io): GeneratorInterface
    {
        $this->io = $io;

        return $this;
    }
}