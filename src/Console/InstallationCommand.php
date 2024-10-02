<?php

namespace Sitroz\LaraBridge\Console;

use Illuminate\Console\Command;
use Sitroz\LaraBridge\LaraBridge;
use Sitroz\LaraBridge\LaraBridgeServiceProvider;
use Symfony\Component\Console\Exception\RuntimeException;

class InstallationCommand extends BaseCommand
{
    const SUCCESS = 0;

    protected $signature = 'laraBridge:install {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Install scripts and publish files interactively";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->isInstalled()){
            $this->info('We detected our scripts. There is nothing to do');
        }elseif ($this->confirm('Would you like to install package?', TRUE)){
            return $this->handleInstallation();
        }

        return self::SUCCESS;
    }

    private function handleInstallation()
    {
        $this->line('Run install');
        $this->call('vendor:publish', [
            '--provider' => LaraBridgeServiceProvider::class,
            '--force' => $this->option('force'),
        ]);

        $this->info('Modifying files');

        $this->modifyAppFile();

        return self::SUCCESS;
    }

    private function modifyAppFile()
    {
        $path = base_path('/bootstrap/app.php');
        $realPath = realpath($path);
        $raw = file_get_contents($path);

        if ($this->ensureAppFileModified($raw, $realPath)){
            return;
        }

        $backupFilePath = $this->createBackupFile($raw, $path, $realPath);
        $this->modifyAppContent($raw, $path, $realPath);

        if ($this->runApplicationTest()){
            $this->info('Installation completed successfully');
            return;
        }

        $this->restoreFromBackUpFile($path, $backupFilePath);
        $this->error("Installation failed");
    }

    /**
     * ensure app.php not contains needle code to prevent duplicates
     *
     * @param $raw string file content
     * @param $realPath string realpath to file
     * @return bool TRUE if file modified
     */
    private function ensureAppFileModified ($raw, $realPath)
    {
        if (str_contains($raw, 'LaraBridge::init')){
            if ($this->isInstalled()){
                $this->twoColumnDetail(sprintf(
                    'File [%s] already modified',
                    str_replace(base_path().'/', '', $realPath)
                ), '<fg=yellow;options=bold>SKIPPED</>');
                return TRUE;
            }

            $this->twoColumnDetail(sprintf(
                'Modifying file [%s]',
                str_replace(base_path().'/', '', $realPath)
            ), '<fg=red;options=bold>ERROR</>');

            $this->newLine(2);

            $this->error(
                'The file has already been modified, ' .
                'but the package has not been initialized.');
            $this->newLine(2);
            $this->line('The code may be commented out.');
            $this->line('You need check the file '. realpath(base_path('/bootstrap/app.php')));
            $this->newLine();
            $this->line('To use LaraBridge package app.php should contains such code:');
            $this->line("if (class_exists(\Sitroz\LaraBridge\LaraBridge::class)){\n".
                '    \Sitroz\LaraBridge\LaraBridge::init($app);'."\n".
                "}");
            return TRUE;

        }

        return FALSE;
    }

    public function modifyAppContent($raw, $path, $realPath)
    {
        $lastPos = (int) strrpos($raw, 'return $app;');

        // find previous code string
        $offset = (0 - strlen($raw) + $lastPos);
        $pastePosition = max(
                strrpos($raw, ";\n", $offset),
                strrpos($raw, "}\n", $offset)
            ) + 2;

        $newContent = substr($raw, 0, $pastePosition);
        $newContent.= file_get_contents(__DIR__.'/../../bootstrap/app_extra_code');
        $newContent.= substr($raw, $pastePosition);
        file_put_contents($path, $newContent);

        $this->twoColumnDetail(sprintf(
            'Modifying file [%s]',
            str_replace(base_path().'/', '', $realPath)
        ), '<fg=green;options=bold>DONE</>');
    }
}
