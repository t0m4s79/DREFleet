<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\Place;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index()//: Response
    {
        $places = Place::all();
        $kids = Kid::all();

        return Inertia::render('Places/AllPlaces',['places' => $places, 'kids' => $kids]);
    }

    //TODO: more verification in each field and frontend verification messages!!!
    public function createPlace(Request $request) {
        $incomingFields = $request->validate([
            'address' => 'required', 
            'known_as' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);~

        Place::create($incomingFields);
        return redirect('/places');
    }

    public function showEditScreen(Place $place) {
        $kids = Kid::all();
        return Inertia::render('Places/Edit',['place'=> $place, 'kids'=> $kids]);
    }

    public function editPlace(Place $place, Request $request) {
        $incomingFields = $request->validate([
            'address' => 'required', 
            'known_as' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);

        $place->update($incomingFields);
        return redirect('/places');
    }

    public function deletePlace($id) {
        $place = Place::findOrFail($id);
        $place->delete();
        
        return redirect('/places');
    }
}
