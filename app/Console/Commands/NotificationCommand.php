<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notice:board';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command runs the notification services and triggers the telegram bot to push out new user';

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
     * @return int
     */
    public function handle()
    {
        
        $this->info('Starting script.');
        $notify = new NotificationService();
        $this->info('Script initiated.');
        $notify->run();

        $this->info('Notification script ran successfully.');
    }
}
