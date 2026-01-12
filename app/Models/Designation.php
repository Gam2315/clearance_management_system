<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;
    protected $table = 'designation';
    protected $fillable = ['department_id','description'];

    public function department() {
        return $this->belongsTo(Department::class);
    }
}
