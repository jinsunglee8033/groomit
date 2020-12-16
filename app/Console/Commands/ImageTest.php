<?php

namespace App\Console\Commands;

use App\Lib\ImageProcessor;
use App\Model\PetPhoto;
use Illuminate\Console\Command;

class ImageTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:test';

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
        $file_path = "/Users/yongj/Pictures/image_from_camera.JPG";
        $contents = file_get_contents($file_path);
        $new_img = ImageProcessor::optimize($contents);

        $o = PetPhoto::find(1);
        if (!empty($o)) {
            $o->photo = $new_img;
            $o->save();
        }
    }
}
