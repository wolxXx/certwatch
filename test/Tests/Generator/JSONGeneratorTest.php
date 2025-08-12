<?php

class JSONGeneratorTest extends \Certwatch\Test\TestBase
{
    private string $target;

    public function setUp(): void
    {
        parent::setUp();
        $this->target = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.json';
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

    public function testGenerateCreatesJsonWithValidAndInvalidResults()
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

        $generator = new \Certwatch\Generator\JSONGenerator();
        $return = $generator
            ->setIo($io)
            ->setResults([$valid, $invalid])
            ->generate();

        // It should be chainable and return the generator
        $this->assertSame($generator, $return);

        // File should be created
        $this->assertFileExists($this->target);

        $json = file_get_contents($this->target);
        $this->assertNotFalse($json);
        $data = json_decode($json, true);
        $this->assertIsArray($data);

        // top-level structure
        $this->assertArrayHasKey('generated', $data);
        $this->assertArrayHasKey('watches', $data);
        $this->assertIsArray($data['watches']);

        // Expect exactly two watches
        $this->assertCount(2, $data['watches']);

        // Find entries by domain
        $byDomain = [];
        foreach ($data['watches'] as $entry) {
            $byDomain[$entry['domain']] = $entry;
        }

        // Valid entry checks
        $this->assertArrayHasKey('valid.example.com', $byDomain);
        $validEntry = $byDomain['valid.example.com'];
        $this->assertTrue($validEntry['valid']);
        $this->assertSame('2030-01-01 00:00:00', $validEntry['validUntil']);
        $this->assertSame(365, $validEntry['validUntilDays']);
        $this->assertSame('Example Issuer', $validEntry['issuer']);
        $this->assertIsArray($validEntry['errors']);
        $this->assertCount(0, $validEntry['errors']);

        // Invalid entry checks
        $this->assertArrayHasKey('invalid.example.com', $byDomain);
        $invalidEntry = $byDomain['invalid.example.com'];
        $this->assertFalse($invalidEntry['valid']);
        $this->assertNull($invalidEntry['validUntil']);
        $this->assertNull($invalidEntry['validUntilDays']);
        $this->assertNull($invalidEntry['issuer']);
        $this->assertIsArray($invalidEntry['errors']);
        $this->assertContains('DNS lookup failed', $invalidEntry['errors']);
    }
}
