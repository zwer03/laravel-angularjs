<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Configuration;
use Illuminate\Support\Facades\DB;
class ConfigurationController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$returndata['data'] = DB::table('configurations')->orderBy('id')->paginate(15);
			// Log::info(DB::table('configurations')->paginate(15));
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get configurations error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			if (request()->isMethod('post')) {
				Log::info('update config');
				Log::info($request);
				$save_configuration = Configuration::findOrFail($request['Configuration']['id']);
				$save_configuration->name = $request['Configuration']['name'];
				$save_configuration->description = $request['Configuration']['description'];
				$save_configuration->value = $request['Configuration']['value'];
				if ($save_configuration->save())
					$returndata['message'] = 'Configurations has been saved.';
			}else{
				$configuration = DB::table('configurations')->where('id','=',$request['id'])->first();
				if (!$configuration)
					throw new \Exception("Invalid configuration", 1);
				$returndata['data'] = $configuration;
			}
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
    }
}