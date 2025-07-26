<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'etudiant_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function students()
    {
        return $this->belongsToMany(Etudiant::class, 'parent_student', 'user_id', 'student_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'enseignant_matiere', 'enseignant_id', 'classe_id')
                    ->withPivot('matiere_id');
    }

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere', 'enseignant_id', 'matiere_id')
                    ->withPivot('classe_id');
    }
}
