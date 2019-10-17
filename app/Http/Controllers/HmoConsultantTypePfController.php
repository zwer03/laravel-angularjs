<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\HmoConsultantTypePf;
use Illuminate\Support\Facades\DB;
class HmoConsultantTypePfController extends Controller
{
	public function delete(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			Log::info('Start deleting hmo consultant type pf');
			Log::info($request);
			$HmoConsultantTypePf = HmoConsultantTypePf::find($request['id']);
			$HmoConsultantTypePf->delete();
			$returndata['message'] = 'Successfully deleted. ID:'. $HmoConsultantTypePf->id;
			Log::info('End deleting hmo consultant type pf');
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
	}
}