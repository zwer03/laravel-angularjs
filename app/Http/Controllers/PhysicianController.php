<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Interfaces\UserInterface;
use Illuminate\Support\Facades\Log;

use App\Patient;
use App\PatientCareProvider;
use App\PatientCareProviderTransaction;
use App\Practitioner;
use App\PatientVisit;
use App\PatientVisitHmo;
use App\PatientVisitMedicalPackage;
use App\Configuration;
use App\SmsTemplate;
use DB;
class PhysicianController extends Controller
{

    public function get_patient_visit(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('physician')) {

			Log::info('Start getting patient visit');
			Log::info($request);
			try {

				// TODO RETURN ERROR MESSAGE WHEN DATE NOW MINUS MGHC DATE TIME IS GREATER THAN GRACE PERIOD
				if(!$request->input('practitioner_id')){
					$practitioner = Practitioner::select(
						'practitioners.external_id',
						'people.*'
					)
					->Join('people','practitioners.person_id','=','people.id')
					->where('people.myresultonline_id','=',$request->user()->username)
					->first();
					$practitioner_id = $practitioner['external_id'];
				}else
					$practitioner_id = $request->input('practitioner_id');
				$patient_visit = PatientVisit::select(
					'patient_visits.id',
					'patient_visits.external_visit_number as visit_number',
					'patient_visits.created_at as admission_datetime',
					'patient_visits.mgh_datetime as mgh_datetime',
					'patient_visits.chief_complaint',
					'patient_visits.icd10',
					'patient_visits.final_diagnosis',
					'patient_visits.bed_room',
					'patient_visits.status as patient_visit_status',
					'patient_visits.hospitalization_plan',
					'patient_visits.membership_id',
					'patients.internal_id as patient_id',
					'people.lastname as px_last_name',
					'people.firstname as px_first_name',
					'people.middlename as px_middle_name',
					'people.sex as px_sex',
					'people.birthdate as px_birthdate',
					'people.marital_status as px_marital_status',
					'patient_care_providers.id as pcp_id',
					'patient_care_providers.consultant_type_id',
					'patient_care_providers.pf_amount',
					'patient_care_providers.phic_amount',
					'patient_care_providers.discount',
					'practitioners.external_id as practitioner_id',
					'consultant_types.name as consultant_type',
					'patient_care_provider_transactions.status as status',
					'patient_care_provider_transactions.expired_at as expiration_datetime'
				)
				->leftJoin('patients','patient_visits.patient_id','=','patients.id')
				->leftJoin('people','patients.person_id','=','people.id')
				->leftJoin('patient_care_providers','patient_care_providers.patient_visit_id','=','patient_visits.id')
				->leftJoin('consultant_types','consultant_types.id','=','patient_care_providers.consultant_type_id')
				->leftJoin('patient_care_provider_transactions','patient_care_providers.id','=','patient_care_provider_transactions.patient_care_provider_id')
				->leftJoin('practitioners','practitioners.id','=','patient_care_providers.practitioner_id')
				->where('patient_visits.external_visit_number','=',$request->input('visit_number'))
				->where('practitioners.external_id','=',$practitioner_id)
				->where('patients.internal_id','=',$request->input('patient_id'))
				->orderBy('patient_visits.created_at', 'desc')
				->first();
				$formatted_patient_visit = array();
				$patient_visit_hmo = PatientVisitHmo::select(
					'hmo.name',
					'hmo.default_pf_amount'
				)
				->leftJoin('patient_visits','patient_visits.id','=','patient_visit_hmo.patient_visit_id')
				->leftJoin('hmo','hmo.id','=','patient_visit_hmo.hmo_id')
				->where('patient_visits.id','=',$patient_visit->id)
				->get();
				$patient_visit_medical_packages = PatientVisitMedicalPackage::select(
					'medical_packages.name',
					'medical_packages.default_pf_amount'
				)
				->leftJoin('patient_visits','patient_visits.id','=','patient_visit_medical_packages.patient_visit_id')
				->leftJoin('medical_packages','medical_packages.id','=','patient_visit_medical_packages.medical_package_id')
				->where('patient_visits.id','=',$patient_visit->id)
				->get();
				$formatted_patient_visit['PatientVisit'] = $patient_visit;
				$formatted_patient_visit['PatientVisitHmo'] = $patient_visit_hmo;
				$formatted_patient_visit['PatientVisitMedicalPackages'] = $patient_visit_medical_packages;
				$returndata['data'] = $formatted_patient_visit;
				// Log::info($formatted_patient_visit);
				Log::info('End getting patient visit');
			} catch(\Exception $e) {
				$returndata['success'] = false;
				$returndata['message'] = 'Physician get_patients error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}
	
	public function get_practitioner_px(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('physician')) {
			try {
				Log::info('Start getting practitioner patients');
				Log::info('User currently logged in '.$request->user()->username);
				$lastname = '';
				$firstname = '';
				$practitioner = Practitioner::select(
					'practitioners.external_id',
					'people.*'
				)
				->Join('people','practitioners.person_id','=','people.id')
				->where('people.myresultonline_id','=',$request->user()->username)
				->first();
				$pratitioner_external_id = $practitioner['external_id'];
				$filter_status = $request->input('filter_status');
				$patient_name = $request->input('patient_name');
				
				if($patient_name && strpos($patient_name, ',')){
					$name = explode(',',$patient_name);
					$lastname = $name[0];
					$firstname = $name[1];
				}
				
				$patients = PatientVisit::select(
					'patient_visits.external_visit_number as visit_number',
					'patient_visits.created_at as admission_datetime',
					'patient_visits.mgh_datetime',
					'patient_visits.untag_mgh_datetime',
					'patient_visits.hospitalization_plan',
					'patient_visits.chief_complaint as chief_complaint',
					'patient_visits.status as patient_visit_status',
					'patients.internal_id as patient_id',
					'people.lastname as px_last_name',
					'people.firstname as px_first_name',
					'people.middlename as px_middle_name',
					'people.sex as px_sex',
					'people.birthdate as px_birthdate',
					'people.marital_status as px_marital_status',
					'patient_care_providers.pf_amount',
					'patient_care_providers.phic_amount',
					'patient_care_providers.discount',
					'patient_care_providers.instrument_fee',
					'practitioners.external_id as practitioner_id',
					'patient_care_provider_transactions.status'
				)
				->leftJoin('patients','patient_visits.patient_id','=','patients.id')
				->leftJoin('people','patients.person_id','=','people.id')
				->leftJoin('patient_care_providers','patient_care_providers.patient_visit_id','=','patient_visits.id')
				->leftJoin('patient_care_provider_transactions','patient_care_providers.id','=','patient_care_provider_transactions.patient_care_provider_id')
				->leftJoin('practitioners','practitioners.id','=','patient_care_providers.practitioner_id')
				->where('practitioners.external_id','=',$pratitioner_external_id)
				->where('patient_care_provider_transactions.status','=',$filter_status)
				->where(function($whereClause) use ($lastname,$firstname, $patient_name) {
					if($patient_name){
						$whereClause->where('people.lastname','like','%'.($lastname?$lastname:$patient_name).'%')->orWhere('people.firstname','like','%'.($firstname?$firstname:$patient_name).'%');
					}
				})
				->orderBy('patient_visits.created_at', 'desc')
				->paginate(20);
				$get_dashboard_data = $this->get_dashboard_data($pratitioner_external_id);
				$returndata['onqueue'] = $get_dashboard_data['onqueue'];
				$returndata['completed'] = $get_dashboard_data['completed'];
				$returndata['data'] = $patients;
				Log::info('End getting practitioner patients');
			} catch(\Exception $e) {
				$returndata['success'] = false;
				$returndata['message'] = 'Physician get_patients error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}

	public function get_dashboard_data($pratitioner_external_id){
		$onqueue = PatientCareProvider::select()
		->leftJoin('patient_care_provider_transactions','patient_care_providers.id','=','patient_care_provider_transactions.patient_care_provider_id')
		->leftJoin('practitioners','practitioners.id','=','patient_care_providers.practitioner_id')
		->where('practitioners.external_id','=',$pratitioner_external_id)
		->where('status', null)->count();

		$completed = PatientCareProvider::select()
		->leftJoin('patient_care_provider_transactions','patient_care_providers.id','=','patient_care_provider_transactions.patient_care_provider_id')
		->leftJoin('practitioners','practitioners.id','=','patient_care_providers.practitioner_id')
		->where('practitioners.external_id','=',$pratitioner_external_id)
		->where('status', 1)->count();

		$returndata['onqueue'] = $onqueue;
		$returndata['completed'] = $completed;
		return $returndata;
	}

	public function set_professional_fee(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('physician')) {
			DB::beginTransaction();
			try {
				Log::info('Start updating pf');
				Log::info($request);
				$save_patient_care_provider_tx = PatientCareProviderTransaction::where('patient_care_provider_id', $request->PatientCareProvider['id'])->first();
				Log::info($save_patient_care_provider_tx);
				if($save_patient_care_provider_tx->expired_at && $save_patient_care_provider_tx->expired_at <= date('Y-m-d H:i:s')){
					// TODO: ADD VALIDATION EXPIRATION DATE SHOULD COME FROM DB NOT FROM DATA POSTED.
					$returndata['message'] = "You can no longer edit your PF.";
					$returndata['success'] = false;
				}else{
					// Check if already posted.
					$update_pcp = PatientCareProvider::findOrFail($request->PatientCareProvider['id']);
					if($save_patient_care_provider_tx->status != 1){
						$pv = PatientVisit::findOrFail($request->PatientVisit['id']);
						$pvmp = PatientVisitMedicalPackage::where('patient_visit_id','=',$request->PatientVisit['id'])->first();
						if($pvmp){
							Log::info('Has Medical Package.');
							Log::info($pvmp);
						}
						// TODO: CHECK IF NHIP
						$non_pay = array('IHMO', 'PHIC');
						if(in_array($pv->hospitalization_plan, $non_pay) || $pvmp){
							// $update_pcp->pf_amount = $request->PatientCareProvider['pf_amount'];
							// Log::info('Updating PCP.');
							// Log::info($update_pcp);
							// if($update_pcp->save()){
							// 	$save_patient_care_provider_tx->pf_amount = $request->PatientCareProvider['pf_amount'];
							// 	$save_patient_care_provider_tx->status = 1;
							// 	$save_patient_care_provider_tx->expired_at = null;
							// 	$save_patient_care_provider_tx->follow_up_at = null;
							// 	Log::info('Updating PCPT.');
							// 	Log::info($save_patient_care_provider_tx);
							// 	if ($save_patient_care_provider_tx->save()){
							// 		$returndata['message'] = 'PF has been saved.';
							// 	}
							// }
						}else{
							$update_pcp->pf_amount = $request->PatientCareProvider['pf_amount'];
							Log::info('Updating PCP.');
							Log::info('Start NON-HMO/NO MP PCP.');
							Log::info($update_pcp);
							if($update_pcp->save()){
								$save_patient_care_provider_tx->pf_amount = $request->PatientCareProvider['pf_amount'];
								$save_patient_care_provider_tx->status = 0;
								$save_patient_care_provider_tx->expired_at = null;
								$save_patient_care_provider_tx->follow_up_at = null;
								Log::info('Updating PCPT.');
								Log::info($save_patient_care_provider_tx);
								if ($save_patient_care_provider_tx->save()){
									$returndata['message'] = 'PF has been saved.';
								}
							}
						}
					}else{
						$returndata['message'] = "You can no longer edit your PF.";
						$returndata['success'] = false;
					}
				}
				DB::commit();
				Log::info('End updating pf');
			} catch(\Exception $e) {
				DB::rollBack();
				$returndata['success'] = false;
				$returndata['message'] = 'Physician set_professional_fee error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}

	public function get_professional_fee(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('tpi')) {
			try {
				//TODO: add condition IHMO should not be included to query.
				Log::info('Start getting all pf');
				$pcpt = PatientCareProviderTransaction::select(
					'patient_care_provider_transactions.id',
					'patient_care_provider_transactions.pf_amount',
					'patient_care_provider_transactions.status',
					'patient_visits.external_visit_number as visit_number',
					'consultant_types.external_id as consultant_type_id',
					'practitioners.external_id as practitioner_id'
				)
				->leftJoin('patient_care_providers','patient_care_providers.id','=','patient_care_provider_transactions.patient_care_provider_id')
				->leftJoin('practitioners','practitioners.id','=','patient_care_providers.practitioner_id')
				->leftJoin('patient_visits','patient_visits.id','=','patient_care_providers.patient_visit_id')
				->leftJoin('consultant_types','consultant_types.id','=','patient_care_providers.consultant_type_id')
				->where('patient_care_provider_transactions.status', 0)
				->orWhere('patient_care_provider_transactions.expired_at', '<=', date('Y-m-d H:i:s'))
				->get(); 
				Log::info($pcpt);
			   	$returndata['data'] = $pcpt;
				Log::info('End getting all pf');
			} catch(\Exception $e) {
				$returndata['success'] = false;
				$returndata['message'] = 'Physician get_professional_fee error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}

	public function get_follow_up_pf(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('tpi')) {
			try {
				Log::info('Start getting all follow pf');
				$pcpt = PatientCareProviderTransaction::select(
					'patient_care_provider_transactions.id',
					'patient_care_provider_transactions.pf_amount',
					'patient_care_provider_transactions.status',
					'patient_visits.external_visit_number as visit_number',
					'patient_care_providers.consultant_type_id',
					'practitioners.external_id as practitioner_id',
					'patients.internal_id as px_id',
					'people.lastname',
					'people.firstname',
					'people.middlename',
					DB::raw('(select mobile from people as person where person.id = practitioners.person_id) as `mobile`')
				)
				->leftJoin('patient_care_providers','patient_care_providers.id','=','patient_care_provider_transactions.patient_care_provider_id')
				->leftJoin('practitioners','practitioners.id','=','patient_care_providers.practitioner_id')
				->leftJoin('patient_visits','patient_visits.id','=','patient_care_providers.patient_visit_id')
				->leftJoin('patients','patients.id','=','patient_visits.patient_id')
				->leftJoin('people','people.id','=','patients.person_id')
				->Where('patient_care_provider_transactions.follow_up_at', '<=', date('Y-m-d H:i:s'))
               	// ->orderBy('patient_care_provider_transactions.created_at', 'desc')
				->get(); 
				Log::info($pcpt);
				$follow_up_pf = array();
				foreach($pcpt as $pcpt_key=>$patient_care_provider_transaction){
					$config = Configuration::where('id','sms_mgh_follow_up_template')->first();
					$default_pf_config = Configuration::where('id','physician_default_pf')->first();
					if($config->value){
						$sms = SmsTemplate::select('subject','content')
								->where('id',$config->value)
								->first();
						$onlinepf_link_domain_name = Configuration::where('id','domain_name')->first();
						$onlinepf_link = $onlinepf_link_domain_name->value.'/physicians/professional_fee/'.$patient_care_provider_transaction['visit_number'].'/'.$patient_care_provider_transaction['px_id'].'/'.$patient_care_provider_transaction['practitioner_id'];
						$patient_name = $patient_care_provider_transaction['lastname'].', '.$patient_care_provider_transaction['firstname'].' '.$patient_care_provider_transaction['middlename'];
						$sms->content = str_replace('$onlinepf_link', $onlinepf_link, $sms->content);
						$sms->content = str_replace('$patient_name', $patient_name, $sms->content);
						$sms->content = str_replace('$default_pf', $default_pf_config->value, $sms->content);
						$follow_up_pf[$pcpt_key]['id']=$patient_care_provider_transaction['id'];
						$follow_up_pf[$pcpt_key]['message']=$sms->content;
						$follow_up_pf[$pcpt_key]['mobile']=$patient_care_provider_transaction['mobile'];
					}
				}
				$returndata['data'] = $follow_up_pf;
				Log::info('End getting all follow pf');
			} catch(\Exception $e) {
				$returndata['success'] = false;
				$returndata['message'] = 'Physician get_professional_fee error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}
	
	public function set_pcp_transaction(Request $request) {

    	$returndata = array('success'=>true,'message'=>null,'data'=>null);

		if ($request->user()->tokenCan('tpi')) {
			DB::beginTransaction();
			try {
				Log::info('Start updating pcp_transaction');
				Log::info($request);
				$update_pcpt = PatientCareProviderTransaction::findOrFail($request->id);
				if($request->follow_up)
					$update_pcpt->follow_up_at = null;
				else{
					$update_pcpt->status = $request->status;
					$update_pcpt->expired_at = null;
					$update_pcpt->follow_up_at = null;
				}
					
				if($update_pcpt->save()){
					if(!$request->follow_up){
						$update_pcp = PatientCareProvider::findOrFail($update_pcpt->patient_care_provider_id);
						$update_pcp->pf_amount = $request->pf_amount;
						if($update_pcp->save())
							$returndata['message'] = 'PatientCareProviderTransaction has been updated.';
					}else
						$returndata['message'] = 'PatientCareProviderTransaction has been updated.';
				}
				Log::info('End updating pcp_transaction');
				DB::commit();
			} catch(\Exception $e) {
				DB::rollBack();
				$returndata['success'] = false;
				$returndata['message'] = 'Physician set_pcp_transaction error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {

			$returndata['success'] = false;
			$returndata['message'] = 'Undefined user scope!';
		}

		// $returndata['data'] = $request->user();

		return $returndata;
	}
}
