<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfflineActivityTimeRequest extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'comment',
        'duration_seconds',
        'patient_id',
        'requester_id',
        'is_approved',
        'is_behavioral',
        'performed_at',
        'activity_id',
    ];
    
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
    
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    
    public function durationInMinutes()
    {
        return $this->duration_seconds / 60;
    }
    
    public function getStatusCssClass() {
        switch ($this->is_approved) {
            case null:
                return 'warning';
            case 1:
                return 'success';
            case 0:
                return 'danger';
        }
    }
    
    public function status()
    {
        switch ($this->is_approved) {
            case null:
                return 'PENDING';
            case 1:
                return 'APPROVED';
            case 0:
                return 'REJECTED';
        }
    }
}
