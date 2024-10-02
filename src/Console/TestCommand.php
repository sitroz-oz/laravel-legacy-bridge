<?php

namespace Sitroz\LaraBridge\Console;

use Illuminate\Console\Command;
use Sitroz\LaraBridge\LaraBridge;
use Sitroz\LaraBridge\LaraBridgeServiceProvider;
use Symfony\Component\Console\Exception\RuntimeException;

class TestCommand extends BaseCommand
{
    const SUCCESS = 0;
    const SIGNATURE = 'laraBridge:test';

    protected $signature = self::SIGNATURE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Ensure Laravel application loads well with LaraBridge package";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->isInstalled()){
            $this->line("<fg=green;options=bold>Success: Package initialized</>");
            return self::SUCCESS;
        }

        throw new RuntimeException('Artisan works well, but the package has not been initialized');
    }
}
