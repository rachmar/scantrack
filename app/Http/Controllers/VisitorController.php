<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use App\Models\Visitor;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $directories = Directory::get(); 

        $visitor = Visitor::where('card_id', $request->visitor)->first();

        return view("public.visitor", compact('directories', 'visitor'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^\d{11}$/',
            'directory_id' => 'required|exists:directories,id',
            'purpose' => 'required|string|max:1000',
        ]);

        // Store the validated data in the database
        $visitor = Visitor::create([
            'card_id' => strtoupper('VIS'. bin2hex(random_bytes(5))),
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'directory_id' => $validated['directory_id'],
            'purpose' => $validated['purpose'],
        ]);

        return redirect()->route('public.visitor.index')->with('success', 'Visitor registration successful! Please notify the guard at the post');

    }

    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(404);
    }
}
