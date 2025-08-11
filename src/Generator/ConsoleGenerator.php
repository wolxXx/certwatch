<?php

namespace Certwatch\Generator;

class ConsoleGenerator extends GeneratorAbstract
{
    #[\Override]
    public function generate(): GeneratorInterface
    {
        if (null === $this->getIo()) {
            throw new \InvalidArgumentException(message: 'need io for continuing!');
        }
        $table = new \Symfony\Component\Console\Helper\Table(output: $this->getIo());
        $table
            ->setHeaderTitle(title: 'Domains')
            ->setHeaders(headers: ['name', 'valid', 'valid until', 'valid until days', 'errors', 'issuer'])
        ;
        foreach ($this->getResults() as $result) {
            if (false === $result->isValid()) {
                $table->addRow(row: [
                        $result->getDomain(),
                        '<error>no</error>',
                        '-',
                        '-',
                        implode(separator: PHP_EOL, array: $result->getErrors()),
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
            $table->addRow(row: [
                    $result->getDomain(),
                    'yes',
                    $result->getValidUntil()->format('Y-m-d H:i:s'),
                    $prefix . $result->getValidUntilDays() . ' day' . (1 === $result->getValidUntilDays() ? '' : 's') . $suffix,
                    implode(separator: PHP_EOL, array: $result->getErrors()),
                    $result->getIssuer(),
                ]
            );
        }
        $table->render();

        return $this;
    }
}