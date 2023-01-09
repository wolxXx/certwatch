<?php

namespace Certwatch\Test;

/**
 * Class TestBase
 *
 * @package Certwatch\Test
 */
abstract class TestBase extends \PHPUnit\Framework\TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
    }


    /**
     * @return array
     */
    public function __sleep()
    {
        return [];
    }


    /**
     * @return \Faker\Generator
     */
    public function getFaker()
    {
        return \Faker\Factory::create('de_de');
    }
}