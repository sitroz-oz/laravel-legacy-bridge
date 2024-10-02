<?php

namespace Sitroz\LaraBridge\Console;

use Illuminate\Console\Command;
use Sitroz\LaraBridge\LaraBridge;

abstract class BaseCommand extends Command
{
    /**
     * Check if the application is installed by determining if a specific key is bound in the Laravel application container.
     *
     * @return bool True if the application is installed, false otherwise.
     */
    protected function isInstalled()
    {
        return app()->bound(LaraBridge::BOUND_KEY);
    }

    protected function runLaraBridgeTest()
    {
        $command = 'php ' . realpath(base_path('artisan')) . " ".TestCommand::SIGNATURE." --test";
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->twoColumnDetail("Test Laravel application with LaraBridge", '<fg=green;options=bold>SUCCESS</>');
            return TRUE;
        }

        $this->twoColumnDetail("Test Laravel application with LaraBridge", '<fg=red;options=bold>ERROR</>');
        return FALSE;
    }

    protected function runApplicationTest()
    {
        $command = 'php ' . realpath(base_path('artisan')) . " list";
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->twoColumnDetail("Test Laravel application", '<fg=green;options=bold>SUCCESS</>');
            return TRUE;
        }

        $this->twoColumnDetail("Test Laravel application", '<fg=red;options=bold>ERROR</>');
        return FALSE;
    }

    protected function createBackupFile($raw, $path)
    {
        $backupFile = $path.'.larabridge-'.time().'.bak';

        file_put_contents($backupFile, $raw);

        $this->twoColumnDetail(sprintf(
            'Backup file [%s] to [%s]',
            str_replace(base_path().'/', '', realpath($path)),
            str_replace(base_path().'/', '', realpath($backupFile))
        ), '<fg=green;options=bold>DONE</>');

        return $backupFile;
    }

    protected function restoreFromBackUpFile($path, $backupFilePath)
    {
        $raw = file_get_contents($backupFilePath);

        file_put_contents($path, $raw);
        $this->twoColumnDetail(sprintf(
            'Restore [%s] from backup [%s]',
            str_replace(base_path().'/', '', realpath($path)),
            str_replace(base_path().'/', '', realpath($backupFilePath))
        ), '<fg=yellow;options=bold>SUCCESS</>');

        if ($this->runApplicationTest()){

            $firstDetailStr = sprintf('Backup file [%s]',str_replace(
                base_path().'/', '', realpath($backupFilePath)
            ));
            if (unlink($backupFilePath)){
                $this->twoColumnDetail($firstDetailStr, '<fg=yellow;options=bold>REMOVED</>');
            }else{
                $this->twoColumnDetail($firstDetailStr, '<fg=red;options=bold>ERROR</>');
            }
        }
    }

    protected function newLine($lines = 1)
    {
        for ($i = 0; $i < $lines; $i++) {
            $this->line('');
        }
    }

    protected function twoColumnDetail($left, $right)
    {
        // Determining the current operating system
        if (strpos(PHP_OS, 'WIN') === 0) {
            // For Windows, set a fixed terminal width
            $terminalWidth = 180;
        } else {
            // For Unix-like systems we use tput to determine the terminal width
            $terminalWidth = (int) exec('tput cols');
        }

        // Calculate positions for both columns
        $leftWidth = strlen(strip_tags($left));
        $rightWidth = strlen(strip_tags($right));

        // Number of spaces between columns
        $spacingWidth = $terminalWidth - $leftWidth - $rightWidth;

        if ($spacingWidth < 1) {
            $spacingWidth = 1;
        }

        // Forming a line with two columns
        $detailString = sprintf(
            "%s%" . $spacingWidth . "s%s",
            $left,
            '',
            $right
        );

        $this->getOutput()->writeln($detailString);
    }
}
