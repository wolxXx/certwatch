<?php

namespace Certwatch\Generator;

class MailGenerator extends GeneratorAbstract
{
    #[\Override]
    public function generate(): GeneratorInterface
    {
        try {
            $mailConfiguration = new \Certwatch\MailConfigurationWrapper()->init();
        } catch (\Exception $exception) {
            $this
                ->getIo()
                ->warning(message: 'error on loading mail configuration: ' . $exception->getMessage())
            ;

            return $this;
        }
        $message = new HTMLGenerator()
            ->setResults(results: $this->getResults())
            ->setStore(store: false)
            ->generate()
            ->getResult()
        ;
        foreach ((array) $mailConfiguration->getTo() as $to) {
            try {

                $connection = new \PHPMailer\PHPMailer\PHPMailer(exceptions: true);
                $connection->msgHTML(message: $message);
                $connection->Subject = 'certwatch status';
                $connection->isSMTP();
                $connection->dsn         = 'SUCCESS,FAILURE,DELAY';
                $connection->Username    = $mailConfiguration->getUsername();
                $connection->Password    = $mailConfiguration->getPassword();
                $connection->Host        = $mailConfiguration->getServer();
                $connection->SMTPAuth    = true;
                $connection->SMTPSecure  = $mailConfiguration->getEncryption();
                $connection->Port        = $mailConfiguration->getPort();
                $connection->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
                $connection->addAddress(address: $to);
                $connection->From     = $mailConfiguration->getFrom();
                $connection->FromName = $mailConfiguration->getFrom();
                if (null !== $mailConfiguration->getFromName()) {
                    $connection->FromName = $mailConfiguration->getFromName();
                }
                foreach ((array) $mailConfiguration->getBcc() as $bcc) {
                    $connection->addBCC(address: $bcc);
                }
                foreach ((array) $mailConfiguration->getCc() as $cc) {
                    $connection->addCC(address: $cc);
                }
                $connection->send();
            } catch (\Exception $exception) {
                $this->getIo()->error(message: 'failed sending mail: ' . $exception->__toString());
            }
        }

        return $this;
    }
}
