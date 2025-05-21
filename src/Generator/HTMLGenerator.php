<?php

declare(strict_types=1);

namespace Certwatch\Generator;

final class HTMLGenerator extends GeneratorAbstract
{

    protected bool $store = true;


    protected string $result;

    protected ?\DateTime $now = null;


    protected ?string $target = null;

    protected ?string $targetIndex = null;


    protected ?string $customTarget;


    public final function __construct()
    {
        $this
            ->setTarget(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.html')
            ->setTargetIndex(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'index.html')
            ->setCustomTarget(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.twig')
        ;
    }


    public function generate(): self
    {
        $this
            ->getIo()
            ?->writeln('starting html generation')
        ;
        $loader       = new \Twig\Loader\FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        $twig         = new \Twig\Environment($loader);
        $twigTemplate = 'results.default.twig';
        if (null !== $this->getCustomTarget() && true === file_exists($this->getCustomTarget())) {
            $twigTemplate = 'results.twig';
        }
        $template     = $twig->load($twigTemplate);
        $data         = [
            'items' => $this->getResults(),
            'now'   => $this->getNow()->format('Y-m-d H:i:s'),
        ];
        $html         = $template->render($data);
        $this->result = $html;
        $this
            ->getIo()
            ?->writeln('finished html generation')
        ;
        if (true === $this->store) {
            $this
                ->getIo()
                ?->writeln('writing html file "' . $this->getTarget() . '"')
            ;
            file_put_contents($this->getTarget(), $html);
            $this
                ->getIo()
                ?->writeln('writing html file "' . $this->getTargetIndex() . '"')
            ;
            file_put_contents($this->getTargetIndex(), $html);
        }
        $this
            ->getIo()
            ?->writeln('html generation done')
        ;

        return $this;
    }


    public function isStore(): bool
    {
        return $this->store;
    }


    public function setStore(bool $store): self
    {
        $this->store = $store;

        return $this;
    }


    public function getResult(): string
    {
        return $this->result;
    }


    public function setResult(string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getNow(): ?\DateTime
    {
        if (null === $this->now) {
            return $this
                ->setNow(new \DateTime())
                ->getNow()
                ;
        }

        return $this->now;
    }
    public function setNow(?\DateTime $now): self
    {
        $this->now = $now;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target = null): self
    {
        $this->target = $target;

        return $this;
    }

    public function getTargetIndex(): ?string
    {
        return $this->targetIndex;
    }

    public function setTargetIndex(?string $targetIndex = null): self
    {
        $this->targetIndex = $targetIndex;

        return $this;
    }

    public function getCustomTarget(): ?string
    {
        return $this->customTarget;
    }


    public function setCustomTarget(?string $customTarget = null): self
    {
        $this->customTarget = $customTarget;

        return $this;
    }
}
