<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
Use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;


class Schema extends Model
{
    use HasFactory, Uuids, SoftDeletes;
    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */

    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    public static function getSchemaName(string $crew_id): string
    {
        return 'db_scan_' . $crew_id;
    }

    public function openDatabaseConnection(): void {
        $dbName = $this->name;
        $dbConfig = Config::get('database.connections.mysql');
        $dbConfig['database'] = $dbName;
        Config::set('database.connections.' . $dbName, $dbConfig);
    }

}
