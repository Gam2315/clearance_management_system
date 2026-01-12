<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year',
        'semester',
        'status',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'academic_id', 'id');
    }

    public function clearances()
    {
        return $this->hasMany(Clearance::class, 'academic_id', 'id');
    }
}
