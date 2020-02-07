<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\PatientCareProvider;
class PatientCareProviderController extends Controller
{
	public function toggle_show_pf(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('tpi')) {
			try {
				Log::info('Start updating pcp show pf');
				Log::info($request);
					$update_pcp = PatientCareProvider::findOrFail($request->input('pcp_id'));
					$update_pcp->show_pf = $request->input('pcp_show_pf');
					if($update_pcp->save())
						$returndata['message'] = 'PatientCareProvider has been updated.';
				Log::info('End updating pcp show pf');
			} catch(\Exception $e) {
				$returndata['success'] = false;
				$returndata['message'] = 'Physician update_pcp error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}
}
