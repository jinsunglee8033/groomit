<?php

namespace App\Console\Commands;

use App\Lib\ImageProcessor;
use App\Model\PetPhoto;
use Illuminate\Console\Command;

class ImageOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:optimize';

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
        ### pet_photo.photo ###

        try {
            $photos = PetPhoto::whereRaw("length(photo) > 0")->get();

            $this->info(' - total ' . count($photos) . ' records.');
            $bar = $this->output->createProgressBar(count($photos));

            if (count($photos) > 0) {
                foreach ($photos as $o) {
                    //$this->info(' - pet photo : ' . $o->photo_id . '...');
                    $o->photo = ImageProcessor::optimize($o->photo);
                    $o->save();

                    $bar->advance();
                }
            }

            $bar->finish();
            $this->info("");
            $this->info(' - finished.');

        } catch (\Exception $ex) {
            $this->error(" - error : " . $ex->getMessage() . ' [' . $ex->getCode() . ' ]');
        }

    }
}
