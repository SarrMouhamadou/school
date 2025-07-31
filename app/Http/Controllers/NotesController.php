<?php
namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Etudiant;
use App\Models\Matiere;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotesController extends Controller
{
    public function indexWeb(Request $request)
    {
        $user = Auth::user();
        $enseignant = Enseignant::where('user_id', $user->id)->firstOrFail();
        $notes = Note::with(['etudiant.classe', 'matiere'])
            ->whereIn('matiere_id', $enseignant->matieres()->pluck('matieres.id'))
            ->get();
        return view('enseignant.notes.index', compact('notes'));
    }

    public function create()
    {
        $user = Auth::user();
        $enseignant = Enseignant::where('user_id', $user->id)->firstOrFail();
        $etudiants = Etudiant::all();
        $matieres = $enseignant->matieres()->get();
        return view('enseignant.notes.create', compact('etudiants', 'matieres'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $enseignant = Enseignant::where('user_id', $user->id)->firstOrFail();
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'matiere_id' => 'required|exists:matieres,id',
            'valeur' => 'required|numeric|min:0|max:20',
            'semestre' => 'required|in:S1,S2',
            'type_evaluation' => 'required|in:devoir,examen',
        ]);

        if (!$enseignant->matieres()->where('matiere_id', $request->matiere_id)->exists()) {
            return back()->withErrors(['matiere_id' => 'Vous n\'êtes pas autorisé à saisir des notes pour cette matière.']);
        }

        Note::create($request->only(['etudiant_id', 'matiere_id', 'valeur', 'semestre', 'type_evaluation']));
        return redirect()->route('enseignant.dashboard')->with('success', 'Note ajoutée avec succès.');
    }

    public function edit($id)
    {
        $note = Note::findOrFail($id);
        $user = Auth::user();
        $enseignant = Enseignant::where('user_id', $user->id)->firstOrFail();
        $etudiants = Etudiant::all();
        $matieres = $enseignant->matieres()->get();
        return view('enseignant.notes.edit', compact('note', 'etudiants', 'matieres'));
    }

    public function update(Request $request, $id)
    {
        $note = Note::findOrFail($id);
        $user = Auth::user();
        $enseignant = Enseignant::where('user_id', $user->id)->firstOrFail();
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'matiere_id' => 'required|exists:matieres,id',
            'valeur' => 'required|numeric|min:0|max:20',
            'semestre' => 'required|in:S1,S2',
            'type_evaluation' => 'required|in:devoir,examen',
        ]);

        if (!$enseignant->matieres()->where('matiere_id', $request->matiere_id)->exists()) {
            return back()->withErrors(['matiere_id' => 'Vous n\'êtes pas autorisé à modifier des notes pour cette matière.']);
        }

        $note->update($request->only(['etudiant_id', 'matiere_id', 'valeur', 'semestre', 'type_evaluation']));
        return redirect()->route('enseignant.dashboard')->with('success', 'Note mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $user = Auth::user();
        $enseignant = Enseignant::where('user_id', $user->id)->firstOrFail();
        if (!$enseignant->matieres()->where('matiere_id', $note->matiere_id)->exists()) {
            return back()->withErrors(['matiere_id' => 'Vous n\'êtes pas autorisé à supprimer cette note.']);
        }
        $note->delete();
        return redirect()->route('enseignant.dashboard')->with('success', 'Note supprimée avec succès.');
    }

    public function getStudentNotesWeb(Request $request)
    {
        $user = Auth::user();
        $etudiantId = $user->etudiant_id;
        $semestre = $request->query('semestre', 'S1');
        $etudiant = Etudiant::with(['notes.matiere'])->findOrFail($etudiantId);
        $notes = $etudiant->notes->where('semestre', $semestre);
        return view('eleve.notes.index', compact('etudiant', 'notes', 'semestre'));
    }

    public function calculateBulletinWeb($semestre)
    {
        $user = Auth::user();
        $etudiantId = $user->etudiant_id;
        $etudiant = Etudiant::with(['notes.matiere'])->findOrFail($etudiantId);
        $notes = $etudiant->notes->where('semestre', $semestre);

        $moyennesMatieres = [];
        $totalPoints = 0;
        $totalWeighted = 0;

        foreach ($notes->groupBy('matiere_id') as $matiereId => $matiereNotes) {
            $devoir = $matiereNotes->where('type_evaluation', 'devoir')->first();
            $examen = $matiereNotes->where('type_evaluation', 'examen')->first();
            if ($devoir && $examen) {
                $moyenneMatiere = ($devoir->valeur * 0.4) + ($examen->valeur * 0.6);
                $matiere = Matiere::find($matiereId);
                $moyennesMatieres[$matiere->nom] = number_format($moyenneMatiere, 2);
                $totalPoints += $moyenneMatiere * $matiere->coefficient;
                $totalWeighted += $matiere->coefficient;
            }
        }

        $moyenneGenerale = $totalWeighted ? number_format($totalPoints / $totalWeighted, 2) : null;
        return view('eleve.bulletin', compact('etudiant', 'semestre', 'moyennesMatieres', 'moyenneGenerale'));
    }

    public function downloadBulletin($semestre)
    {
        // À implémenter pour l'export PDF
        return response()->json(['message' => 'Fonctionnalité de téléchargement non implémentée']);
    }
}
