<?php

namespace App\Http\Controllers;

use App\SmsTemplate;
use App\Traits\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class SmsTemplateController extends Controller
{
    use Authorizable;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $sms_templates = SmsTemplate::orderby('created_at', 'desc')->paginate(20); //show only 5 items at a time in descending order

        return view('sms_templates.index', compact('sms_templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('sms_templates.create');
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
            'subject'=>'required',
            'content'=>'required',
        ]);

        $sms_template = New SmsTemplate;
        $sms_template->id = $request->id;
        $sms_template->subject = $request->subject;
        $sms_template->description = $request->description;
        $sms_template->content = $request->content;
        $sms_template->save();
    //Display a successful message upon save
        return redirect()->route('sms_templates.index')
            ->with('status', $sms_template->subject.' created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(SmsTemplate $sms_template) {
        return view ('sms_templates.show', compact('sms_template'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(SmsTemplate $sms_template) {
        return view('sms_templates.edit', compact('sms_template'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SmsTemplate $sms_template) {
        $this->validate($request, [
            'subject'=>'required',
            'content'=>'required',
        ]);

        $sms_template->subject = $request->input('subject');
        $sms_template->description = $request->input('description');
        $sms_template->content = $request->input('content');
        $sms_template->save();

        return redirect()->route('sms_templates.index', 
            $sms_template->id)->with('status', 
            $sms_template->subject.' updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SmsTemplate $sms_template) {
        $sms_template->delete();

        return redirect()->route('sms_templates.index')
            ->with('status',
            $sms_template->name.' successfully deleted');

    }
}
