<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    public const S3_FOLDER_PATH = 'tournaments/';
    public const INDEX_URL = 'tournaments';


    protected $fillable = [
        'league_table', 'tournament_id', 'result_matrix', 'numteams', 'ptswin', 'ptsdra', 'selmth', 'team_names', 'game_fixture_details', 'league_name'
    ];
}
