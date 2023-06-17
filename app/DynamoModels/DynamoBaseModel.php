<?php


namespace App\DynamoModels;


use BaoPham\DynamoDb\DynamoDbModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class DynamoBaseModel extends DynamoDbModel
{
    public const CREATED_AT = 'CreatedAt';
    public const UPDATED_AT = 'UpdatedAt';

    protected $primaryKey = 'GID';

    public function getTable(): string
    {
        $prefix = Config::get('dynamodb.connections.' . Config::get('dynamodb.default'));
        $prefix = $prefix['prefix'] ?? '';
        return $prefix . ( $this->table ?? Str::snake(Str::pluralStudly(class_basename($this))) );
    }
}
