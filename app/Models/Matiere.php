<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'coefficient'];

    public function enseignants()
    {
        return $this->belongsToMany(Enseignant::class)->withPivot('classe_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }
}
