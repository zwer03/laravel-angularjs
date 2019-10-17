<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\People;
use App\Patient;
use App\User;
use App\LaboratoryPatientOrder;

class PatientController extends Controller
{

    public function get_patient_orders(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);
    	try {

			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');

			if ($request->filled('start_date')) {
				$start_date = date('Y-m-d', strtotime($request->input('start_date')));
			}
			if ($request->filled('end_date')) {
				$end_date = date('Y-m-d', strtotime($request->input('end_date')));
			}
			// Log::info('HERE');
			// Log::info($request->user());
			if ($start_date > $end_date) {
				throw new \Exception("Invalid date range!", 1);
			}
			$patient_orders = LaboratoryPatientOrder::select(
				'laboratory_patient_orders.id',
				'laboratory_patient_orders.company_branch_id',
				'laboratory_patient_orders.laboratory_id',
				'laboratory_patient_orders.total_amount_due',
				'laboratory_patient_orders.specimen_id',
				'laboratory_patient_orders.external_specimen_id',
				'laboratory_patient_orders.date_requested',
				'laboratory_patient_orders.time_requested',
				'patients.id as patient_id',
				'patients.internal_id as patient_internal_id',
				'people.id as people_id',
				'people.title_id as people_title_id',
				'people.lastname as people_lastname',
				'people.firstname as people_firstname',
				'people.middlename as people_middlename',
				'people.birthdate as people_birthdate',
				'people.sex as people_sex',
				'people.marital_status as people_marital_status',
				'laboratory_test_orders.id as test_order_id',
				'laboratory_test_orders.status as test_order_status',
				'laboratory_test_results.id as test_result_id',
				'laboratory_test_results.order_type as test_result_order_type',
				'laboratory_test_results.remarks as test_result_remarks',
				'laboratory_test_results.order_status as test_result_order_status',
				'laboratory_test_results.release_level_id as test_result_release_level_id',
				'laboratory_test_results.release_date as test_result_release_date',
				'laboratory_test_results.release_time as test_result_release_time',
				'laboratory_test_results.medtech_user_id as test_result_medtech_user_id',
				'laboratory_test_results.other_medtech_user_id as test_result_other_medtech_user_id',
				'laboratory_test_results.pathologist_user_id as test_result_pathologist_user_id',
				'laboratory_test_results.pdf_result as test_result_pdf_result',
				'laboratory_test_results.pdf_filename as test_result_pdf_filename',
				'laboratory_test_groups.id as test_group_id',
				'laboratory_test_groups.name as test_group_name'
			)
			->leftJoin('patients','laboratory_patient_orders.patient_id','=','patients.id')
			->leftJoin('people','patients.person_id','=','people.id')
			->leftJoin('laboratory_test_orders','laboratory_patient_orders.id','=','laboratory_test_orders.id')
			->leftJoin('laboratory_test_results','laboratory_test_orders.id','=','laboratory_test_results.test_order_id')
			->leftJoin('laboratory_test_groups','laboratory_test_results.test_group_id','=','laboratory_test_groups.id')
			->where('people.myresultonline_id','=',$request->user()->username)
			->where('laboratory_patient_orders.date_requested','>=',$start_date)
			->where('laboratory_patient_orders.date_requested','<=',$end_date)
			->orderBy('laboratory_patient_orders.date_requested', 'desc')
			->paginate(20);
			
			$formatted_orders = array();
			$patient_order_items = $patient_orders->items();
			foreach($patient_order_items as $order){
				if (!isset($formatted_orders[$order->id])) {
					$formatted_orders[$order->id] = array(
						'People' => array(),
						'Patient' => array(),
						'TestOrder' => array()
					);
					$formatted_orders[$order->id]['People']['id'] = $order->people_id;
					$formatted_orders[$order->id]['People']['title_id'] = $order->people_title_id;
					$formatted_orders[$order->id]['People']['lastname'] = $order->people_lastname;
					$formatted_orders[$order->id]['People']['firstname'] = $order->people_firstname;
					$formatted_orders[$order->id]['People']['middlename'] = $order->people_middlename;
					$formatted_orders[$order->id]['People']['birthdate'] = $order->people_birthdate;
					$formatted_orders[$order->id]['People']['sex'] = $order->people_sex;
					$formatted_orders[$order->id]['People']['marital_status'] = $order->people_marital_status;

					$formatted_orders[$order->id]['Patient']['id'] = $order->patient_id;
					$formatted_orders[$order->id]['Patient']['internal_id'] = $order->patient_internal_id;

					$formatted_orders[$order->id]['TestOrder']['id'] = $order->test_order_id;
					$formatted_orders[$order->id]['TestOrder']['status'] = $order->test_order_status;
				}

				if (!isset($formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id])) {
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['id'] = $order->test_result_id;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['order_type'] = $order->test_result_order_type;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['remarks'] = $order->test_result_remarks;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['order_status'] = $order->test_result_order_status;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['release_level_id'] = $order->test_result_release_level_id;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['release_date'] = $order->test_result_release_date;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['release_time'] = $order->test_result_release_time;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['medtech_user_id'] = $order->test_result_medtech_user_id;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['other_medtech_user_id'] = $order->test_result_other_medtech_user_id;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['pathologist_user_id'] = $order->test_result_pathologist_user_id;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['pdf_result'] = $order->test_result_pdf_result;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['pdf_file'] = $order->test_result_pdf_file;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['TestGroup']['id'] = $order->test_group_id;
					$formatted_orders[$order->id]['TestOrder']['TestResult'][$order->test_result_id]['TestGroup']['name'] = $order->test_group_name;
				}

			}
			foreach ($patient_orders->items() as &$patient_order) {
				if (isset($formatted_orders[$patient_order->id])) {
					$patient_order['People'] = $formatted_orders[$patient_order->id]['People'];
					$patient_order['Patient'] = $formatted_orders[$patient_order->id]['Patient'];
					$patient_order['TestOrder'] = $formatted_orders[$patient_order->id]['TestOrder'];
				}
			}
			// Log::info($formatted_orders);
			$returndata['data'] = $patient_orders;
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = ' get_patients error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		
		return $returndata;
    }

    public function get_person(Request $request) {

		$returndata = array('success'=>true,'message'=>null,'data'=>null);
		
		if ($request->filled('username')) {
			$username = $request->input('username');
		}else
			$username = $request->user()->username;

    	$user = User::select(
				'people.*',
				'users.id as user_id',
				'users.username as user_username',
				'users.role',
				'users.name',
				'users.last_login',
			)
			->leftJoin('people','people.myresultonline_id','=','users.username')
			->where('users.username','=',$username)
			->first();
		
		if($user)
			$returndata['data'] = $user;
		else
			$returndata = array('success'=>false,'message'=>'No record found.','data'=>null);
		

		return $returndata;
    }
}
