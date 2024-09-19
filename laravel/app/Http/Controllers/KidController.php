<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\Kid;
use Inertia\Inertia;
use App\Models\Place;
use Illuminate\Http\Request;

class KidController extends Controller
{
    public function index() //: Response
    {
        $kids = Kid::with(['places'])->get(); //Load kids with number of places each has

        // Add a new attribute for place IDs
        $kids->transform(function ($kid) {
            $kid->place_ids = $kid->places->pluck('id')->toArray(); // Collect place IDs
            return $kid;
        });

        $places = Place::all();

        return Inertia::render('Kids/AllKids', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'kids' => $kids,
            'places' => $places
        ]);
    }

    public function showCreateKidForm()
    {

        $kids = Kid::with(['places'])->get(); //Load kids with number of places each has

        // Add a new attribute for place IDs
        $kids->transform(function ($kid) {
            $kid->place_ids = $kid->places->pluck('id')->toArray(); // Collect place IDs
            return $kid;
        });

        $places = Place::all();

        return Inertia::render('Kids/NewKid', ['kids' => $kids, 'places' => $places]);
    }

    //TODO: more verification in each field and frontend verification messages!!!
    public function createKid(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => 'required',
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['required', 'email'],
            'wheelchair' => 'required',
            'places' => 'array',
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['wheelchair'] = strip_tags($incomingFields['wheelchair']);

        $addPlaces = isset($incomingFields['places']) ? array_map('strip_tags', $incomingFields['places']) : [];

        try {
            $kid = Kid::create($incomingFields);
            $kid->places()->attach($addPlaces);
            return redirect()->route('kids.index')->with('message', 'Criança criada com sucesso!');
        } catch (\Exception $e) {
            return redirect('kids')->with('error', 'Houve um problema ao criar a criança. Tente novamente.');
        }
    }

    public function showEditKidForm(Kid $kid)
    {

        $kidPlaces = $kid->places;                                                  //Given kid places

        $associatedPlaceIds = $kid->places->pluck('id');
        $availablePlaces = Place::whereNotIn('id', $associatedPlaceIds)->get();     //Places that dont belong to given kid

        return Inertia::render('Kids/Edit', ['kid' => $kid, 'kidPlaces' => $kidPlaces, 'availablePlaces' => $availablePlaces]);
    }

    public function editKid(Kid $kid, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => 'required',
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['required', 'email'],
            'wheelchair' => 'required',
            'addPlaces' => 'array',
            'removePlaces' => 'array',
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['wheelchair'] = strip_tags($incomingFields['wheelchair']);

        $addPlaces = isset($incomingFields['addPlaces']) ? array_map('strip_tags', $incomingFields['addPlaces']) : [];
        $removePlaces = isset($incomingFields['removePlaces']) ? array_map('strip_tags', $incomingFields['removePlaces']) : [];

        try {
            $kid->update($incomingFields);
            $kid->places()->attach($addPlaces);
            $kid->places()->detach($removePlaces);
            return redirect('/kids')->with('message', 'Dados da criança #' . $kid->id . ' editados com sucesso!');;
        } catch (\Exception $e) {
            return redirect('/kids')->with('error', 'Houve um problema ao editar os dados da criança. Tente novamente.');
        }
    }

    public function deleteKid($id)
    {
        try {
            $kid = Kid::findOrFail($id);
            $kid->delete();

            return redirect('/kids')->with('message', 'Criança apagada com sucesso!');
            
        } catch (\Exception $e) {
            return redirect('/kids')->with('error', 'Houve um problema ao apagar a criança. Tente novamente.');
        }
    }
}
