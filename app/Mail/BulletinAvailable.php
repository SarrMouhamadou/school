<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulletinAvailable extends Mailable
{
    use Queueable, SerializesModels;

    public $etudiant;
    public $semestre;
    public $moyenneGenerale;

    public function __construct($etudiant, $semestre, $moyenneGenerale)
    {
        $this->etudiant = $etudiant;
        $this->semestre = $semestre;
        $this->moyenneGenerale = $moyenneGenerale;
    }

    public function build()
    {
        return $this->view('emails.bulletin-available')
                    ->subject('Nouveau Bulletin Disponible');
    }
}
