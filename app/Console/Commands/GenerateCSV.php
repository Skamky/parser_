<?php

namespace App\Console\Commands;

use App\Http\Controllers\SendController;
use Illuminate\Console\Command;

class GenerateCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-csv {full_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация csv файла из данных с сайта full_path - полный путь с изменением генерируемого файла';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data    = SendController::send();
        print('Generate file');
        $file     = fopen($this->argument('full_path'), 'w');
        $columsns = [
            'Condition',
            'google_product_category',
            'store_code',
            'vehicle_fulfillment(option:store_code)',
            'Brand',
            'Model',
            'Year',
            'Color',
            'Mileage',
            'Price',
            'VIN',
            'image_link',
            'link_template',
        ];
        fputcsv($file,$columsns,"\t");
        foreach ($data as $row) {
            fputcsv($file,$row,"\t");
        }
        fclose($file);
    }
}
