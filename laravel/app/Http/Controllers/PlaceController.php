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
    //TODO: check if kid_id is required
    public function createPlace(Request $request) {
        $incomingFields = $request->validate([
            'address' => 'required', 
            'known_as' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'kid_id' => 'required',
        ]);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);~
        $incomingFields['kid_id'] = strip_tags($incomingFields['kid_id']);

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
            'kid_id' => 'required',
        ]);

        $incomingFields['address'] = strip_tags($incomingFields['address']);
        $incomingFields['known_as'] = strip_tags($incomingFields['known_as']);
        $incomingFields['latitude'] = strip_tags($incomingFields['latitude']);
        $incomingFields['longitude'] = strip_tags($incomingFields['longitude']);
        $incomingFields['kid_id'] = strip_tags($incomingFields['kid_id']);

        $place->update($incomingFields);
        return redirect('/places');
    }

    public function deletePlace($id) {
        $place = Place::findOrFail($id);
        $place->delete();
        
        return redirect('/places');
    }
}
