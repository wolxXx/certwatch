<?php

class HTMLGeneratorExtraTest extends \Certwatch\Test\TestBase
{
    private string $tmpHtml;
    private string $tmpIndex;

    public function setUp(): void
    {
        parent::setUp();
        $base = __DIR__ . DIRECTORY_SEPARATOR . 'fixture';
        if (!is_dir($base)) {
            mkdir($base, 0777, true);
        }
        $this->tmpHtml = $base . DIRECTORY_SEPARATOR . 'tmp-results.html';
        $this->tmpIndex = $base . DIRECTORY_SEPARATOR . 'tmp-index.html';
        @unlink($this->tmpHtml);
        @unlink($this->tmpIndex);
    }

    public function tearDown(): void
    {
        if (file_exists($this->tmpHtml)) {
            @unlink($this->tmpHtml);
        }
        if (file_exists($this->tmpIndex)) {
            @unlink($this->tmpIndex);
        }
        parent::tearDown();
    }

    public function testFallbackToDefaultTemplateWhenCustomMissing(): void
    {
        $gen = new \Certwatch\Generator\HTMLGenerator();
        $gen->setStore(false);
        // Point to a non-existent custom template path to force fallback
        $gen->setCustomTarget(__DIR__ . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'does-not-exist.twig');
        $gen->setResults([
            (new \Certwatch\Result())->setDomain('fallback.example.com')
        ]);
        $gen->generate();
        $html = $gen->getResult();
        $this->assertIsString($html);
        $this->assertStringContainsString('fallback.example.com', $html);
    }

    public function testStoreTrueWritesToCustomTargets(): void
    {
        $gen = new \Certwatch\Generator\HTMLGenerator();
        $gen->setStore(true);
        $gen->setTarget($this->tmpHtml);
        $gen->setTargetIndex($this->tmpIndex);
        $now = new \DateTime('2020-02-02 02:02:02');
        $gen->setNow($now);
        $gen->setResults([
            (new \Certwatch\Result())->setDomain('store.example.com')
        ]);
        $gen->generate();

        $this->assertFileExists($this->tmpHtml);
        $this->assertFileExists($this->tmpIndex);
        $html = file_get_contents($this->tmpHtml);
        $this->assertNotFalse($html);
        $this->assertStringContainsString('store.example.com', $html);
        $this->assertStringContainsString('2020-02-02 02:02:02', $html);
    }
}
