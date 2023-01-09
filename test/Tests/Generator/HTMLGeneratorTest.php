<?php

class HTMLGeneratorTest extends \Certwatch\Test\TestBase
{
    public function testInstance()
    {
        $this->assertInstanceOf(\Certwatch\Generator\HTMLGenerator::class, new \Certwatch\Generator\HTMLGenerator());
    }


    public function testGeneration()
    {
        $generator = new \Certwatch\Generator\HTMLGenerator();
        $generator->setStore(false);
        $now = new \DateTime('2020-01-01 12:34:56');
        $generator->setNow($now);
        $generator->setResults([
            (new \Certwatch\Result())
            ->setDomain('google.de')
        ]);
        $generator->generate();
        $expected = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'fixture'.DIRECTORY_SEPARATOR.'result.html');
        #$expected = '';
        $this->assertSame($expected, $generator->getResult());
    }


    public function testGetSetNow()
    {
        $generator = new \Certwatch\Generator\HTMLGenerator();
        $this->assertEquals((new \DateTime())->format('Y-m-d H:i'), $generator->getNow()->format('Y-m-d H:i'));
    }


    public function testGetSetResult()
    {
        $generator = new \Certwatch\Generator\HTMLGenerator();
        $expected = 'foobar';
        $this->assertSame($expected, $generator->setResult($expected)->getResult());
    }


    public function testGetSetStore()
    {
        $generator = new \Certwatch\Generator\HTMLGenerator();
        $this->assertTrue($generator->isStore());
        $this->assertFalse($generator->setStore(false)->isStore());
        $this->assertTrue($generator->setStore(true)->isStore());
    }
}