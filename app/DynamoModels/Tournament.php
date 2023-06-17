<?php


namespace App\DynamoModels;


class Tournament extends DynamoBaseModel
{

    protected $fillable = [
        'Name', 'Description', 'Status', 'Group', 'Image', 'Occurrence', 'Since',
    ];
}
