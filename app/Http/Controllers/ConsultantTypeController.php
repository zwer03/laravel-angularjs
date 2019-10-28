<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\ConsultantType;
use Illuminate\Support\Facades\DB;
class ConsultantTypeController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$returndata['data'] = DB::table('consultant_types')->orderBy('id')->paginate(20);
			// Log::info(DB::table('medical_packages')->paginate(15));
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get consultant_types error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function list() {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			
			$returndata['data'] = DB::table('consultant_types')->pluck('id', 'name');
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get ConsultantType error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function add(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			Log::info('Start adding ConsultantType');
			if (request()->isMethod('post')) {
				Log::info($request);
				$existing_consultant_type = ConsultantType::where('external_id','=',$request['ConsultantType']['external_id'])->first();
				if(!$existing_consultant_type){
					$save_consultant_type = new ConsultantType;
					$save_consultant_type->external_id = $request['ConsultantType']['external_id'];
					$save_consultant_type->name = $request['ConsultantType']['name'];
					$save_consultant_type->default_pf_amount = $request['ConsultantType']['default_pf_amount'];
					if($save_consultant_type->save()){
						$returndata['message'] = 'ConsultantType has been saved.';
					}
				}else
					$returndata = array('success'=>false, 'message'=>'ConsultantType already exists.');
			}
			Log::info('End adding ConsultantType');
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
	}
	
	public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			Log::info('Start editing ConsultantType');
			if (request()->isMethod('post')) {
				Log::info($request);
				$save_consultant_types = ConsultantType::findOrFail($request['ConsultantType']['id']);
				$save_consultant_types->name = $request['ConsultantType']['name'];
				// $save_consultant_types->description = $request['ConsultantType']['description'];
				$save_consultant_types->default_pf_amount = $request['ConsultantType']['default_pf_amount'];
				if ($save_consultant_types->save())
					$returndata['message'] = 'ConsultantType has been saved.';
			}else{
				$consultant_types = DB::table('consultant_types')->where('id','=',$request['id'])->first();
				if (!$consultant_types)
					throw new \Exception("Invalid ConsultantType", 1);
				$returndata['data'] = $consultant_types;
			}
			Log::info('End editing ConsultantType');
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
    }
}