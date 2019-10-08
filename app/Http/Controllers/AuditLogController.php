<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\AuditLog;
use App\User;
use Illuminate\Support\Facades\DB;
class AuditLogController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$username = null;
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');

			$filter_string = "";

			if ($request->filled('username')) {
				$username = $request->input('username');
				$filter_string .= "username=".$username.", ";
			}
			if ($request->filled('start_date')) {
				$start_date = date('Y-m-d', strtotime($request->input('start_date')));
				$filter_string .= "StartDate=".$request->input('start_date').", ";
			}
			if ($request->filled('end_date')) {
				$end_date = date('Y-m-d', strtotime($request->input('end_date')));
				$filter_string .= "EndDate=".$request->input('end_date').", ";
			}
			if ($start_date > $end_date) {
				throw new \Exception("Invalid date range!", 1);
			}
			$start_datetime = $start_date.' 00:00:00';
			$end_datetime = $end_date.' 23:59:59';
			$returndata['data'] = AuditLog::where(function($whereClause) use ($username,$start_datetime,$end_datetime) {
				$whereClause->whereBetween('audit_logs.datetime',[$start_datetime,$end_datetime]);
				if ($username) {
					$whereClause->where('audit_logs.remarks','like','%'.$username.'%');
				}
			})
			->orderBy('audit_logs.datetime', 'desc')
			->paginate(15);
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'User get_users error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function utilization(Request $request) {
		
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$username = null;
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');

			$filter_string = "";

			if ($request->filled('username')) {
				$username = $request->input('username');
				$filter_string .= "username=".$username.", ";
			}
			if ($request->filled('start_date')) {
				$start_date = date('Y-m-d', strtotime($request->input('start_date')));
				$filter_string .= "StartDate=".$request->input('start_date').", ";
			}
			if ($request->filled('end_date')) {
				$end_date = date('Y-m-d', strtotime($request->input('end_date')));
				$filter_string .= "EndDate=".$request->input('end_date').", ";
			}
			if ($start_date > $end_date) {
				throw new \Exception("Invalid date range!", 1);
			}
			$start_datetime = $start_date.' 00:00:00';
			$end_datetime = $end_date.' 23:59:59';
			Log::info($start_datetime);
			$users_login = AuditLog::where(function($whereClause) use ($username,$start_datetime,$end_datetime) {
				$whereClause->whereBetween('audit_logs.datetime',[$start_datetime,$end_datetime]);
				$whereClause->where('audit_logs.remarks','rlike','"success":"true"');
				$whereClause->where('audit_logs.action','=','user.login');
			})
			->orderBy('audit_logs.datetime', 'desc')
			->get();
			$visits = array();
			foreach($users_login as $key=>$value){
				$remarksjson = json_decode($value->remarks, true);
				$visits[$value->id] = DB::select('select * from users where username = :username', ['username' => $remarksjson['username']] );
			}

			
			$utilization_report = array();
			foreach($visits as $newval){
				if(isset($utilization_report[$newval[0]->id])){
					$utilization_report[$newval[0]->id]['count']++;
				}else{
					$utilization_report[$newval[0]->id]['count'] = 0;
					$utilization_report[$newval[0]->id]['name'] = $newval[0]->name;
					$utilization_report[$newval[0]->id]['role'] = $newval[0]->role;
					$utilization_report[$newval[0]->id]['count']++;
				}
			}
			$utilization_report = collect($utilization_report)->sortBy('count')->reverse()->toArray();
			$format_ur = array();
			foreach($utilization_report as $ur_key=>$ur){
				$format_ur[$ur['role']][$ur_key] = $ur;
			}
			Log::info($format_ur);
			if(isset($format_ur['ROLE_PATIENT'])){
				$format_ur['totalpatientvisits'] = 0;
				foreach($format_ur['ROLE_PATIENT'] as $format_ur_value)
					$format_ur['totalpatientvisits'] += $format_ur_value['count'];
			}
			if(isset($format_ur['ROLE_PHYSICIAN'])){
				$format_ur['totalphysicianvisits'] = 0;
				foreach($format_ur['ROLE_PHYSICIAN'] as $format_ur_value)
					$format_ur['totalphysicianvisits'] += $format_ur_value['count'];
			}
			$returndata['data'] = $format_ur;
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'User get_users error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		
		return $returndata;
	}
}