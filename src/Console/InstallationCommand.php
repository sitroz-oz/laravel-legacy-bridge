<?php

namespace Sitroz\LaraBridge\Console;

use Illuminate\Console\Command;
use Sitroz\LaraBridge\LaraBridge;
use Sitroz\LaraBridge\LaraBridgeServiceProvider;
use Symfony\Component\Console\Exception\RuntimeException;

class InstallationCommand extends Command
{
    protected $signature = 'laraBridge
                            {--i|install : Install scripts and publish configuration file}
                            {--force : (For installation) Overwrite any existing files}
                            {--r|remove : Remove package (should execute before removing by composer)}
                            {--test : Ensure Laravel application loads well with LaraBridge package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Installing/removing scripts for a package interactively";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('test')){
            return $this->handleTest();
        }

        if ($this->option('install')){
            return $this->handleInstallation();
        }
        if ($this->option('remove')){
            return $this->handleRemove();
        }

        if ($this->isInstalled()){
            if ($this->confirm('We detected our scripts. Would you like to remove package?', FALSE)){
                return $this->handleRemove();
            }
        }elseif ($this->confirm('Would you like to install package?', TRUE)){
            return $this->handleInstallation();
        }

        return self::SUCCESS;
    }

    private function isInstalled()
    {
        return app()->bound(LaraBridge::BOUND_KEY);
    }

    private function handleTest()
    {
        if ($this->isInstalled()){
            $this->line("<fg=green;options=bold>Success: Package initialized</>");
            return self::SUCCESS;
        }

        throw new RuntimeException('Artisan works well, but the package has not been initialized');
    }

    private function handleInstallation()
    {
        $this->line('Run install');
        $this->call('vendor:publish', [
            '--provider' => LaraBridgeServiceProvider::class,
            '--force' => $this->option('force'),
        ]);

        $this->components->info('Modifying files');
        $this->modifyAppFile();

        return self::SUCCESS;
    }

    private function handleRemove()
    {
        $this->line('Run remove');
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
            $this->components->info('Installation completed successfully');
            return;
        }

        $this->rollbackChanges($path, $raw, $realPath, $backupFilePath);
        $this->components->error("Installation failed");
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
                $this->components->twoColumnDetail(sprintf(
                    'File [%s] already modified',
                    str_replace(base_path().'/', '', $realPath)
                ), '<fg=yellow;options=bold>SKIPPED</>');
                return TRUE;
            }

            $this->components->twoColumnDetail(sprintf(
                'Modifying file [%s]',
                str_replace(base_path().'/', '', $realPath)
            ), '<fg=red;options=bold>ERROR</>');

            $this->newLine();
            $this->newLine();

            $this->components->error(
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

    private function createBackupFile($raw, $path, $realPath)
    {
        $backupFile = str_replace('app.php','app_backup_'.time().'.php', $path);
        file_put_contents($backupFile, $raw);

        $this->components->twoColumnDetail(sprintf(
            'Backup file [%s] to [%s]',
            str_replace(base_path().'/', '', $realPath),
            str_replace(base_path().'/', '', realpath($backupFile))
        ), '<fg=green;options=bold>DONE</>');

        return $backupFile;
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

        $this->components->twoColumnDetail(sprintf(
            'Modifying file [%s]',
            str_replace(base_path().'/', '', $realPath)
        ), '<fg=green;options=bold>DONE</>');
    }

    /**
     * @return bool TRUE on success
     */
    private function runApplicationTest()
    {
        $command = 'php ' . realpath(base_path('artisan')) . ' laraBridge --test';
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->components->twoColumnDetail("Test Laravel application",
                '<fg=green;options=bold>SUCCESS</>'
            );
            return TRUE;
        }

        return FALSE;
    }

    private function rollbackChanges($path, $raw, $realPath, $backupFilePath)
    {
        $this->components->twoColumnDetail("Test Laravel application", '<fg=red;options=bold>ERROR</>');

        file_put_contents($path, $raw);
        $this->components->twoColumnDetail(sprintf(
            'File [%s]',
            str_replace(base_path().'/', '', $realPath)
        ), '<fg=yellow;options=bold>ROLLBACK</>');


        $firstDetailStr = sprintf('File [%s]',str_replace(
            base_path().'/', '', realpath($backupFilePath)
        ));
        if (unlink($backupFilePath)){
            $this->components->twoColumnDetail($firstDetailStr, '<fg=yellow;options=bold>REMOVED</>');
        }else{
            $this->components->twoColumnDetail($firstDetailStr, '<fg=red;options=bold>ERROR</>');
        }
    }
}
