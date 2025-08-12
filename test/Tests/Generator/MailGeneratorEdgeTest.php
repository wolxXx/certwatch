<?php

class MailGeneratorEdgeTest extends \Certwatch\Test\TestBase
{
    private string $projectRoot;
    private string $mailConfigPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
        $this->mailConfigPath = $this->projectRoot . DIRECTORY_SEPARATOR . 'mail-config.php';
        if (file_exists($this->mailConfigPath)) {
            @unlink($this->mailConfigPath);
        }
    }

    public function tearDown(): void
    {
        if (file_exists($this->mailConfigPath)) {
            @unlink($this->mailConfigPath);
        }
        parent::tearDown();
    }

    public function testGenerateWithoutIoAndNoConfigDoesNotThrow(): void
    {
        $results = [
            (new \Certwatch\Result())->setDomain('example.com')->setValid(false)
        ];
        $generator = new \Certwatch\Generator\MailGenerator();
        $generator->setResults($results);
        // getIo() is null by default
        $generator->generate();
        $this->assertTrue(true);
    }

    public function testGenerateWithConfigToAsStringAndNoIo(): void
    {
        $config = [
            'username' => 'user@example.com',
            'password' => 'pass',
            'server' => 'localhost',
            'encryption' => 'tls',
            'port' => 1025,
            'from' => 'from@example.com',
            'to' => 'to1@example.com', // string instead of array
            'fromName' => 'Certwatch Test',
        ];
        file_put_contents($this->mailConfigPath, '<?php return ' . var_export($config, true) . ';');

        $results = [
            (new \Certwatch\Result())->setDomain('example.com')->setValid(true)
        ];
        $generator = new \Certwatch\Generator\MailGenerator();
        $generator->setResults($results);

        // IO is null; should not throw even if sending fails internally
        $generator->generate();
        $this->assertTrue(true);
    }
}
