<?php

namespace App\Http\Controllers;

use App\Configuration;
use App\Traits\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ConfigurationController extends Controller
{
    use Authorizable;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $configurations = Configuration::orderby('id', 'asc')->paginate(20); //show only 5 items at a time in descending order

        return view('configurations.index', compact('configurations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('configurations.create');
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
            'id'=>'required',
            'name'=>'required',
            'value'=>'required',
        ]);

        $configuration = New Configuration;
        $configuration->id = $request->id;
        $configuration->name = $request->name;
        $configuration->description = $request->description;
        $configuration->value = $request->value;
        $configuration->save();
    //Display a successful message upon save
        return redirect()->route('configurations.index')
            ->with('status', $configuration->name.' created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Configuration $configuration) {
        return view ('configurations.show', compact('configuration'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Configuration $configuration) {
        return view('configurations.edit', compact('configuration'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Configuration $configuration) {
        $this->validate($request, [
            'name'=>'required',
            'value'=>'required',
        ]);

        $configuration->name = $request->input('name');
        $configuration->description = $request->input('description');
        $configuration->value = $request->input('value');
        $configuration->save();

        return redirect()->route('configurations.index', 
            $configuration->id)->with('status', 
            $configuration->name.' updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Configuration $configuration) {
        $configuration->delete();

        return redirect()->route('configurations.index')
            ->with('status',
            $configuration->name.' successfully deleted');

    }
}
