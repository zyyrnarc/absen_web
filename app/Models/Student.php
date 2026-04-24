<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model {
    protected $fillable = [
        'name',
        'nim',
        'major',
        'study_program',
        'campus',
        'mentor',
        'email',
        'username',
        'password',
        'status',
    ];

    public function campus()           { return $this->belongsTo(Campus::class); }
    public function mentor()           { return $this->belongsTo(Mentor::class); }
    public function attendances()      { return $this->hasMany(Attendance::class); }
    public function permits()          { return $this->hasMany(Permit::class); }
    public function weeklyActivities() { return $this->hasMany(WeeklyActivity::class); }
}
