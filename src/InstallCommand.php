<?php
namespace WebProjectInstaller\Console;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
class InstallCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Install scaffold for web project.')
            ->addArgument('name', InputArgument::OPTIONAL);
    }
    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->verifyApplicationDoesntExist(
            $directory = ($input->getArgument('name')) ? getcwd().'/'.$input->getArgument('name') : getcwd()
        );

        $output->writeln('<info>Welcome to the web project installer.</info>');

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            '<question>Please choose your framework backend</question>',
            ['laravel', 'symfony']
        );
        $framework = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: ' . $framework);

        $process = new Process($this->getCommandInstallFramework($framework, $input->getArgument('name')), $directory, null, null, null);
        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }
        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    private function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }
        return 'composer';
    }

    private function getCommandInstallFramework($framework, $name)
    {
        $composer = $this->findComposer();
        switch ($framework) {
            case 'laravel':
                return $composer.' create-project laravel/laravel --prefer-dist '.$name;
                break;
            case 'symfony':
                return $composer.' create-project symfony/framework-standard-edition '.$name;
        }
    }
}