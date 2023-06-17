<?php

use App\Association;
use App\BaseModel;
use App\Staff;
use App\Player;
use App\Stadium;
use App\Team;
use App\Tenant;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Sport;
use App\Season;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        // Sports
        DB::table('sports')->insert([
            'name' => 'Football',
            'sport_name' => 'Football',
            'scoring' => json_encode(array(
                array("goal" => 1)
            ), JSON_THROW_ON_ERROR),
            'stats' => json_encode(array(
                array("name" => "goal", "display_name" => "Goal", "type" => "number", "primary" => true),
                array("name" => "red_card", "display_name" => "Red Card", "type" => "number", "primary" => false),
                array("name" => "yellow_card", "display_name" => "Yellow Card", "type" => "number", "primary" => false),
                array("name" => "possession", "display_name" => "Possession","type" => "text", "primary" => false),
                array("name" => "tackles", "display_name" => "Tackles", "type" => "number", "primary" => false)
            ), JSON_THROW_ON_ERROR),
            'roles' => json_encode(array(
                'player_roles' => array(
                    "qb" => "Quarter Back",
                    "c" => "Center",
                    "rb" => "Running Back",
                    "fb" => "Full Back",
                    "wr" => "Wide Receiver",
                    "te" => "Tight End",
                    "lg" => "Left Guard",
                    "rg" => "Right Guard",
                    "lt" => "Left Tackle",
                    "rt" => "Right Tackle",
                    "dt" => "Defensive Tackle",
                    "de" => "Defensive End",
                    "lb" => "Line Backer",
                    "s" => "Safety",
                    "cb" => "Corner Back",
                    "gk" => "Goal Keeper",
                ),
                'staff_roles' => array(
                    "m" => "Manager",
                    "am" => "Assistant Manager",
                    "gc" => "Goalkeeping Coach",
                    "ca" => "Chief Analyst",
                    "fc" => "Fitness Coach",
                    "cc" => "Conditioning Coach",
                    "n" => "Nutritionist",
                    "p" => "Physiotherapist",
                    "ms" => "Masseur",
                    "s" => "Scout",
                    "km" => "Kit Manager",
                    "ytc" => "Youth Team Coach",
                ),
                'officials' => array(
                    "rf" => "Referee",
                    "um" => "Umpire",
                    "hl" => "Head Linesman",
                    "lj" => "Line Judge",
                    "bj" => "Back Judge",
                    "fj" => "Field Judge",
                    "sj" =>"Side Judge",
                )
            ), JSON_THROW_ON_ERROR),
            'groups' => json_encode(array(
                'team_group' => array(
                    "default" => "Primary Team",
                    "tb" => "Team B",
                    "u19" => "Under-19s",
                ),
            ), JSON_THROW_ON_ERROR),
            'rules' => json_encode(array(
                "points_name" => "goal",
                "match_day_squad" => 19,
                "starting_players" => 11,
                "number_of_subs" => 3,
                "rolling_sub" => false,
                "temp_sub" => false,
                "resub_allowed" => false,
                "total_match_duration" => 90,
                "number_of_halves" => 2,
                "extra_period_allowed" => true,
                "extra_period_name" => "Extra time",
                "number_of_extra_periods" => 2,
                "each_extra_period_time" => 15,
                "stoppage_time_allowed" => true,
                "running_clock" => true,
                "penalties_allowed" => true,
                "home_and_away" => true,
                "single_stage_round_robin" => true,
            ), JSON_THROW_ON_ERROR),
            'created_at' => Carbon::now(),
            'status' => Sport::STATUS_PUBLISH,
        ]);
//        DB::table('sports')->insert([
//            'name' => 'Rugby League',
//            'sport_name' => 'Rugby',
//            'scoring' => json_encode(array(
//                array("goals" => 2),
//                array("try" => 4),
//                array("conversion" => 1),
//                array("penalty" => 2)
//            ), JSON_THROW_ON_ERROR),
//            'stats' => json_encode(array(
//                array("name" => "try", "type" => "int", "primary" => true),
//                array("name" => "conversion", "type" => "int", "primary" => true),
//                array("name" => "goal", "type" => "int", "primary" => true),
//                array("name" => "penalty", "type" => "int", "primary" => true),
//                array("name" => "red_card", "type" => "int", "primary" => false),
//                array("name" => "yellow_card", "type" => "int", "primary" => false),
//                array("name" => "possession", "type" => "percentage", "primary" => false)
//            ), JSON_THROW_ON_ERROR),
//            'roles' => json_encode(array(
//                'player_roles' => array(
//                    "fb" => "Full Back",
//                    "lwt" => "Left Wing Threequarter",
//                    "lct" => "Left Centre Threequarter",
//                    "rct" => "Right Centre Threequarter",
//                    "rwt" => "Right Wing Threequarter",
//                    "soh" => "Stand-off Half",
//                    "sh" => "Scrum Half",
//                    "p" => "Prop",
//                    "h" => "Hooker",
//                    "frf" => "Front Row Forward",
//                    "srlef" => "Second Row Left Edge Forward",
//                    "srref" => "Second Row Right Edge Forward",
//                    "lf" => " Lock Forward",
//                ),
//                'staff_roles' => array(
//                    "hc" => "Head Coach",
//                    "dc" => "Defence Coach",
//                    "fc" => "Forward Coach",
//                    "aac" => "Assistant Attack Coach",
//                    "tc" => "Transition Coach",
//                    "hsc" => "Head of Strength & Conditioning",
//                    "scc" => "Strength & Conditioning Coach",
//                    "sci" => "Strength & Conditioning Intern",
//                    "hpa" => "Head Performance Analyst",
//                    "pa" => "Performance Analyst",
//                    "hp" => "Head of Physiotherapy",
//                    "ftp" => "First Team Physiotherapist",
//                    "stp" => "Soft Tissue Physiotherapist",
//                    "pi" => "Physio Intern",
//                    "tm" => "Team Manager",
//                    "ra" => "Rugby Administrator",
//                    "km" => "Kit Manager",
//                    "pc" => "Performance Chef",
//                    "ka" => "Kitchen Assistant",
//                )
//            ), JSON_THROW_ON_ERROR),
//            'rules' => json_encode(array(
//                "points_name" => "points",
//                "match_day_squad" => 23,
//                "starting_players" => 13,
//                "number_of_subs" => 10,
//                "rolling_sub" => false,
//                "temp_sub" => true,
//                "resub_allowed" => false,
//                "total_match_duration" => 80,
//                "number_of_halves" => 2,
//                "extra_period_allowed" => true,
//                "extra_period_name" => "Extra time",
//                "number_of_extra_periods" => 2,
//                "each_extra_period_time" => 10,
//                "stoppage_time_allowed" => false,
//                "running_clock" => true,
//                "penalties_allowed" => false,
//                "home_and_away" => false,
//                "single_stage_round_robin" => false,
//            ), JSON_THROW_ON_ERROR),
//            'created_at' => Carbon::now(),
//            'status' => Sport::STATUS_PUBLISH,
//        ]);
//        DB::table('sports')->insert([
//            'name' => 'Rugby union',
//            'sport_name' => 'Rugby',
//            'scoring' => json_encode(array(
//                array("goals" => 3),
//                array("try" => 5),
//                array("conversion" => 2)
//            ), JSON_THROW_ON_ERROR),
//            'stats' => json_encode(array(
//                array("name" => "try", "type" => "int", "primary" => true),
//                array("name" => "conversion", "type" => "int", "primary" => true),
//                array("name" => "goal", "type" => "int", "primary" => true),
//                array("name" => "red_card", "type" => "int", "primary" => false),
//                array("name" => "yellow_card", "type" => "int", "primary" => false),
//                array("name" => "possession", "type" => "percentage", "primary" => false)
//            ), JSON_THROW_ON_ERROR),
//            'roles' => json_encode(array(
//                'player_roles' => array(
//                    "h" => "Hooker",
//                    "lhp" => "Loose-head Prop",
//                    "thp" => "Tight-head Prop",
//                    "l" => "Lock",
//                    "osf" => "Open-side flanker",
//                    "bsf" => "Blind-side flanker",
//                    "n" => "Number 8",
//                    "lw" => "Left Wing",
//                    "sh" => "Scrum Half",
//                    "fb" => "Full Back",
//                    "fh" => "Fly Half",
//                    "ic" => "Inside Centre",
//                    "oc" => "Outside Centre",
//                    "rw" => "Right Wing",
//                ),
//                'staff_roles' => array(
//                    "hc" => "Head Coach",
//                    "dc" => "Defence Coach",
//                    "fc" => "Forward Coach",
//                    "aac" => "Assistant Attack Coach",
//                    "tc" => "Transition Coach",
//                    "hsc" => "Head of Strength & Conditioning",
//                    "scc" => "Strength & Conditioning Coach",
//                    "sci" => "Strength & Conditioning Intern",
//                    "hpa" => "Head Performance Analyst",
//                    "pa" => "Performance Analyst",
//                    "hp" => "Head of Physiotherapy",
//                    "ftp" => "First Team Physiotherapist",
//                    "stp" => "Soft Tissue Physiotherapist",
//                    "pi" => "Physio Intern",
//                    "tm" => "Team Manager",
//                    "ra" => "Rugby Administrator",
//                    "km" => "Kit Manager",
//                    "pc" => "Performance Chef",
//                    "ka" => "Kitchen Assistant",
//                )
//            ), JSON_THROW_ON_ERROR),
//            'rules' => json_encode(array(
//                "points_name" => "points",
//                "match_day_squad" => 23,
//                "starting_players" => 15,
//                "number_of_subs" => 8,
//                "rolling_sub" => false,
//                "temp_sub" => true,
//                "resub_allowed" => false,
//                "total_match_duration" => 80,
//                "number_of_halves" => 2,
//                "extra_period_allowed" => true,
//                "extra_period_name" => "Extra time",
//                "number_of_extra_periods" => 2,
//                "each_extra_period_time" => 10,
//                "stoppage_time_allowed" => false,
//                "running_clock" => true,
//                "penalties_allowed" => false,
//                "home_and_away" => true,
//                "single_stage_round_robin" => false,
//            ), JSON_THROW_ON_ERROR),
//            'created_at' => Carbon::now(),
//            'status' => Sport::STATUS_PUBLISH,
//        ]);
        // Super Admin
        DB::table('admins')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@footballhub.my',
            'password' => Hash::make('admin123'),
            'status' => User::STATUS_PUBLISH,
            'created_at' => Carbon::now(),
        ]);

        // Tenant with its Admin
        DB::table('tenants')->insert([
            'name' => 'NextTv',
            'email' => 'admin@example.com',
            'sport_id' => 1,
            'status' => Tenant::STATUS_PUBLISH,
            'created_at' => Carbon::now(),
        ]);
        DB::table('admins')->insert([
            'name' => 'An Admin',
            'email' => 'anotheradmin@example.com',
            'tenant_id' => 1,
            'password' => Hash::make('anotheradmin123'),
            'status' => User::STATUS_PUBLISH,
            'created_at' => Carbon::now(),
        ]);
        // Associations
        DB::table('associations')->insert([
            'name' => '{"en":"FIFA","fr":"FIFA"}',
            'country' => 'pk',
            'status' => Association::STATUS_PUBLISH,
            'image' => '1592218057.jpg',
            'created_at' => Carbon::now(),
        ]);
        // Tournaments
        DB::table('tournaments')->insert([
            'name' => '{"en":"Kemuning Football League 5.0"}',
            'description' => '{"en":"' . Str::random(15) . '","fr":"' . Str::random(15) . '"}',
            'team_group' => 'default',
            'status' => 1,
            'occurrence' => 4,
            'association_id' => 1,
            'since' => '31 March, 2020',
            'image' => '1592218048.jpg',
            'created_at' => Carbon::now(),
        ]);
        //Season Templates
        DB::table('season_templates')->insert([
            'name' => 'Sersaw',
            'type' => 0,
            'number_of_teams' => 8,
            'number_of_stages' => 3,
            'configuration' => '{"stage_1":{"name":"Stage 1","type":"0","home_and_away":"1"},"stage_2":{"name":"Stage 2","type":"0","home_and_away":"0"},"stage_3":{"name":"Stage 3","type":"0","home_and_away":"1"}}',
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);
        // Seasons
        DB::table('seasons')->insert([
            'name' => '{"en":"Season 2020","fr":"Season 2020"}',
            'tournament_id' => 1,
            "season_template_id" => 1,
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);
        DB::table('seasons')->insert([
            'name' => '{"en":"Test Season 2","fr":"Test Season 2"}',
            'tournament_id' => 1,
            "season_template_id" => 1,
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);
        // Stages

//        DB::table('stages')->insert([
//            'name' => '{"en":"Semi Final","fr":"Semi Final"}',
//            'season_id' => 1,
//            'stage_number' => 1,
//            'type' => Season::STAGE_TYPE_ROUND_ROBIN,
//            'configuration' => '{"seeding":"3","round_robin_type":"2"}',
//            'created_at' => Carbon::now(),
//        ]);
//        DB::table('stages')->insert([
//            'name' => '{"en":"Final","fr":"Final"}',
//            'season_id' => 1,
//            'stage_number' => 2,
//            'type' => Season::STAGE_TYPE_KNOCKOUT,
//            'configuration' => '{"third_place":"0"}',
//            'created_at' => Carbon::now(),
//        ]);
        DB::table('stages')->insert([
            'name' => '{"en":"Test 1","fr":"Test 1"}',
            'season_id' => 1,
            'stage_number' => 1,
            'type' => Season::STAGE_TYPE_KNOCKOUT,
            'configuration' => '{"third_place":"0"}',
            'created_at' => Carbon::now(),
        ]);

        DB::table('stages')->insert([
            'name' => '{"en":"Test 2","fr":"Test 2"}',
            'season_id' => 1,
            'stage_number' => 2,
            'type' => Season::STAGE_TYPE_KNOCKOUT,
            'configuration' => '{"third_place":"0"}',
            'created_at' => Carbon::now(),
        ]);

        DB::table('stages')->insert([
            'name' => '{"en":"Test 3","fr":"Test 3"}',
            'season_id' => 1,
            'stage_number' => 2,
            'type' => Season::STAGE_TYPE_KNOCKOUT,
            'configuration' => '{"third_place":"0"}',
            'created_at' => Carbon::now(),
        ]);

        DB::table('stages')->insert([
            'name' => '{"en":"Semi Final","fr":"Semi Final"}',
            'season_id' => 1,
            'stage_number' => 2,
            'type' => Season::STAGE_TYPE_ROUND_ROBIN,
            'configuration' => '{"seeding":"3","round_robin_type":"2"}',
            'created_at' => Carbon::now(),
        ]);

        DB::table('stages')->insert([
            'name' => '{"en":"Final","fr":"Final"}',
            'season_id' => 1,
            'stage_number' => 2,
            'type' => Season::STAGE_TYPE_KNOCKOUT,
            'configuration' => '{"third_place":"0"}',
            'created_at' => Carbon::now(),
        ]);


        DB::table('stages')->insert([
            'name' => '{"en":"Test 1","fr":"Test 1"}',
            'season_id' => 2,
            'stage_number' => 1,
            'type' => Season::STAGE_TYPE_KNOCKOUT,
            'configuration' => '{"third_place":"0"}',
            'created_at' => Carbon::now(),
        ]);

        DB::table('stages')->insert([
            'name' => '{"en":"Test 2","fr":"Test 2"}',
            'season_id' => 2,
            'stage_number' => 2,
            'type' => Season::STAGE_TYPE_KNOCKOUT,
            'configuration' => '{"third_place":"0"}',
            'created_at' => Carbon::now(),
        ]);
        // Stadiums

            DB::table('stadiums')->insert([
                'name' => '{"en":"TwentyFive.7"}',
                'country' => 'my',
                'status' => Stadium::STATUS_PUBLISH,
                'location' => 'Lot 43495, Persiaran Oleander, 42500, Telok Panglima Garang, Selangor',
                'latitude' => "2.9551113",
                'longitude' => "101.5492551",
                'capacity' => '500',
                'image' => '1592218068.jpg',
                'created_at' => Carbon::now(),
            ]);
        DB::table('stadiums')->insert([
            'name' => '{"en":"Sir Gerald Templer Football Field"}',
            'country' => 'my',
            'status' => Stadium::STATUS_PUBLISH,
            'location' => 'Sekolah Tunas Bakti, SBE,Taman Salak Selatan,57100 Kuala Lumpur',
            'latitude' => "3.0803593",
            'longitude' => "101.6989772",
            'capacity' => '500',
            'image' => '1592218068.jpg',
            'created_at' => Carbon::now(),
        ]);
        // Staff
        for ($i = 1; $i <= 2; $i++) {
            DB::table('staff')->insert([
                'name' => '{"en":"Dummy Staff ' . $i . '","fr":"Dummy Staff ' . $i . '"}',
                'country' => 'pk',
                'status' => Staff::STATUS_PUBLISH,
                'image' => '1592218869.jpg',
                'type' => 'm',
                'created_at' => Carbon::now(),
            ]);
        }
        // Clubs
        for ($i = 1; $i <= 5; $i++) {
            DB::table('clubs')->insert([
                "stadium_id" => random_int(1, 2),
                'name' => '{"en":"Team ' . $i . '","fr":"Team ' . $i . '"}',
                'description' => '{"en":"' . Str::random(15) . '","fr":"' . Str::random(15) . '"}',
                'founding_date' => '31 March, 2020',
                'primary_color' => '#ffffff',
                'secondary_color' => '#f1f1f1',
                'status' => Team::STATUS_PUBLISH,
                'image' => '1593152126.png',
                'twitter' => '@fake_twitter_handle',
                'facebook' => '@fake_fb_page',
                'instagram' => '@fake_instagram_account',
                'youtube' => '@fake_youtube_account',
                'created_at' => Carbon::now(),
            ]);
        }
        // Teams
        $clubs = array();
        for ($i = 1; $i <= 5; $i++) {
            $club_id = random_int(1, 5);
            $team = [
                "club_id" => $club_id,
                'name' => '{"en":"Team ' . $i . '","fr":"Team ' . $i . '"}',
//                'description' => '{"en":"' . Str::random(15) . '","fr":"' . Str::random(15) . '"}',
                'team_group' => 'default',
                'status' => Team::STATUS_PUBLISH,
                'created_at' => Carbon::now(),
                'is_default_team' => !in_array($club_id, $clubs, true) ? true : false,
            ];
            $team['team_group'] = 'default';
            DB::table('teams')->insert($team);
            $clubs[] = $club_id;
            DB::table('staff_team')->insert([
                "staff_id" => random_int(1, 2),
                "team_id" => $i,
                'joined_on' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]);
        }


        // Players
        $teams = array();
        for ($i = 1; $i <= 100; $i++) {
            $team_id = random_int(1, 5);
            DB::table('players')->insert([
                'name' => '{"en":"Player ' . $i . ' Team ' . $team_id . '","fr":"Player ' . $i . ' Team ' . $team_id . '"}',
                'country' => 'my',
                'dob' => '31 March, 2020',
                'status' => Player::STATUS_PUBLISH,
                'image' => '1592218096.jpg',
                'jersey' => 10,
                'playing_position' => 'gk',
                'created_at' => Carbon::now(),
            ]);
            DB::table('player_team')->insert([
                'player_id' => $i,
                'team_id' => $team_id,
                'joined_on' => Carbon::yesterday(),
                'created_at' => Carbon::now(),
            ]);

            DB::table('player_season_team')->insert([
                'player_id' => $i,
                'season_id' => 1,
                'team_id' => $team_id,
                'created_at' => Carbon::now(),
            ]);
            if (!in_array($team_id, $teams, true)) {
                DB::table('captain_team')->insert([
                    'player_id' => $i,
                    'team_id' => $team_id,
                    'joined_on' => Carbon::yesterday(),
                    'created_at' => Carbon::now(),
                ]);
                $teams[] = $team_id;
            }
        }


        //Player season Team

        //Officials
        DB::table('officials')->insert([
            'name' => '{"en":"Dummy Official ' . $i . '","fr":"Dummy Official ' . $i . '"}',
            'country' => 'pk',
            'image' => '1591265932.jpg',
            'type' => 'rf',
            'created_at' => Carbon::now(),
        ]);

        //Fixtures

        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 1,
            'team_a_id' => 2,
            'team_b_id' => 3,
            'stage_id' => 1,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '25 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);

        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 1,
            'team_a_id' => 4,
            'team_b_id' => 5,
            'stage_id' => 1,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '26 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);
        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 1,
            'team_a_id' => 4,
            'team_b_id' => 5,
            'stage_id' => 2,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '26 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);

        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 1,
            'team_a_id' => 4,
            'team_b_id' => 5,
            'stage_id' => 3,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '26 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);

        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 1,
            'team_a_id' => 4,
            'team_b_id' => 5,
            'stage_id' => 4,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '26 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);

        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 1,
            'team_a_id' => 4,
            'team_b_id' => 5,
            'stage_id' => 5,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '26 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);

        DB::table('fixtures')->insert([
            'match_day' => 5,
            'season_id' => 2,
            'team_a_id' => 1,
            'team_b_id' => 5,
            'stage_id' => 2,
            'stadium_id' => 1,
            'official_id' => 1,
            'team_a_score' => 0,
            'team_b_score' => 0,
            'scheduled_date' => '27 March 2020',
            'match_status' => 'upcoming',
            'match_result' => 'pre_match',
            'created_at' => Carbon::now(),
        ]);


        DB::table('static_pages')->insert([
            "page_name"=> "about_us",
            "content" => "About Us",
        ]);


        DB::table('static_pages')->insert([
            "page_name"=> "privacy_policy",
            "content" => "Privacy Policy",
        ]);



        $this->call([
            PermissionsSeeder::class,
            RolesSeeder::class,
            SettingsSeeder::class
        ]);
    }
}
