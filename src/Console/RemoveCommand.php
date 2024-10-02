<?php

namespace Sitroz\LaraBridge\Console;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sitroz\LaraBridge\LaraBridge;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RemoveCommand extends BaseCommand
{
    const SUCCESS = 0;
    protected $signature = 'laraBridge:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove package (should execute before removing by composer)";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->isInstalled()){
            if ($this->confirm('We detected our scripts. Would you like to remove package?')){
                return $this->handleRemove();
            }
        }else {
            $this->line('LaraBridge is not loaded. Something went wrong');
        }

        return self::SUCCESS;
    }

    private function handleRemove()
    {
        $this->line('Run remove');

        $this->handleAppFile();

        $this->handleAppConfigFile();

        $this->removePackageUsingComposer();

        return self::SUCCESS;
    }

    private function findFile($directory, $pattern)
    {
        // Используем рекурсивный итератор для обхода всей директории
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        // Проходим по всему итератору
        foreach ($iterator as $fileInfo) {
            // Проверяем, соответствует ли имя файла заданному шаблону
            if (fnmatch($pattern, $fileInfo->getFilename())) {
                return $fileInfo->getPathname();
            }
        }

        return null;
    }

    private function handleAppFile()
    {
        $appFilePath = base_path('/bootstrap/app.php');

        if (!str_contains(file_get_contents($appFilePath), 'LaraBridge::init')) {
            return;
        }

        $backupFilePath = $this->findFile(base_path('/bootstrap'), 'app.php.larabridge-*.bak');

        $detailString = sprintf(
            'Search backup file for [%s]',
            str_replace(base_path() . '/', '', realpath($appFilePath))
        );
        if ($backupFilePath !== NULL) {
            $this->twoColumnDetail($detailString, '<fg=green;options=bold>DONE</>');

            $raw = file_get_contents($appFilePath);
            $this->restoreFromBackUpFile($appFilePath, $backupFilePath);

            if ($this->runApplicationTest()){
                $this->twoColumnDetail('Restore from backup file', '<fg=green;options=bold>DONE</>');
                return;

            }

            $this->twoColumnDetail('Restore from backup file', '<fg=green;options=bold>ERROR</>');
            file_put_contents($appFilePath, $raw);

        } else {
            $this->twoColumnDetail($detailString, '<fg=reg;options=bold>NOT FOUND</>');
        }

        while (str_contains(file_get_contents($appFilePath), 'LaraBridge::init')){
            $this->error(
                "Before continue you need to delete string '\Sitroz\LaraBridge\LaraBridge::init(\$app);' " .
                "from 'bootstrap/app.php' manually"
            );

            while(!$this->ask('Is it complete?')){
                if ($this->ask('Break uninstallation (not recommended)?')){
                    die;
                }
            }
        }
    }

    private function handleAppConfigFile()
    {
        $appConfigFilePath = base_path('/config/app.php');
        $raw = $content = file_get_contents($appConfigFilePath);

        if (!str_contains($content, 'LaraBridgeServiceProvider::class')) {
            return;
        }

        $this->newLine();
        $this->line('Removing LaraBridgeServiceProvider from config/app.php');

        $variants = [
            'Sitroz\LaraBridge\LaraBridgeServiceProvider::class,',
            'Sitroz\LaraBridge\LaraBridgeServiceProvider::class',
            'LaraBridgeServiceProvider::class,',
            'LaraBridgeServiceProvider::class',
            'use Sitroz\LaraBridge\LaraBridgeServiceProvider;'
        ];

        $content = str_replace($variants, '', $content);
        file_put_contents($appConfigFilePath, $content);

        if ($this->runApplicationTest()){
            $this->info("Config file 'config/app.php' modified successfully");
        }else{
            $this->error("Config file 'config/app.php' modified with errors -> rollback");
            file_put_contents($appConfigFilePath, $raw);

        }

        while (str_contains(file_get_contents($appConfigFilePath), 'LaraBridgeServiceProvider::class')){
            $this->error("You need to delete 'LaraBridgeServiceProvider::class' from 'config/app.php' manually");

            while(!$this->ask('Is it complete?')){
                if ($this->ask('Break uninstallation (not recommended)?')){
                    die;
                }
            }
        }
    }

    private function removePackageUsingComposer()
    {
        if (!$this->ask("Would you like to run command 'composer remove laravel-legacy-bridge'?")){
            $this->line("In this case you need to run 'composer remove laravel-legacy-bridge' later to complete uninstallation");
            return;
        }

        // Определяем базовую директорию приложения
        $basePath = base_path();

        // Создаем новый процесс для выполнения команды
        $process = new Process(['composer', 'remove', 'laravel-legacy-bridge']);
        $process->setWorkingDirectory($basePath);
        $process->setTimeout(null);

        try {
            // Запускаем процесс
            $process->mustRun();

            // Выводим результат выполнения команды
            $this->line($process->getOutput());
        } catch (ProcessFailedException $exception) {
            // Выводим сообщение об ошибке
            $this->error('The process has been failed.');
            $this->error($exception->getMessage());
        }
    }
}
