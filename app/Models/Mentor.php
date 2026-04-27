<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mentor extends Model {
    protected $fillable = ['name', 'position', 'email', 'campus_id'];

    public function campus()   { return $this->belongsTo(Campus::class); }
    public function students() { return $this->hasMany(Student::class); }
}
