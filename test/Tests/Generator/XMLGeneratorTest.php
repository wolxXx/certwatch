<?php

class XMLGeneratorTest extends \Certwatch\Test\TestBase
{
    public function testGenerateCreatesXmlWithValidAndInvalidResults()
    {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            new \Symfony\Component\Console\Output\NullOutput()
        );

        $valid = (new \Certwatch\Result())
            ->setDomain('valid.example.com')
            ->setValid(true)
            ->setIssuer('Example Issuer')
            ->setValidFrom(new \DateTime('2020-01-01 00:00:00'))
            ->setValidUntil(new \DateTime('2030-01-01 00:00:00'))
            ->setValidUntilDays(365);

        $invalid = (new \Certwatch\Result())
            ->setDomain('invalid.example.com')
            ->setValid(false)
            ->addError('DNS lookup failed');

        $generator = new \Certwatch\Generator\XMLGenerator();
        $generator
            ->setIo($io)
            ->setResults([$valid, $invalid])
            ->generate();

        $target = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.xml';
        $this->assertFileExists($target);
        $xml = file_get_contents($target);
        $this->assertStringContainsString('<result>', $xml);
        $this->assertStringContainsString('<watches>', $xml);
        // valid entry
        $this->assertStringContainsString('valid.example.com', $xml);
        $this->assertStringContainsString('<valid>true</valid>', $xml);
        $this->assertStringContainsString('Example Issuer', $xml);
        // invalid entry
        $this->assertStringContainsString('invalid.example.com', $xml);
        $this->assertStringContainsString('<valid>false</valid>', $xml);
        $this->assertStringContainsString('DNS lookup failed', $xml);

        // cleanup
        @unlink($target);
    }
}
