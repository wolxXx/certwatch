#!/usr/bin/env php
<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$application = new \Symfony\Component\Console\Application('runner', '1.0.0');
$application->add(new class extends \Symfony\Component\Console\Command\Command {
    const ARGUMENT_CHECK        = 'check';

    const ARGUMENT_INIT         = 'init';

    const ARGUMENT_SKIP_CONSOLE = 'skip-console';

    const ARGUMENT_SKIP_FILES   = 'skip-files';

    const ARGUMENT_SKIP_HTML    = 'skip-html';

    const ARGUMENT_SKIP_JSON    = 'skip-json';

    const ARGUMENT_SKIP_MAIL    = 'skip-mail';

    const ARGUMENT_SKIP_XML     = 'skip-xml';

    /**
     * @var string | null
     */
    protected $check;

    /**
     * @var bool
     */
    protected $init;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $io;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var \Certwatch\Runner
     */
    protected $runner;

    /**
     * @var bool
     */
    protected $skipConsole = false;

    /**
     * @var bool
     */
    protected $skipFiles = false;

    /**
     * @var bool
     */
    protected $skipHtml = false;

    /**
     * @var bool
     */
    protected $skipJson = false;

    /**
     * @var bool
     */
    protected $skipMail = false;

    /**
     * @var bool
     */
    protected $skipXml = false;


    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('user manager')
            ->setDescription('user manager')
            ->setHelp('user manager')
            ->addOption(self::ARGUMENT_INIT, null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'initialize the cerwatcher')
            ->addOption(self::ARGUMENT_CHECK, null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'check only the given domain(s), separate by comma!')
            ->addOption(self::ARGUMENT_SKIP_CONSOLE, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'skip console output')
            ->addOption(self::ARGUMENT_SKIP_FILES, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'skip file generation')
            ->addOption(self::ARGUMENT_SKIP_HTML, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'skip html generation')
            ->addOption(self::ARGUMENT_SKIP_JSON, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'skip json generation')
            ->addOption(self::ARGUMENT_SKIP_MAIL, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'skip sending mail')
            ->addOption(self::ARGUMENT_SKIP_XML, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'skip xml generation')
        ;
    }


    /**
     * @return $this
     */
    protected function parseOptions()
    {
        $this->skipConsole = $this->input->hasParameterOption('--' . self::ARGUMENT_SKIP_CONSOLE);
        $this->skipFiles   = $this->input->hasParameterOption('--' . self::ARGUMENT_SKIP_FILES);
        $this->skipHtml    = $this->input->hasParameterOption('--' . self::ARGUMENT_SKIP_HTML);
        $this->skipJson    = $this->input->hasParameterOption('--' . self::ARGUMENT_SKIP_JSON);
        $this->skipMail    = $this->input->hasParameterOption('--' . self::ARGUMENT_SKIP_MAIL);
        $this->skipXml     = $this->input->hasParameterOption('--' . self::ARGUMENT_SKIP_XML);
        $this->init        = $this->input->hasParameterOption('--' . self::ARGUMENT_INIT);
        $this->check       = $this->input->getOption(self::ARGUMENT_CHECK);

        return $this;
    }


    /**
     * @inheritdoc
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->io     = new \Symfony\Component\Console\Style\SymfonyStyle($this->input, $this->output);
        $this->parseOptions();
        $this->io->title('runner ' . $this->getApplication()->getLongVersion());
        if (true === $this->init) {
            $this->initializeCertwatch();

            return $this;
        }
        $this->runner = (new \Certwatch\Runner())
            ->setIo($this->io)
        ;
        if (null !== $this->check) {
            $this->runner->clearResults();
            $parts = explode(',', $this->check);
            foreach ($parts as $domain) {
                $this->runner->addResult((new \Certwatch\Result())->setDomain($domain));
            }
        }
        $this
            ->runner
            ->run()
        ;
        $this->generateHTML();
        $this->generateJSON();
        $this->generateXML();
        $this->sendMail();
        $this->outputResultsToConsole();

        return $this;
    }


    /**
     * @return $this
     */
    protected function initializeCertwatch()
    {
        $pathToMailConfiguration = (new \Certwatch\MailConfigurationWrapper())->getPathToConfigurationFile();
        if (true === file_exists($pathToMailConfiguration)) {
            $this->io->writeln('the mail configuration file already exists!');
            $overwrite = 'yes' === $this->io->choice('overwrite mail configuration file?', [1 => 'yes', 2 => 'no']);
            if (true === $overwrite) {
                unlink($pathToMailConfiguration);
            }
        }
        if (false === file_exists($pathToMailConfiguration)) {
            $this->io->writeln('copying mail config template file to ' . $pathToMailConfiguration);
            $this->io->writeln('please adjust the value for fitting your data');
            copy(__DIR__ . DIRECTORY_SEPARATOR . 'mail-config.template.php', $pathToMailConfiguration);
        }
        $pathToDomains = __DIR__ . DIRECTORY_SEPARATOR . 'domains.txt';
        if (true === file_exists($pathToDomains)) {
            $this->io->writeln('the domain configuration file already exists!');
            $overwrite = 'yes' === $this->io->choice('overwrite domain configuration file?', [1 => 'yes', 2 => 'no']);
            if (false === $overwrite) {
                return $this;
            }
        }
        $domains = [];
        $this->io->writeln('please add the domains you want to watch. leave value blank for finishing!');
        while (true) {
            $newDomain = $this->io->ask('add domain:');
            $newDomain = trim($newDomain);
            if ('' === $newDomain) {
                break;
            }
            $domains[] = $newDomain;
        }
        file_put_contents($pathToDomains, implode(PHP_EOL, $domains));
        $this->io->writeln('the domain configuration was written to "' . $pathToDomains . '". you can easily add more by editing that file.');

        return $this;
    }


    /**
     * @return $this
     */
    protected function outputResultsToConsole()
    {
        if (true === $this->skipConsole) {
            return $this;
        }
        (new \Certwatch\Generator\ConsoleGenerator())
            ->setIo($this->io)
            ->setResults($this->runner->getResults())
            ->generate()
        ;

        return $this;
    }


    /**
     * @return $this
     */
    protected function generateXML()
    {
        if (true === $this->skipFiles || true === $this->skipXml) {
            return $this;
        }
        (new \Certwatch\Generator\XMLGenerator())
            ->setIo($this->io)
            ->setResults($this->runner->getResults())
            ->generate()
        ;

        return $this;
    }


    /**
     * @return $this
     */
    protected function generateJSON()
    {
        if (true === $this->skipFiles || true === $this->skipJson) {
            return $this;
        }
        (new \Certwatch\Generator\JSONGenerator())
            ->setIo($this->io)
            ->setResults($this->runner->getResults())
            ->generate()
        ;

        return $this;
    }


    /**
     * @return $this
     */
    protected function generateHTML()
    {
        if (true === $this->skipFiles || true === $this->skipHtml) {
            return $this;
        }
        (new \Certwatch\Generator\HTMLGenerator())
            ->setIo($this->io)
            ->setResults($this->runner->getResults())
            ->generate()
        ;

        return $this;
    }


    /**
     * @return $this
     */
    protected function sendMail()
    {
        if (true === $this->skipMail) {
            return $this;
        }
        try {
            (new \Certwatch\Generator\MailGenerator())
                ->setIo($this->io)
                ->setResults($this->runner->getResults())
                ->generate()
            ;
        } catch (\Exception $exception) {

        }

        return $this;
    }
});
$application->setDefaultCommand('user manager', true);
$application->run();
exit(0);
