<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;
    protected $table = 'units';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'kode_unit',
        'id_parent',
        'user_id',
        'type',
        'status',
        'periode_pembayaran',
        'purchase_type',
        'tenor',
        'nama_penghuni',
        'no_identitas',
        'alamat',
        'provinsi',
        'kota',
        'kode_pos',
        'tanggal_mulai',
        'tanggal_jatuh_tempo',
    ];

    public function perumahan(): BelongsTo
    {
        return $this->belongsTo(Perumahan::class, 'id_parent', 'id');
    }
    public function kontrakan(): BelongsTo
    {
        return $this->belongsTo(Kontrakan::class, 'id_parent', 'id');
    }
    public function kostan(): BelongsTo
    {
        return $this->belongsTo(Kostan::class, 'id_parent', 'id');
    }

    public function listPayments()
    {
        return $this->hasMany(ListPayment::class);
    }
    public function listIdleProperties()
    {
        return $this->hasMany(ListIdleProperty::class);
    }
}
