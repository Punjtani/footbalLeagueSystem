<?php

use Illuminate\Database\Seeder;

class MembershipLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('membership_levels')->truncate();

        DB::table('membership_levels')->insert([
            'name' => '{"en":"Silver"}',
            'discount_type'=> 'Fixed',
            'discount_value'=> '0',
            'no_of_bookings'=> '0',
            'weight'=> 1,
        ]);
        DB::table('membership_levels')->insert([
            'name' => '{"en":"Gold"}',
            'discount_type'=> 'Percentage',
            'discount_value'=> '10',
            'no_of_bookings'=> '10',
            'weight'=> 2,
        ]);
        DB::table('membership_levels')->insert([
            'name' =>'{"en":"Platinum"}',
            'discount_type'=> 'Percentage',
            'discount_value'=> '20',
            'no_of_bookings'=> '20',
            'weight'=> 3,
        ]);

        DB::table('membership_levels')->insert([
            'name' =>'{"en":"Diamond"}',
            'discount_type'=> 'Percentage',
            'discount_value'=> '30',
            'no_of_bookings'=> '30',
            'weight'=> 4,
        ]);

        DB::table('membership_levels')->insert([
            'name' =>'{"en":"Uranium"}',
            'discount_type'=> 'Percentage',
            'discount_value'=> '50',
            'no_of_bookings'=> '40',
            'weight'=> 5,
        ]);
    }
}
