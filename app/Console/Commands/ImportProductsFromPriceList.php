<?php

namespace App\Console\Commands;

use App\Models\Product;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ImportProductsFromPriceList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {--schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from price list';

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
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $json = Storage::disk('local')->get('data/prods.json');
        $data = json_decode($json, true);

        $products = Product::with('attributes')->get();
        $destroyRecords = $this->option('schedule');

        if ($destroyRecords) {
            $productNames = array_map(function ($item) {
                return $item['name'];
            }, $data);

            $destroyRecordIds = $products->filter(function ($product) use ($productNames) {
                return !in_array($product->name, $productNames);
            })->map(function ($product) {
                return $product->id;
            })->toArray();

            Product::destroy($destroyRecordIds);
        }

        foreach ($data as $item) {
            $product = Product::updateOrCreate(['name' => $item['name']], $item);
            foreach ($item['characteristics'] as $name => $value) {
                $product->attributes()->updateOrCreate(['name' => $name], [
                    'name' => $name,
                    'value' => $value,
                ]);
            }
        }
    }
}
