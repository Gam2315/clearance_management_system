<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClearanceStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'clearance_id',
        'department_id',
        'office_id',
        'status',
        'approved_by',
        'approver_role',
        'student_id',
        'or_number',
        'remarks',
        'cleared_at',
        'is_archived',
    ];

    protected $table = 'clearance_statuses';

    protected $casts = [
        'cleared_at' => 'datetime',
    ];
    public function clearance() {
        return $this->belongsTo(Clearance::class);
    }   

   

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update overall status whenever a clearance status is created, updated, or deleted
        static::created(function ($clearanceStatus) {
            if ($clearanceStatus->clearance) {
                $clearanceStatus->clearance->updateOverallStatus();
            }
        });

        static::updated(function ($clearanceStatus) {
            if ($clearanceStatus->clearance) {
                $clearanceStatus->clearance->updateOverallStatus();
            }
        });

        static::deleted(function ($clearanceStatus) {
            if ($clearanceStatus->clearance) {
                $clearanceStatus->clearance->updateOverallStatus();
            }
        });
    }

}
