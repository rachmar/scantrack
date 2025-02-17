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
        $qrCode = null;

        $directories = Directory::get(); 

        $visitor = Visitor::where('card_id', $request->visitor)->first();

        if ($visitor) {
            $qrcode = QrCode::size(300)
            ->margin(2) // Adds a white border
            ->generate($visitor->card_id);
        }

        return view("public.visitor", compact('directories', 'qrCode', 'visitor'));
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

        return redirect()->route('public.visitor.index', [
            'visitor' => $visitor->card_id,
        ]);
    }

    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
