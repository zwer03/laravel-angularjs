<?php

namespace App\Http\Controllers;

use App\ConsultantType;
use App\Traits\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ConsultantTypeController extends Controller
{
    use Authorizable;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $consultant_types = ConsultantType::orderby('created_at', 'desc')->paginate(20); //show only 5 items at a time in descending order

        return view('consultant_types.index', compact('consultant_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('consultant_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) { 
        // Log::info($request);
    //Validating title and body field
        $this->validate($request, [
            'external_id'=>'required',
            'name'=>'required',
        ]);

        $consultant_type = New ConsultantType;
        $consultant_type->external_id = $request->external_id;
        $consultant_type->name = $request->name;
        $consultant_type->default_pf_amount = $request->default_pf_amount;
        $consultant_type->save();
    //Display a successful message upon save
        return redirect()->route('consultant_types.index')
            ->with('status', $consultant_type->name.' created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ConsultantType $consultant_type) {
        return view ('consultant_types.show', compact('consultant_type'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ConsultantType $consultant_type) {
        return view('consultant_types.edit', compact('consultant_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConsultantType $consultant_type) {
        $this->validate($request, [
            'external_id'=>'required',
            'name'=>'required',
        ]);

        $consultant_type->external_id = $request->input('external_id');
        $consultant_type->name = $request->input('name');
        $consultant_type->default_pf_amount = $request->input('default_pf_amount');
        $consultant_type->save();

        return redirect()->route('consultant_types.index', 
            $consultant_type->id)->with('status', 
            $consultant_type->name.' updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConsultantType $consultant_type) {
        $consultant_type->delete();

        return redirect()->route('consultant_types.index')
            ->with('status',
            $consultant_type->name.' successfully deleted');

    }
}
