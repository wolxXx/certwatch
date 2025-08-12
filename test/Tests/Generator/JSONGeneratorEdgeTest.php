<?php

class JSONGeneratorEdgeTest extends \Certwatch\Test\TestBase
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

    public function testGenerateWithoutIoAndEmptyResults(): void
    {
        $generator = new \Certwatch\Generator\JSONGenerator();
        // Ensure empty results array to avoid foreach over null
        $generator->setResults([]);
        $ret = $generator->generate();
        $this->assertSame($generator, $ret);
        $this->assertFileExists($this->target);
        $json = file_get_contents($this->target);
        $this->assertNotFalse($json);
        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('watches', $data);
        $this->assertCount(0, $data['watches']);
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
            ->setValidUntilDays(1);

        $invalidNoErrors = (new \Certwatch\Result())
            ->setDomain('invalid-no-errors.example.com')
            ->setValid(false);

        $invalidWithSpecialError = (new \Certwatch\Result())
            ->setDomain('invalid-special.example.com')
            ->setValid(false)
            ->addError('Bad & <error> message');

        $generator = new \Certwatch\Generator\JSONGenerator();
        $generator->setIo($io)->setResults([$valid, $invalidNoErrors, $invalidWithSpecialError])->generate();

        $json = file_get_contents($this->target);
        $this->assertNotFalse($json);
        $data = json_decode($json, true);
        $this->assertIsArray($data);

        $byDomain = [];
        foreach ($data['watches'] as $entry) {
            $byDomain[$entry['domain']] = $entry;
        }

        $this->assertSame('Issuer & < > "', $byDomain['weird & <domain>.example.com']['issuer']);
        $this->assertSame('2030-01-01 00:00:00', $byDomain['weird & <domain>.example.com']['validUntil']);
        $this->assertSame(1, $byDomain['weird & <domain>.example.com']['validUntilDays']);

        $this->assertArrayHasKey('invalid-no-errors.example.com', $byDomain);
        $this->assertIsArray($byDomain['invalid-no-errors.example.com']['errors']);
        $this->assertCount(0, $byDomain['invalid-no-errors.example.com']['errors']);

        $this->assertContains('Bad & <error> message', $byDomain['invalid-special.example.com']['errors']);
    }
}
