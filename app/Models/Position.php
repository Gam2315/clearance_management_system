<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;
     protected $table = 'position';
    protected $fillable = ['designation_id','position_title'];

    public function designation() {
        return $this->belongsTo(Designation::class);
    }
}
