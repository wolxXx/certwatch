<?php

class ConsoleGeneratorPluralizationTest extends \Certwatch\Test\TestBase
{
    public function testPluralizationAndEmphasisWithoutDecoration()
    {
        $buffer = new \Symfony\Component\Console\Output\BufferedOutput();
        $buffer->setDecorated(false);
        $io = new \Symfony\Component\Console\Style\SymfonyStyle(
            new \Symfony\Component\Console\Input\StringInput(''),
            $buffer
        );

        $oneDay = (new \Certwatch\Result())
            ->setDomain('one.example.com')
            ->setValid(true)
            ->setIssuer('Issuer1')
            ->setValidFrom(new \DateTime('2029-12-30 00:00:00'))
            ->setValidUntil(new \DateTime('2030-01-01 00:00:00'))
            ->setValidUntilDays(1);

        $fewDays = (new \Certwatch\Result())
            ->setDomain('few.example.com')
            ->setValid(true)
            ->setIssuer('Issuer2')
            ->setValidFrom(new \DateTime('2029-12-30 00:00:00'))
            ->setValidUntil(new \DateTime('2030-01-06 00:00:00'))
            ->setValidUntilDays(5); // triggers <error> style but decoration disabled

        $manyDays = (new \Certwatch\Result())
            ->setDomain('many.example.com')
            ->setValid(true)
            ->setIssuer('Issuer3')
            ->setValidFrom(new \DateTime('2029-12-30 00:00:00'))
            ->setValidUntil(new \DateTime('2030-02-15 00:00:00'))
            ->setValidUntilDays(45); // triggers <info> style but decoration disabled

        $generator = new \Certwatch\Generator\ConsoleGenerator();
        $generator->setIo($io)->setResults([$oneDay, $fewDays, $manyDays])->generate();

        $out = $buffer->fetch();
        $this->assertStringContainsString('one.example.com', $out);
        $this->assertStringContainsString('few.example.com', $out);
        $this->assertStringContainsString('many.example.com', $out);

        // Pluralization correctness
        $this->assertStringContainsString('1 day', $out);
        $this->assertStringContainsString('5 days', $out);
        $this->assertStringContainsString('45 days', $out);

        // Ensure no raw style tags leak when decoration disabled
        $this->assertStringNotContainsString('<error>', $out);
        $this->assertStringNotContainsString('<info>', $out);
    }
}
