<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\SmsTemplate;
use Illuminate\Support\Facades\DB;
class SmsTemplateController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$returndata['data'] = DB::table('sms_templates')->orderBy('id')->paginate(15);
			// Log::info(DB::table('sms_templates')->paginate(15));
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get sms_templates error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		try {
			Log::info('Start editing sms template');
			if (request()->isMethod('post')) {
				Log::info($request);
				$save_sms_templates = SmsTemplate::findOrFail($request['SmsTemplate']['id']);
				$save_sms_templates->content = $request['SmsTemplate']['content'];
				$save_sms_templates->type = $request['SmsTemplate']['type'];
				if ($save_sms_templates->save())
					$returndata['message'] = 'SmsTemplate has been saved.';
			}else{
				$sms_template = DB::table('sms_templates')->where('id','=',$request['id'])->first();
				if (!$sms_template)
					throw new \Exception("Invalid SmsTemplate", 1);
				$returndata['data'] = $sms_template;
			}
			Log::info('End editing sms template');
		} catch (\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
    }
}