<?php

class ConsoleGeneratorTest extends \Certwatch\Test\TestBase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(\Certwatch\Generator\ConsoleGenerator::class, new \Certwatch\Generator\ConsoleGenerator());
    }

    public function testGenerateWithoutIoThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('need io for continuing!');

        $generator = new \Certwatch\Generator\ConsoleGenerator();
        // Ensure results is set to empty to avoid foreach on null
        $generator->setResults([]);
        $generator->generate();
    }

    public function testGenerateWithEmptyResultsRendersHeaders()
    {
        $buffer = new \Symfony\Component\Console\Output\BufferedOutput();
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            $buffer
        );

        $generator = new \Certwatch\Generator\ConsoleGenerator();
        $ret = $generator->setIo($io)->setResults([])->generate();

        // Chainability: should return the same instance
        $this->assertSame($generator, $ret);

        $out = $buffer->fetch();
        $this->assertIsString($out);
        // Table header title and headers should appear
        $this->assertStringContainsString('Domains', $out);
        $this->assertStringContainsString('name', $out);
        $this->assertStringContainsString('valid', $out);
        $this->assertStringContainsString('valid until', $out);
        $this->assertStringContainsString('valid until days', $out);
        $this->assertStringContainsString('errors', $out);
        $this->assertStringContainsString('issuer', $out);
    }

    public function testGenerateWithValidAndInvalidResults()
    {
        $buffer = new \Symfony\Component\Console\Output\BufferedOutput();
        // Disable decoration so styled tags resolve to plain text ("no", "365 days", etc.)
        $buffer->setDecorated(false);
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            $buffer
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

        $generator = new \Certwatch\Generator\ConsoleGenerator();
        $generator->setIo($io)->setResults([$valid, $invalid])->generate();

        $out = $buffer->fetch();

        // Valid row expectations
        $this->assertStringContainsString('valid.example.com', $out);
        $this->assertStringContainsString('yes', $out);
        $this->assertStringContainsString('2030-01-01 00:00:00', $out);
        $this->assertStringContainsString('365 days', $out);
        $this->assertStringContainsString('Example Issuer', $out);

        // Invalid row expectations
        $this->assertStringContainsString('invalid.example.com', $out);
        // Styled <error>no</error> should be rendered as plain 'no' with decoration disabled
        $this->assertStringContainsString('no', $out);
        $this->assertStringContainsString('DNS lookup failed', $out);
        // Placeholders for missing values
        $this->assertStringContainsString('-', $out);
    }
}
