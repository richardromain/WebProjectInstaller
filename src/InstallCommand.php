<?php
namespace WebProjectInstaller\Console;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
            ->addArgument('name', InputArgument::REQUIRED, 'What the name of your web project?');
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

        $fs = new Filesystem();
        $helper = $this->getHelper('question');
        $output->writeln('<info>Welcome to the web project installer.</info>');

        $question_backend_framework = new ChoiceQuestion(
            '<question>Please choose your backend framework</question>',
            ['laravel', 'symfony']
        );
        $backend_framework = $helper->ask($input, $output, $question_backend_framework);
        $output->writeln('<info>Installation of '.ucfirst($backend_framework).'</info>');

        // Download the scaffold of project type
        $process = new Process($this->getCommandInstallBackendFramework($backend_framework, $input->getArgument('name')), $directory);
        try {
            $process->mustRun(function ($type, $line) use ($output) {
                $output->write($line);
            });
        } catch (ProcessFailedException $e) {
            $output->writeln('<error>Composer install failed.</error>');
            $output->writeln('<error>'.$process->getOutput().'</error>');
        }
        // .bowerrc file is usefull for download bower components in vendor directory
        $fs->copy('src/files/.bowerrc', $directory.'/.bowerrc');

        // Configuration the laravel project
        if ($backend_framework === 'laravel') {
            $backend_framework_installed = new InstallLaravel($input, $output, $helper, $directory);
        }

        $question_frontend_framework = new ChoiceQuestion(
            '<question>Please choose your frontend framework</question>',
            ['Bootstrap', 'Foundation', 'Materialize', 'Material Design Lite', 'AdminLTE']
        );
        $frontend_framework = $helper->ask($input, $output, $question_frontend_framework);
        $this->downloadFrontendFramework($frontend_framework, $directory, $output);
        $backend_framework_installed->installFrontendFramework($frontend_framework);
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

    /**
     * Get the composer command to install a framework
     *
     * @param $backend_framework
     * @param $name
     * @return string
     */
    private function getCommandInstallBackendFramework($backend_framework, $name)
    {
        $composer = $this->findComposer();
        switch ($backend_framework) {
            case 'laravel':
                return $composer.' create-project laravel/laravel --prefer-dist '.$name;
                break;
            case 'symfony':
                return $composer.' create-project symfony/framework-standard-edition '.$name;
        }
    }

    /**
     * Install the frontend framework with bower
     *
     * @param $frontend_framework
     * @param $directory
     * @param $output
     */
    private function downloadFrontendFramework($frontend_framework, $directory, $output)
    {
        switch ($frontend_framework) {
            case 'AdminLTE':
                $process = new Process('bower install adminlte', $directory);
                try {
                    $process->mustRun(function ($type, $line) use ($output) {
                        $output->write($line);
                    });
                } catch (ProcessFailedException $e) {
                    $output->writeln('<error>Installation of AdminLTE failed.</error>');
                    $output->writeln('<error>'.$process->getOutput().'</error>');
                }
                break;
            default:
                $output->writeln('<error>This feature coming soon...</error>');
                break;
        }
    }
}