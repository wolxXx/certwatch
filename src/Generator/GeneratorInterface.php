<?php

namespace Certwatch\Generator;

/**
 * Interface GeneratorInterface
 *
 * @package Certwatch\Generator
 */
interface GeneratorInterface
{
    /**
     * @param \Certwatch\Result[] $results
     *
     * @return \Certwatch\Generator\GeneratorInterface
     */
    public function setResults(array $results): GeneratorInterface;


    /**
     * @return \Certwatch\Generator\GeneratorInterface
     */
    public function generate(): GeneratorInterface;
}