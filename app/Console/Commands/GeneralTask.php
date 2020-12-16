<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneralTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'general:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            $ret = DB::statement("
                update product_denom
                set denom = if(group_id = 1, 15, 10),
                    min_denom = if(group_id = 1, 15, 10),
                    max_denom = if(group_id = 1, 15, 10)
                where prod_id = 11
            ");

            if ($ret < 1) {
                throw new \Exception('query reuslt : ' . $ret);
            }

            Helper::send_mail('tech@groomit.me', '[' . getenv('APP_ENV') . "] Mother's day promtion revert success.", '');
        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            $this->error($msg);
            Helper::send_mail('tech@groomit.me', '[' . getenv('APP_ENV') . "] Mother's day promtion revert error.", $msg);
        }
    }
}
