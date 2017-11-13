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
        $coupon = app()->make('App\Coupon');
        $coupon->fill([
            'code_name' => 'HARBOLNAS11',
            'desc' => 'Potongan 11000 langsung tanpa syarat',
            'qty' => 100,
            'valid_start' => '2017-11-14',
            'valid_till' => '2017-11-30',
            'discount' => 11000,
        ]);
        $coupon->save();
    }
}
