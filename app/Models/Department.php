<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    protected $fillable = ['department_code', 'department_name', 'department_head'];

      public function courses()
    {
        return $this->hasMany(Course::class , 'department_id', 'id');
    }

    public function programs()
    {
        return $this->hasMany(Course::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id', 'id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'department_id', 'id');
    }

    public function clearances()
    {
        return $this->hasMany(Clearance::class, 'department_id', 'id');
    }
}
