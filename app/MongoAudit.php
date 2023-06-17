<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Audit;
use OwenIt\Auditing\Contracts\Audit as AuditContract;

class MongoAudit extends Model implements AuditContract
{
    use Audit;

    protected $table = 'audits';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * @return string
     */
    public function getTable() : string {
        return $this->collection ?? parent::getTable();
    }

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'old_values'   => 'json',
        'new_values'   => 'json',
        'auditable_id' => 'integer',
    ];

    /**
     * {@inheritdoc}
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        return $this->morphTo();
    }
}
