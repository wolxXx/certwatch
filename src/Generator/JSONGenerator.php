<?php

namespace Certwatch\Generator;

/**
 * Class JSONGenerator
 *
 * @package Certwatch\Generator
 */
class JSONGenerator extends GeneratorAbstract
{
    /**
     * @inheritdoc
     */
    public function generate(): GeneratorInterface
    {
        $this->getIo()->writeln('starting json generation');
        $data = [
            'generated' => (new \DateTime())->format('Y-m-d H:i:s'),
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
            $domainData['validUntil']     = $result->getValidUntil()->format('Y-m-d H:i:s');
            $data['watches'][]            = $domainData;
        }
        $target = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.json';
        $data   = json_encode($data, JSON_PRETTY_PRINT);
        $this->getIo()->writeln('finished json generation');
        $this->getIo()->writeln('writing json file "' . $target . '"');
        file_put_contents($target, $data);
        $this->getIo()->writeln('json generation done');

        return $this;
    }
}