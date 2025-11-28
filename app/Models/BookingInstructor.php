<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingInstructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'package_id',
        'customer_name',
        'customer_phone',
        'notes',
        'status',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function package()
    {
        return $this->belongsTo(InstructorPackage::class);
    }
}
