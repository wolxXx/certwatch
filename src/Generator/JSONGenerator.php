<?php

namespace Certwatch\Generator;

class JSONGenerator extends GeneratorAbstract
{
    #[\Override]
    public function generate(): GeneratorInterface
    {
        $io = $this->getIo();
        $io?->writeln('starting json generation');
        $data = [
            'generated' => new \DateTime()->format(format: 'Y-m-d H:i:s'),
            'watches'   => [],
        ];
        foreach ($this->getResults() as $result) {
            $domainData = [
                'domain'         => $result->getDomain(),
                'valid'          => $result->isValid(),
                'validUntil'     => null,
                'validUntilDays' => null,
                'issuer'         => null,
                'errors'         => [],
            ];
            if (false === $result->isValid()) {
                $domainData['errors'] = $result->getErrors();
                $data['watches'][]    = $domainData;
                continue;
            }
            $domainData['validUntilDays'] = $result->getValidUntilDays();
            $domainData['issuer']         = $result->getIssuer();
            $domainData['validUntil']     = $result
                ->getValidUntil()
                ->format(format: 'Y-m-d H:i:s')
            ;
            $data['watches'][]            = $domainData;
        }
        $target = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.json';
        $data   = json_encode(value: $data, flags: JSON_PRETTY_PRINT);
        $io?->writeln(messages: 'finished json generation');
        $io?->writeln(messages: 'writing json file "' . $target . '"');
        file_put_contents(filename: $target, data: $data);
        $io?->writeln(messages: 'json generation done');

        return $this;
    }
}