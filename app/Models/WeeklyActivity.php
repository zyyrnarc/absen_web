<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivity extends Model {
    protected $fillable = ['student_id', 'activity_name', 'activity_date'];

    public function student() { return $this->belongsTo(Student::class); }
}