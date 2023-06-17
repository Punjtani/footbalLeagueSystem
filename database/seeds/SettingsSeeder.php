<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'logo' => '/front-end/images/logo.png',
            'favicon' => '/front-end/images/logo.ico',
            'title' => 'Football Hub',
            'url' => 'www.myfootbalhub.com',
            'about' => 'To drive the ecosystem of football in Malaysia by establishing top tier football facilities. Through this, making football more accessible for the people ultimately creating unity through our beloved common sport. ',
            'address' => 'Lot 43495, Persiaran Oleander, Telok Panglima Garang, Selangor',
            'phone' => '+6011-2627-3241',
            'email' => 'admin@footballhub.com.my',
            'footer' => 'Footballhub Sdn Bhd (1290003-V). All Rights Reserved',
            'social_links' => '',
            'background' => '/front-end/images/main-slider-img.jpg',
            'is_otp_enable' => 1,
            'forgot_password_attempt' => 2
        ]);
    }
}
