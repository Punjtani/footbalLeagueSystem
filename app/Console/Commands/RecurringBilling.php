<?php

namespace App\Console\Commands;

use App\Subscription;
use Illuminate\Console\Command;


class RecurringBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:billing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change All Active Status to orange for admin';

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
        Subscription::where('status', 'active')->update(['is_first_of_month' => true]);
    }
}
