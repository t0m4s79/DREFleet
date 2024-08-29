<?php

namespace App\Http\Controllers;

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

    //TODO: more verification in each field and frontend verification messages!!!
    public function createKid(Request $request)
    {
        $customErrorMessages = [
            'required' => 'O campo :attribute é obrigatório.',
            'email.email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.numeric' => 'Formato inválido. Apenas são permitidos números.',
            'phone.regex' => 'O campo telefone deve ter entre 9 e 15 dígitos.',
        ];

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

        if (isset($incomingFields['places'])) {
            $incomingFields['places'] = array_map('strip_tags', $incomingFields['places']);
        } else {
            $incomingFields['places'] = []; // If no places were selected, pass an empty array
        }

        try {
            $kid = Kid::create($incomingFields);
            $kid->places()->attach($incomingFields['places']);
            return redirect()->route('kids.index')->with('message', 'Criança criada com sucesso!');
        } catch (\Exception $e) {
            return redirect('kids')->with('error', 'Houve um problema ao criar a criança. Tente novamente mais tarde.');
        }
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

    public function showEditScreen(Kid $kid)
    {

        $kidPlaces = $kid->places;                                                  //Given kid places

        $associatedPlaceIds = $kid->places->pluck('id');
        $availablePlaces = Place::whereNotIn('id', $associatedPlaceIds)->get();     //Places that dont belong to given kid

        return Inertia::render('Kids/Edit', ['kid' => $kid, 'kidPlaces' => $kidPlaces, 'availablePlaces' => $availablePlaces]);
    }

    public function editKid(Kid $kid, Request $request)
    {
        $customErrorMessages = [
            'required' => 'O campo :attribute é obrigatório.',
            'email.email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.numeric' => 'Formato inválido. Apenas são permitidos números.',
            'phone.regex' => 'O campo telefone deve ter entre 9 e 15 dígitos.',
        ];

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

        if (isset($incomingFields['addPlaces'])) {
            $incomingFields['addPlaces'] = array_map('strip_tags', $incomingFields['addPlaces']);
        } else {
            $incomingFields['addPlaces'] = []; // If no places were selected, pass an empty array
        }

        if (isset($incomingFields['removePlaces'])) {
            $incomingFields['removePlaces'] = array_map('strip_tags', $incomingFields['removePlaces']);
        } else {
            $incomingFields['removePlaces'] = []; // If no places were selected, pass an empty array
        }

        try {
            $kid->update($incomingFields);
            $kid->places()->attach($incomingFields['addPlaces']);
            $kid->places()->detach($incomingFields['removePlaces']);
            return redirect('/kids');
        } catch (\Exception $e) {
            return redirect('kids')->with('error', 'Houve um problema ao editar os dados da criança. Tente novamente mais tarde.');
        }
    }

    public function deleteKid($id)
    {
        $kid = Kid::findOrFail($id);
        $kid->delete();

        return redirect('/kids');
    }
}
