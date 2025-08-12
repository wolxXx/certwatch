<?php

namespace Certwatch\Generator;

class XMLGenerator extends GeneratorAbstract
{
    #[\Override]
    public function generate(): GeneratorInterface
    {
        $io = $this->getIo();
        $io?->writeln(messages: 'starting xml generation');
        $addCdata = function($name, $value, \SimpleXMLElement $parent) {
            $child = $parent->addChild(qualifiedName: $name);
            if ($child !== null) {
                $childNode  = dom_import_simplexml(node: $child);
                $childOwner = $childNode->ownerDocument;
                $childNode->appendChild(node: $childOwner->createCDATASection($value));
            }

            return $child;
        };
        $string   = '<?xml version="1.0" encoding="UTF-8"?><result></result>';
        $xmlRoot  = new \SimpleXMLElement(data: $string);
        $addCdata(name: 'generated', value: new \DateTime()->format(format: 'Y-m-d H:i:s'), parent: $xmlRoot);
        $watches = $xmlRoot->addChild(qualifiedName: 'watches');
        foreach ($this->getResults() as $result) {
            $entry = $watches->addChild(qualifiedName: 'watch');
            $addCdata(name: 'domain', value: $result->getDomain(), parent: $entry);
            $entry->addChild(qualifiedName: 'valid', value: $result->isValid() ? 'true' : 'false');
            if (false === $result->isValid()) {
                $entry->addChild(qualifiedName: 'validUntil', value: null);
                $entry->addChild(qualifiedName: 'validUntilDays', value: null);
                $entry->addChild(qualifiedName: 'issuer', value: null);
                $errors = $entry->addChild(qualifiedName: 'errors');
                foreach ($result->getErrors() as $error) {
                    $addCdata(name: 'error', value: $error, parent: $errors);
                }
                continue;
            }
            $addCdata(                           name  : 'validUntil', value: $result
                ->getValidUntil()
                ->format(format: 'Y-m-d H:i:s'), parent: $entry);
            $entry->addChild(qualifiedName: 'validUntilDays', value: (string)$result->getValidUntilDays());
            $addCdata(name: 'issuer', value: $result->getIssuer(), parent: $entry);
            $entry->addChild(qualifiedName: 'errors');
        }
        $target                  = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.xml';
        $data                    = $xmlRoot->asXML();
        $dom                     = new \DOMDocument(version: "1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML(source: $data);
        $data = $dom->saveXML();
        $io?->writeln(messages: 'finished xml generation');
        $io?->writeln(messages: 'writing xml file "' . $target . '"');
        file_put_contents(filename: $target, data: $data);
        $io?->writeln(messages: 'xml generation done');

        return $this;
    }
}