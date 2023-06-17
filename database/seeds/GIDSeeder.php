<?php

use Illuminate\Database\Seeder;
use Hidehalo\Nanoid\Client;
use Hidehalo\Nanoid\GeneratorInterface;

class GIDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = array('User', 'Association', 'Stadium', 'Club', 'Team', 'News', 'Official', 'Player', 'SeasonTemplate', 'Tournament', 'Season', 'Fixture',
//            'Sponsor',
            'Sport', 'Staff', 'Stage', 'Tenant', 'Weather', 'MatchStatistic', 'PointsTable', );
        foreach ($models as $model) {
            try {
                $model = trim('\App\ ') . $model;
                $items = $model::query()->whereNull('gid')->get();
                foreach ($items as $item) {
                    $client = new Client();
                    $item->gid = $client->generateId($size = 21, $mode = Client::MODE_DYNAMIC);
                    $item->save();
                }
            } catch (Exception $ex) {

            }
        }
    }
}
