<?php

class RunnerTest extends \Certwatch\Test\TestBase
{
    public function testInstantiation()
    {
        $runner = new \Certwatch\Runner();
        $this->assertSame(\Certwatch\Runner::class, get_class($runner));
    }


    public function testGetSetIo()
    {
        $runner = new \Certwatch\Runner();
        $this->assertNull($runner->getIo());
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(new \Symfony\Component\Console\Input\StringInput(''), new \Symfony\Component\Console\Output\NullOutput());
        $this->assertSame($io, $runner->setIo($io)->getIo());
    }

    public function testGetSetPathToDomains()
    {
        $runner = new \Certwatch\Runner();
        $this->assertSame(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'domains.txt'), realpath($runner->getPathToDomains()));
        $newPath = 'new/path/to/domains.txt';
        $this->assertSame($newPath, $runner->setPathToDomains($newPath)->getPathToDomains());
    }


    public function testReloadFailsForNonExistingFile()
    {
        $runner = new \Certwatch\Runner();
        $runner->clearResults();
        $newPath = 'new/path/to/domains.txt';
        $runner->setPathToDomains($newPath)->reloadConfiguration();
        $this->assertSame(0, sizeof($runner->getResults()));
    }

    public function testCustomDomainsFile()
    {
        $runner = new \Certwatch\Runner();
        $pathToDomains = __DIR__.DIRECTORY_SEPARATOR.'fixture'.DIRECTORY_SEPARATOR.'domains.txt';
        $this->assertSame($pathToDomains, $runner->setPathToDomains($pathToDomains)->getPathToDomains());
        $runner->reloadConfiguration();
        $this->assertSame(3, sizeof($runner->getResults()));
        $domains = [
            'google.de',
            'barfoos.net',
            'git.wolxxx.de',
        ];
        foreach ($runner->getResults() as $result) {
            $this->assertTrue(in_array($result->getDomain(), $domains));
        }
    }


    public function testResults()
    {
        $runner = new \Certwatch\Runner();
        $runner->clearResults();
        $this->assertSame(0, sizeof($runner->getResults()));
        $result1 = new \Certwatch\Result();
        $result2 = new \Certwatch\Result();
        $runner->addResult($result1);
        $this->assertSame(1, sizeof($runner->getResults()));
        $runner->addResult($result2);
        $this->assertSame(2, sizeof($runner->getResults()));
    }


    /**
     * @param string $domain
     * @param bool   $valid
     * @dataProvider runTestDataProvider
     */
    public function testRun(string $domain, bool $valid)
    {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(new \Symfony\Component\Console\Input\StringInput(''), new \Symfony\Component\Console\Output\NullOutput());
        $runner = new \Certwatch\Runner();
        $runner->clearResults();
        $runner->addResult((new \Certwatch\Result())->setDomain($domain));
        $runner->setIo($io);
        $runner->run();

        $result = $runner->getResults()[0];
        $this->assertSame($valid, $result->isValid());
    }


    public function runTestDataProvider()
    {
        return [
            ['aaaaaaaaaaaaaaaaaaaaaaa.de', false],
            ['google.de', true],
            ['git.wolxxx.de', true],
        ];
    }
}