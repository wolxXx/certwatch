<?php

namespace Certwatch\Generator;

/**
 * Class HTMLGenerator
 *
 * @package Certwatch\Generator
 */
class HTMLGenerator extends GeneratorAbstract
{
    /**
     * @var bool
     */
    protected $store = true;

    /**
     * @var string
     */
    protected $result;

    /**
     * @var \DateTime | null
     */
    protected $now;

    /**
     * @var string | null
     */
    protected $target;

    /**
     * @var string | null
     */
    protected $targetIndex;

    /**
     * @var string | null
     */
    protected $customTarget;


    /**
     * HTMLGenerator constructor.
     */
    public function __construct()
    {
        $this
            ->setTarget(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.html')
            ->setTargetIndex(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'index.html')
            ->setCustomTarget(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'results.twig')
        ;
    }


    /**
     * @inheritdoc
     */
    public function generate(): GeneratorInterface
    {
        if (null !== $this->getIo()) {
            $this->getIo()->writeln('starting xml generation');
        }
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
        if (null !== $this->getIo()) {
            $this->getIo()->writeln('finished html generation');
        }
        if (true === $this->store) {
            if (null !== $this->getIo()) {
                $this->getIo()->writeln('writing html file "' . $this->getTarget(). '"');
            }
            file_put_contents($this->getTarget(), $html);
            if (null !== $this->getIo()) {
                $this->getIo()->writeln('writing html file "' . $this->getTargetIndex() . '"');
            }
            file_put_contents($this->getTargetIndex(), $html);
        }
        if (null !== $this->getIo()) {
            $this->getIo()->writeln('html generation done');
        }

        return $this;
    }


    /**
     * @return bool
     */
    public function isStore(): bool
    {
        return $this->store;
    }


    /**
     * @param bool $store
     *
     * @return HTMLGenerator
     */
    public function setStore(bool $store): HTMLGenerator
    {
        $this->store = $store;

        return $this;
    }


    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }


    /**
     * @param string $result
     *
     * @return HTMLGenerator
     */
    public function setResult(string $result): HTMLGenerator
    {
        $this->result = $result;

        return $this;
    }


    /**
     * @return \DateTime|null
     */
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


    /**
     * @param \DateTime|null $now
     *
     * @return $this
     */
    public function setNow(?\DateTime $now): HTMLGenerator
    {
        $this->now = $now;

        return $this;
    }


    /**
     * @return null|string
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }


    /**
     * @param null|string $target
     *
     * @return $this
     */
    public function setTarget(?string $target): HTMLGenerator
    {
        $this->target = $target;

        return $this;
    }


    /**
     * @return null|string
     */
    public function getTargetIndex(): ?string
    {
        return $this->targetIndex;
    }


    /**
     * @param null|string $targetIndex
     *
     * @return $this
     */
    public function setTargetIndex(?string $targetIndex): HTMLGenerator
    {
        $this->targetIndex = $targetIndex;

        return $this;
    }


    /**
     * @return null|string
     */
    public function getCustomTarget(): ?string
    {
        return $this->customTarget;
    }


    /**
     * @param null|string $customTarget
     *
     * @return $this
     */
    public function setCustomTarget(?string $customTarget): HTMLGenerator
    {
        $this->customTarget = $customTarget;

        return $this;
    }
}
