<?php

namespace App;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListIdleProperty extends Model
{
    use SoftDeletes;
    protected $table = 'list_idle_properties';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'unit_id',
        'user_id'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
