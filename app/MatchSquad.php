<?php

namespace App;

use App\Traits\BelongsToTenant;

class MatchSquad extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'match_squad';
}
