<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\PatientCareProvider;
class PatientCareProviderController extends Controller
{
	public function toggle_display_pf(Request $request, $pcp_id, $show_pf) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);
		try {
			Log::info('Start updating pcp show pf');
			Log::info($pcp_id);
			Log::info($show_pf);
			$update_pcp = PatientCareProvider::findOrFail($pcp_id);
			$update_pcp->show_pf = $show_pf;
			if($update_pcp->save())
				$returndata['message'] = 'PatientCareProvider has been updated.';
			Log::info('End updating pcp show pf');
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Physician update_pcp error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}

		return $returndata;
	}
}
