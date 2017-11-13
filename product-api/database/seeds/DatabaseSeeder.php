<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        $product = app()->make('App\Product');
        $product->fill([
            'name' => 'Laptop 17 inch murah banget',
            'desc' => 'Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet.',
            'stock' => 5,
            'price' => 15000000,
            'weight' => 3000,
            'sold' => 0,
        ]);
        $product->save();
    }
}
