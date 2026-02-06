<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'stadium_id',
        'bank_code',
        'account_number',
        'account_name',
        'bank_name',
        'qr_format',
        'is_active',
    ];

    public function stadium()
    {
        return $this->belongsTo(Stadium::class);
    }
}
