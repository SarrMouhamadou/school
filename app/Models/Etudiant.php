<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Etudiant extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $fillable = ['prenom', 'nom', 'date_de_naissance', 'matricule', 'classe_id'];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function notes ()
    {
        return $this->hasMany(Note::class, 'etudiant_id');
    }
}
