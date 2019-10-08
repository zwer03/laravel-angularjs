<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Hmo;
use Illuminate\Support\Facades\DB;
class HmoController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$returndata['data'] = DB::table('hmo')->orderBy('id')->paginate(15);
			// Log::info(DB::table('hmo')->paginate(15));
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get hmo error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			Log::info('Start editing hmo');
			if (request()->isMethod('post')) {
				Log::info($request);
				$save_hmo = Hmo::findOrFail($request['Hmo']['id']);
				$save_hmo->name = $request['Hmo']['name'];
				$save_hmo->description = $request['Hmo']['description'];
				$save_hmo->default_pf_amount = $request['Hmo']['default_pf_amount'];
				if ($save_hmo->save())
					$returndata['message'] = 'Hmo has been saved.';
			}else{
				$hmo = DB::table('hmo')->where('id','=',$request['id'])->first();
				if (!$hmo)
					throw new \Exception("Invalid hmo", 1);
				$returndata['data'] = $hmo;
			}
			Log::info('End editing hmo');
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
    }
}