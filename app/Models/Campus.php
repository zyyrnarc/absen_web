<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model {
    protected $fillable = ['name', 'address'];

    public function students() { return $this->hasMany(Student::class); }
    public function mentors()  { return $this->hasMany(Mentor::class); }
}