<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all caches';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('route:clear'); // Remove the route cache file
        Artisan::call('view:clear'); // Clear all compiled view files
        Artisan::call('config:clear'); // Remove the configuration cache file
        Artisan::call('cache:clear'); // Flush the application cache
        Artisan::call('clear-compiled'); // Remove the compiled class file
//        Artisan::call('auth:clear-resets'); // Flush expired password reset tokens
        Artisan::call('config:cache'); // Create Configuration a cache file for faster configuration loading
        Artisan::call('route:cache'); // Create Route a cache file for faster configuration loading
        shell_exec('composer dump-autoload --optimize');
        $this->line('<fg=green>All Caches Cleared & Autoload Dumped.</>');
    }
}
