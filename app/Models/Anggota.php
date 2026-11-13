<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;
    protected $table = 'anggotas';
    
    protected $fillable = [
        'nama',
        'email',
        'no_telepon',
        'user_id',
    ];
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'anggota_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
