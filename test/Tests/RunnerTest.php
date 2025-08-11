<?php

class RunnerTest extends \Certwatch\Test\TestBase
{
    public function testInstantiation()
    {
        $runner = new \Certwatch\Runner();
        $this->assertSame(\Certwatch\Runner::class, get_class(object: $runner));
    }


    public function testGetSetIo()
    {
        $runner = new \Certwatch\Runner();
        $this->assertNull($runner->getIo());
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(input: new \Symfony\Component\Console\Input\StringInput(input: ''), output: new \Symfony\Component\Console\Output\NullOutput());
        $this->assertSame($io, $runner->setIo(io: $io)->getIo());
    }

    public function testGetSetPathToDomains()
    {
        $runner = new \Certwatch\Runner();
        $this->assertSame(realpath(path: __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'domains.txt'), realpath(path: $runner->getPathToDomains()));
        $newPath = 'new/path/to/domains.txt';
        $this->assertSame($newPath, $runner->setPathToDomains(pathToDomains: $newPath)->getPathToDomains());
    }


    public function testReloadFailsForNonExistingFile()
    {
        $runner = new \Certwatch\Runner();
        $runner->clearResults();
        $newPath = 'new/path/to/domains.txt';
        $runner->setPathToDomains(pathToDomains: $newPath)->reloadConfiguration();
        $this->assertSame(0, count(value: $runner->getResults()));
    }

    public function testCustomDomainsFile()
    {
        $runner = new \Certwatch\Runner();
        $pathToDomains = __DIR__.DIRECTORY_SEPARATOR.'fixture'.DIRECTORY_SEPARATOR.'domains.txt';
        $this->assertSame($pathToDomains, $runner->setPathToDomains(pathToDomains: $pathToDomains)->getPathToDomains());
        $runner->reloadConfiguration();
        $this->assertSame(3, count(value: $runner->getResults()));
        $domains = [
            'google.de',
            'barfoos.net',
            'git.wolxxx.de',
        ];
        foreach ($runner->getResults() as $result) {
            $this->assertTrue(in_array(needle: $result->getDomain(), haystack: $domains));
        }
    }


    public function testResults()
    {
        $runner = new \Certwatch\Runner();
        $runner->clearResults();
        $this->assertSame(0, count(value: $runner->getResults()));
        $result1 = new \Certwatch\Result();
        $result2 = new \Certwatch\Result();
        $runner->addResult(result: $result1);
        $this->assertSame(1, count(value: $runner->getResults()));
        $runner->addResult(result: $result2);
        $this->assertSame(2, count(value: $runner->getResults()));
    }


    /**
     * @param string $domain
     * @param bool   $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("runTestDataProvider")]
    public function testRun(string $domain, bool $valid)
    {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(input: new \Symfony\Component\Console\Input\StringInput(input: ''), output: new \Symfony\Component\Console\Output\NullOutput());
        $runner = new \Certwatch\Runner();
        $runner->clearResults();
        $runner->addResult(result: (new \Certwatch\Result())->setDomain(domain: $domain));
        $runner->setIo(io: $io);
        $runner->run();

        $result = $runner->getResults()[0];
        $this->assertSame($valid, $result->isValid());
    }


    public static function runTestDataProvider()
    {
        return [
            ['aaaaaaaaaaaaaaaaaaaaaaa.de', false],
            ['google.de', true],
            ['github.com', true],
        ];
    }
}