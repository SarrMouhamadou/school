<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classe;

class Matiere extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'coefficient'];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
    public function enseignants()
    {
        return $this->belongsToMany(User::class, 'enseignant_matiere', 'matiere_id', 'enseignant_id')
            ->withPivot('classe_id');
    }
}
