<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use Illuminate\Http\Request;

class KidController extends Controller
{
    public function index()//: Response
    {
        $kids = Kid::all();

        return Inertia::render('Kids/AllKids',['kids' => $kids]);
    }

    //TODO: more verification in each field and frontend verification messages!!!
    public function createKid(Request $request) {
        $incomingFields = $request->validate([
            'name' => 'required', 
            'phone' => ['required', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['required', 'email'],
            'wheelchair' => 'required',
        ]);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['wheelchair'] = strip_tags($incomingFields['wheelchair']);

        Kid::create($incomingFields);
        return redirect('/kids');
    }

    public function showEditScreen(Kid $kid) {
        return Inertia::render('Kids/Edit',['kid'=> $kid]);
    }

    public function editKid(Kid $kid, Request $request) {
        $incomingFields = $request->validate([
            'name' => 'required', 
            'phone' => ['required', 'regex:/^[0-9]{9,15}$/'],
            'email' => ['required', 'email'],
            'wheelchair' => 'required',
        ]);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['wheelchair'] = strip_tags($incomingFields['wheelchair']);

        $kid->update($incomingFields);
        return redirect('/kids');
    }

    public function deleteKid($id) {
        $kid = Kid::findOrFail($id);
        $kid->delete();
        
        return redirect('/kids');
    }
}
