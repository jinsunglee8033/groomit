<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SendReminderEmail::class,
        Commands\SendReminderEmailCA::class,
        Commands\CheckGroomerOnTheWay::class,
        Commands\GeneralTask::class,
        Commands\HoldCC::class,
        Commands\ImageOptimize::class,
        Commands\ImageTest::class,
        Commands\HourlyJob::class,
        Commands\AssignCheck::class,
        Commands\NotifyGroomer2::class,
        Commands\UserStat::class,
        Commands\SpecialPromotion::class,
        Commands\Spooky::class,
    ];
//Commands\NotifyGroomer::class,
//Commands\Inspire::class,
//Commands\SpecialPromotion::class,
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('send:reminder')
                 ->timezone('America/New_York')
                 ->dailyAt('08:00');

        $schedule->command('spooky:promotion')
            ->timezone('America/New_York')
            ->dailyAt('09:01');

//        $schedule->command('special:promotion')
//            ->timezone('America/New_York')
//            ->dailyAt('17:47');

        $schedule->command('send:reminder_ca')
                ->timezone('America/New_York')
                ->dailyAt('11:00');

        $schedule->command('check:groomer_on_the_way')
                 ->timezone('America/New_York')
                 ->everyTenMinutes();

        $schedule->command('hold:cc')
            ->timezone('America/New_York')
            ->everyFiveMinutes();

//        $schedule->command('general:task')
//            ->timezone('America/New_York')
//            ->cron("00 00 15 05 *");

        $schedule->command('job:hourly')
            ->timezone('America/New_York')
            ->hourly();

        //Notices to admin if it's not assigned within 30 mins or 60 mins.
        $schedule->command('assign:check')
            ->timezone('America/New_York')
            ->everyFiveMinutes();

        //Notice to customer if it's yesterday's, but not assigned yet
        $schedule->command('assign:check:daily')
          ->timezone('America/New_York')
          ->dailyAt('07:00');

        //Need to be removed 02/04/2020.
//        $schedule->command('groomer:notify')
//            ->timezone('America/New_York')
//            ->everyFiveMinutes();

        //Notify to groomers 3/12/24 minutes later to next level of groups.
        $schedule->command('groomer:notify2')
            ->timezone('America/New_York')
            ->cron('*/3 * * * *');  //At every three minutes

        $schedule->command('user:stat')
          ->timezone('America/New_York')
          ->dailyAt('04:00');

    }
}
