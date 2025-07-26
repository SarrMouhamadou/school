<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom', 'email', 'matricule', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere', 'enseignant_id', 'matiere_id')
                    ->withPivot('classe_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'enseignant_matiere', 'enseignant_id', 'classe_id')
                    ->withPivot('matiere_id');
    }
}
