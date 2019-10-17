<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\MedicalPackage;
use Illuminate\Support\Facades\DB;
class MedicalPackageController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$returndata['data'] = DB::table('medical_packages')->orderBy('id')->paginate(20);
			// Log::info(DB::table('medical_packages')->paginate(15));
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get medical_packages error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			Log::info('Start editing medical_packages');
			if (request()->isMethod('post')) {
				Log::info($request);
				$save_medical_packages = MedicalPackage::findOrFail($request['MedicalPackage']['id']);
				$save_medical_packages->name = $request['MedicalPackage']['name'];
				$save_medical_packages->description = $request['MedicalPackage']['description'];
				$save_medical_packages->default_pf_amount = $request['MedicalPackage']['default_pf_amount'];
				if ($save_medical_packages->save())
					$returndata['message'] = 'MedicalPackage has been saved.';
			}else{
				$medical_package = DB::table('medical_packages')->where('id','=',$request['id'])->first();
				if (!$medical_package)
					throw new \Exception("Invalid MedicalPackage", 1);
				$returndata['data'] = $medical_package;
			}
			Log::info('End editing medical_packages');
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
    }
}