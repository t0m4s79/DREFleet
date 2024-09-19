<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\Kid;
use Inertia\Inertia;
use App\Models\Place;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Objects\Point;

class PlaceController extends Controller
{
    public function index() //: Response
    {
        $places = Place::with(['kids'])->get(); //Load kids with number of places each has

        // Add a new attribute for place IDs
        $places->transform(function ($place) {
            $place->kid_ids = $place->kids->pluck('id')->toArray(); // Collect place IDs
            return $place;
        });

        return Inertia::render('Places/AllPlaces', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'places' => $places
        ]);
    }

    public function showCreatePlaceForm()
    {
        return Inertia::render('Places/NewPlace');
    }

    public function createPlace(Request $request)
    {
        $customErrorMessages = [
            'required' => 'Este campo é obrigatório.',
            'numeric' => 'Apenas são permitidos números.',
            'latitude.between' => 'A latitude deve estar entre -90 e 90 graus.',
            'longitude.between' => 'A longitude deve estar entre -180 e 180 graus.',
            'latitude.regex' => 'O formato da latitude é inválido. Deve ter até 6 casas decimais.',
            'longitude.regex' => 'O formato da longitude é inválido. Deve ter até 6 casas decimais.',
            'known_as.regex' => 'O campo "Conhecido como" deve conter apenas letras e espaços.',
        ];

        $incomingFields = $request->validate([
            'address' => 'required|string|max:255',
            'known_as' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{1,6}$/'],
        ], $customErrorMessages);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);

        $coordinates = new Point($incomingFields['latitude'], $incomingFields['longitude']);

        try {
            Place::create([
                'address' => $incomingFields['address'],
                'known_as' => $incomingFields['known_as'],
                'coordinates' => $coordinates,
            ]);

            return redirect('/places')->with('message', 'Morada criada com sucesso!');;
        } catch (\Exception $e) {
            return redirect('places')->with('error', 'Houve um problema ao criar a morada. Tente novamente.');
        }
    }

    public function showEditPlaceForm(Place $place)
    {
        $kids = Kid::all();
        return Inertia::render('Places/Edit', ['place' => $place, 'kids' => $kids]);
    }

    public function editPlace(Place $place, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'address' => 'required|string|max:255',
            'known_as' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{1,6}$/'],
        ], $customErrorMessages);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);

        try {
            $coordinates = new Point($incomingFields['latitude'], $incomingFields['longitude']);

            $place->update([
                'address' => $incomingFields['address'],
                'known_as' => $incomingFields['known_as'],
                'coordinates' => $coordinates,
            ]);

            return redirect('/places')->with('message', 'Morada editada com sucesso!');
        } catch (\Exception $e) {
            return redirect('/places')->with('error', 'Houve um problema ao editar a morada. Tente novamente.');
        }
    }

    public function deletePlace($id)
    {
        try {
            $place = Place::findOrFail($id);
            $place->delete();

            return redirect('/places')->with('message', 'Morada apagada com sucesso!');

        } catch (\Exception $e) {
            return redirect('/places')->with('error', 'Houve um problema ao apagar a morada. Tente novamente.');
        }
    }
}
