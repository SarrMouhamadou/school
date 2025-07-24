<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = ['nom'];
    protected function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'classe_id');
    }
    public function enseignants()
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_matiere', 'classe_id', 'enseignant_id');
    }
}
