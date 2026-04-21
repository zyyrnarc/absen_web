<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Permit extends Model {
    protected $fillable = ['student_id', 'type', 'permit_date', 'reason', 'status'];

    public function student() { return $this->belongsTo(Student::class); }
}