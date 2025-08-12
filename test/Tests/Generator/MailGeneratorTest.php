<?php

class MailGeneratorTest extends \Certwatch\Test\TestBase
{
    private string $projectRoot;
    private string $mailConfigPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
        $this->mailConfigPath = $this->projectRoot . DIRECTORY_SEPARATOR . 'mail-config.php';
        // ensure we start without leftover config
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

    public function testGenerateWithoutConfigDoesNotThrow()
    {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            new \Symfony\Component\Console\Output\NullOutput()
        );
        $results = [
            (new \Certwatch\Result())->setDomain('example.com')->setValid(false)->addError('x')
        ];
        $generator = new \Certwatch\Generator\MailGenerator();
        $generator->setIo($io)->setResults($results);

        // No exception expected even if config is missing
        $generator->generate();
        $this->assertTrue(true);
    }

    public function testGenerateWithConfigHandlesSendFailureGracefully()
    {
        // Create a minimal but valid mail configuration file at the default expected path
        $config = [
            'username' => 'user@example.com',
            'password' => 'pass',
            'server' => 'localhost',
            'encryption' => 'tls',
            'port' => 1025, // typical dev mailhog/empty port; sending will likely fail but be caught
            'from' => 'from@example.com',
            'to' => ['to1@example.com', 'to2@example.com'],
            'fromName' => 'Certwatch Test',
            'bcc' => [],
            'cc' => []
        ];
        file_put_contents($this->mailConfigPath, '<?php return ' . var_export($config, true) . ';');

        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            new \Symfony\Component\Console\Output\NullOutput()
        );
        $results = [
            (new \Certwatch\Result())->setDomain('example.com')->setValid(true)
        ];
        $generator = new \Certwatch\Generator\MailGenerator();
        $generator->setIo($io)->setResults($results);

        // Should not throw even if PHPMailer::send fails; errors are handled internally
        $generator->generate();
        $this->assertTrue(true);
    }
}
