<?php

namespace Certwatch\Generator;

/**
 * Class XMLGenerator
 *
 * @package Certwatch\Generator
 */
class XMLGenerator extends GeneratorAbstract
{
    /**
     * @inheritdoc
     */
    public function generate(): GeneratorInterface
    {
        $this->getIo()->writeln('starting xml generation');
        $addCdata = function ($name, $value, \SimpleXMLElement $parent) {
            $child = $parent->addChild($name);
            if ($child !== null) {
                $childNode  = dom_import_simplexml($child);
                $childOwner = $childNode->ownerDocument;
                $childNode->appendChild($childOwner->createCDATASection($value));
            }

            return $child;
        };
        $string   = '<?xml version="1.0" encoding="UTF-8"?><result></result>';
        $xmlRoot  = new \SimpleXMLElement($string);
        $addCdata('generated', (new \DateTime())->format('Y-m-d H:i:s'), $xmlRoot);
        $watches = $xmlRoot->addChild('watches');
        foreach ($this->getResults() as $result) {
            $entry = $watches->addChild('watch');
            $addCdata('domain', $result->getDomain(), $entry);
            $entry->addChild('valid', $result->isValid() ? 'true' : 'false');
            if (false === $result->isValid()) {
                $entry->addChild('validUntil', null);
                $entry->addChild('validUntilDays', null);
                $entry->addChild('issuer', null);
                $errors = $entry->addChild('errors');
                foreach ($result->getErrors() as $error) {
                    $addCdata('error', $error, $errors);
                }
                continue;
            }
            $addCdata('validUntil', $result->getValidUntil()->format('Y-m-d H:i:s'), $entry);
            $entry->addChild('validUntilDays', $result->getValidUntilDays());
            $addCdata('issuer', $result->getIssuer(), $entry);
            $entry->addChild('errors');
        }
        $target                  = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.xml';
        $data                    = $xmlRoot->asXML();
        $dom                     = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($data);
        $data = $dom->saveXML();
        $this->getIo()->writeln('finished xml generation');
        $this->getIo()->writeln('writing xml file "' . $target . '"');
        file_put_contents($target, $data);
        $this->getIo()->writeln('xml generation done');

        return $this;
    }
}