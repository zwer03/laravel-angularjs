<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Practitioner;
class ReportController extends Controller
{

	public function pf_summary(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		// try {
			Log::info($request);
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
			// if ($start_date > $end_date) {
			// 	throw new \Exception("Invalid date range!", 1);
			// }
			$start_datetime = $start_date.' 00:00:00';
			$end_datetime = $end_date.' 23:59:59';
			$practitioner = Practitioner::select(
				'practitioners.external_id',
				'people.*'
			)
			->Join('people','practitioners.person_id','=','people.id')
			->where('people.myresultonline_id','=',$request->user()->username)
			->first();
			$pratitioner_external_id = $practitioner['external_id'];
			if(isset($pratitioner_external_id)){
				$query = "
				select * from (
					select peeps.firstname, peeps.middlename, peeps.lastname,pv.created_at,pv.hospitalization_plan, pcp.pf_amount from patient_care_providers pcp
					join patient_care_provider_transactions pcpt on pcp.id = pcpt.patient_care_provider_id
					join patient_visits pv on pcp.patient_visit_id = pv.id
					join patients px on pv.patient_id = px.id
					join people peeps on px.person_id = peeps.id
					join practitioners prac on prac.id = pcp.practitioner_id
					where prac.external_id = '$pratitioner_external_id' and pcpt.status = 1 and pv.created_at >= '$start_datetime' and pv.created_at <= '$end_datetime'
					union
					select '','', '','','TOTAL:',sum(pcp.pf_amount)  from patient_care_providers pcp
					join patient_visits pv on pcp.patient_visit_id = pv.id
					join patient_care_provider_transactions pcpt on pcp.id = pcpt.patient_care_provider_id
					join practitioners prac on prac.id = pcp.practitioner_id
					where prac.external_id = '$pratitioner_external_id' and pcpt.status = 1 and pv.created_at >= '$start_datetime' and pv.created_at <= '$end_datetime'
				) IncomeReport
				";
			}else{
				$query = "
				select * from (
					select peeps.firstname, peeps.middlename, peeps.lastname,pv.created_at,pv.hospitalization_plan, pcp.pf_amount from patient_care_providers pcp
					join patient_care_provider_transactions pcpt on pcp.id = pcpt.patient_care_provider_id
					join patient_visits pv on pcp.patient_visit_id = pv.id
					join patients px on pv.patient_id = px.id
					join people peeps on px.person_id = peeps.id
					join practitioners prac on prac.id = pcp.practitioner_id
					where pcpt.status = 1 and pv.created_at >= '$start_datetime' and pv.created_at <= '$end_datetime'
					union
					select '','', '','','TOTAL:',sum(pcp.pf_amount)  from patient_care_providers pcp
					join patient_visits pv on pcp.patient_visit_id = pv.id
					join patient_care_provider_transactions pcpt on pcp.id = pcpt.patient_care_provider_id
					join practitioners prac on prac.id = pcp.practitioner_id
					where pcpt.status = 1 and pv.created_at >= '$start_datetime' and pv.created_at <= '$end_datetime'
				) IncomeReport
				";
			}
			$returndata['data'] = DB::select(
				DB::raw($query)
			);
			// Log::info(DB::table('medical_packages')->paginate(15));
		// } catch(\Exception $e) {
		// 	$returndata['success'] = false;
		// 	$returndata['message'] = 'Get Report Income error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		// }
		Log::info($returndata);
		return view('reports.pf_summary', ['data'=>$returndata]);
	}
}