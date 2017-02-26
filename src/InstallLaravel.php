<?php
namespace WebProjectInstaller\Console;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InstallLaravel {
    private $input;
    private $output;
    private $helper;
    private $directory;
    private $auth;
    private $fs;
    private $finder;

    /**
     * InstallLaravel constructor.
     * @param $input
     * @param $output
     * @param $helper
     * @param $directory
     */
    public function __construct($input, $output, $helper, $directory)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->directory = $directory;
        $this->auth = false;
        $this->fs = new Filesystem();
        $this->finder = new Finder();

        $this->configureEnvLaravel();
        $this->enableAuthFeatureLaravel();
    }

    /**
     * Function to configure the laravel app (.env)
     */
    private function configureEnvLaravel()
    {
        $process = new Process('npm install', $this->directory);
        try {
            $process->mustRun(function ($type, $line) {
                $this->output->write($line);
            });
        } catch (ProcessFailedException $e) {
            $this->output->writeln('<error>Npm install failed.</error>');
            $this->output->writeln('<error>'.$process->getOutput().'</error>');
        }
        $config = [];
        $this->output->writeln('<info>Configuration of Laravel.</info>');

        $question_driver_db = new Question('<question>Enter the database driver</question> <comment>[mysql]</comment> : ', 'mysql');
        $driver_db = $this->helper->ask($this->input, $this->output, $question_driver_db);
        $config['driver_db'] = $driver_db;

        $question_host_db = new Question('<question>Enter the database host</question> <comment>[127.0.0.1]</comment> : ', '127.0.0.1');
        $host_db = $this->helper->ask($this->input, $this->output, $question_host_db);
        $config['host_db'] = $host_db;

        $question_port_db = new Question('<question>Enter the database port</question> <comment>[3306]</comment> : ', '3306');
        $port_db = $this->helper->ask($this->input, $this->output, $question_port_db);
        $config['port_db'] = $port_db;

        $question_database_db = new Question('<question>Enter the database name</question> <comment>[homestead]</comment> : ', 'homestead');
        $database_db = $this->helper->ask($this->input, $this->output, $question_database_db);
        $config['database_db'] = $database_db;

        $question_username_db = new Question('<question>Enter the database username</question> <comment>[homestead]</comment> : ', 'homestead');
        $username_db = $this->helper->ask($this->input, $this->output, $question_username_db);
        $config['username_db'] = $username_db;

        $question_password_db = new Question('<question>Enter the database password</question> <comment>[secret]</comment> : ', 'secret');
        $question_password_db->setHidden(true);
        $question_password_db->setHiddenFallback(false);
        $password_db = $this->helper->ask($this->input, $this->output, $question_password_db);
        $config['password_db'] = $password_db;

        if (file_exists($this->directory.'/.env')) {
            $dotenv = $this->directory.'/.env';
        } elseif (file_exists($this->directory.'/laravel/.env')) {
            $dotenv = $this->directory.'/laravel/.env';
        }

        // Rewrite the .env file with the news values
        file_put_contents($dotenv, implode('',
            array_map(function($data) use ($config) {
                if (stristr($data, 'DB_CONNECTION')) {
                    return 'DB_CONNECTION='.$config['driver_db'].PHP_EOL;
                } elseif (stristr($data, 'DB_HOST')) {
                    return 'DB_HOST='.$config['host_db'].PHP_EOL;
                } elseif (stristr($data, 'DB_PORT')) {
                    return 'DB_PORT='.$config['port_db'].PHP_EOL;
                } elseif (stristr($data, 'DB_DATABASE')) {
                    return 'DB_DATABASE='.$config['database_db'].PHP_EOL;
                } elseif (stristr($data, 'DB_USERNAME')) {
                    return 'DB_USERNAME='.$config['username_db'].PHP_EOL;
                } elseif (stristr($data, 'DB_PASSWORD')) {
                    return 'DB_PASSWORD='.$config['password_db'].PHP_EOL;
                } else {
                    return $data;
                }
            }, file($dotenv))
        ));
    }

    /**
     * Function to enable auth Laravel
     */
    private function enableAuthFeatureLaravel()
    {
        $question_enable_auth = new ConfirmationQuestion('<question>Do you want enable auth feature of Laravel?</question> <comment>[Y/n]</comment> ', false);
        if ($this->helper->ask($this->input, $this->output, $question_enable_auth)) {
            $process = new Process('php artisan make:auth', $this->directory);
            try {
                $process->mustRun(function ($type, $line) {
                    $this->output->write($line);
                });
            } catch (ProcessFailedException $e) {
                $this->output->writeln('<error>Installation of auth feature failed.</error>');
                $this->output->writeln('<error>'.$process->getOutput().'</error>');
            }
            $this->auth = true;
            $this->runMigration();
        }
    }

    /**
     * Function to run Laravel migrations
     */
    private function runMigration()
    {
        $process = new Process('php artisan migrate', $this->directory);
        try {
            $process->mustRun(function ($type, $line) {
                $this->output->write('<info>'.$line.'</info>');
            });
        } catch (ProcessFailedException $e) {
            $this->output->writeln('<error>Migrations failed</error>');
            $this->output->writeln('<error>'.$process->getOutput().'</error>');
        }
    }

    public function installFrontendFramework($frontend_framework)
    {
        switch ($frontend_framework) {
            case 'AdminLTE':
                $this->fs->copy('src/files/laravel/adminlte/webpack.mix.js', $this->directory.'/webpack.mix.js', true);
                $process = new Process('npm run dev', $this->directory);
                try {
                    $process->mustRun(function ($type, $line) {
                        $this->output->write($line);
                    });
                } catch (ProcessFailedException $e) {
                    $this->output->writeln('<error>Generating assets failed.</error>');
                    $this->output->writeln('<error>'.$process->getOutput().'</error>');
                }
                if ($this->auth) {
                    /* TODO: Copy of views auth folder in laravel install */
                    $this->finder->files()->in('src/files/laravel/adminlte/views/auth');
                    foreach ($this->finder as $file) {
                        $this->fs->copy($file->getPathname(), $this->directory.'/resources/views/auth/'.$file->getRelativePathname(), true);
                    }
                }
                break;
        }
    }
}