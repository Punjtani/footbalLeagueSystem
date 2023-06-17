<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Player;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 1; $i <= 100; $i++) {
            $team_id = random_int(1, 5);
            DB::table('players')->insert([
                'name' => '{"en":"Player ' . $i . ' Team ' . $team_id . '","fr":"Player ' . $i . ' Team ' . $team_id . '"}',
                'country' => 'pk',
                'dob' => '31 March, 2020',
                'status' => Player::STATUS_PUBLISH,
                'image' => '1592218096.jpg',
                'jersey' => 10,
                'playing_position' => 'gk',
                'created_at' => Carbon::now(),
            ]);
            $player_id = DB::getPdo()->lastInsertId();
            DB::table('player_season_team')->insert([
                'player_id' => $player_id,
                'season_id' => 14,
                'team_id' => $team_id,
                'created_at' => Carbon::now(),
            ]);
            DB::table('player_team')->insert([
                'player_id' => $player_id,
                'team_id' => $team_id,
                'joined_on' => Carbon::yesterday(),
                'created_at' => Carbon::now(),
            ]);

            DB::table('club_player')->insert([
                'player_id' => $player_id,
                'club_id' => random_int(1, 4),
                'joined_on' => Carbon::yesterday(),
                'created_at' => Carbon::now(),
            ]);


        }

    }
}
