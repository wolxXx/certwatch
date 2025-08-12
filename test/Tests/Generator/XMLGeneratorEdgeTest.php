<?php

class XMLGeneratorEdgeTest extends \Certwatch\Test\TestBase
{
    private string $target;

    public function setUp(): void
    {
        parent::setUp();
        $this->target = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.xml';
        if (file_exists($this->target)) {
            @unlink($this->target);
        }
    }

    public function tearDown(): void
    {
        if (file_exists($this->target)) {
            @unlink($this->target);
        }
        parent::tearDown();
    }

    public function testGenerateWithoutIoAndEmptyResults(): void
    {
        $generator = new \Certwatch\Generator\XMLGenerator();
        $generator->setResults([]);
        $ret = $generator->generate();
        $this->assertSame($generator, $ret);
        $this->assertFileExists($this->target);
        $xml = file_get_contents($this->target);
        $this->assertNotFalse($xml);
        $this->assertStringContainsString('<watches', $xml);
    }

    public function testSpecialCharactersAndInvalidWithoutErrors(): void
    {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            new \Symfony\Component\Console\Output\NullOutput()
        );

        $valid = (new \Certwatch\Result())
            ->setDomain('weird & <domain>.example.com')
            ->setValid(true)
            ->setIssuer('Issuer & < > "')
            ->setValidFrom(new \DateTime('2020-01-01 00:00:00'))
            ->setValidUntil(new \DateTime('2030-01-01 00:00:00'))
            ->setValidUntilDays(2);

        $invalidNoErrors = (new \Certwatch\Result())
            ->setDomain('invalid-no-errors.example.com')
            ->setValid(false);

        $generator = new \Certwatch\Generator\XMLGenerator();
        $generator->setIo($io)->setResults([$valid, $invalidNoErrors])->generate();

        $xml = file_get_contents($this->target);
        $this->assertNotFalse($xml);

        // CDATA should preserve special characters
        $this->assertStringContainsString('weird & <domain>.example.com', $xml);
        $this->assertStringContainsString('Issuer & < > "', $xml);

        // invalid with no errors should still have <errors/>
        $this->assertStringContainsString('invalid-no-errors.example.com', $xml);
        $this->assertStringContainsString('<valid>false</valid>', $xml);
        $this->assertStringContainsString('<errors', $xml);
    }
}
