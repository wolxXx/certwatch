<?php

namespace Certwatch\Generator;

/**
 * Class ConsoleGenerator
 *
 * @package Certwatch\Generator
 */
class ConsoleGenerator extends GeneratorAbstract
{
    /**
     * @inheritdoc
     */
    public function generate(): GeneratorInterface
    {
        if (null === $this->getIo()) {
            throw new \InvalidArgumentException('need io for continuing!');
        }
        $table = new \Symfony\Component\Console\Helper\Table($this->getIo());
        $table
            ->setHeaderTitle('Domains')
            ->setHeaders(['name', 'valid', 'valid until', 'valid until days', 'errors', 'issuer'])
        ;
        foreach ($this->getResults() as $result) {
            if (false === $result->isValid()) {
                $table->addRow([
                        $result->getDomain(),
                        '<error>no</error>',
                        '-',
                        '-',
                        implode(PHP_EOL, $result->getErrors()),
                        '-',
                    ]
                );
                continue;
            }
            $prefix = '';
            $suffix = '';
            if ($result->getValidUntilDays() < 10) {
                $prefix = '<error>';
                $suffix = '</error>';
            }
            if ($result->getValidUntilDays() > 30) {
                $prefix = '<info>';
                $suffix = '</info>';
            }
            $table->addRow([
                    $result->getDomain(),
                    'yes',
                    $result->getValidUntil()->format('Y-m-d H:i:s'),
                    $prefix . $result->getValidUntilDays() . ' day' . (1 === $result->getValidUntilDays() ? '' : 's') . $suffix,
                    implode(PHP_EOL, $result->getErrors()),
                    $result->getIssuer(),
                ]
            );
        }
        $table->render();

        return $this;
    }
}