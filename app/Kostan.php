<?php

namespace App;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kostan extends Model
{
    use SoftDeletes;
    protected $table = 'kostans';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'alamat',
        'provinsi',
        'kota',
        'kode_pos',
        'jml_unit',
        'periode_pembayaran',
        'kode_unit',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'id_parent', 'id');
    }
}