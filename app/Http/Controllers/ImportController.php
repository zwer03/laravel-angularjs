<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Response;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\Interfaces\UserInterface;


use App\People;
use App\Patient;
use App\User;
use App\PatientVisit;
use App\Practitioner;
use App\ConsultantType;
use App\PatientCareProvider;
use App\PatientCareProviderTransaction;
use App\SmsTemplate;
use App\Configuration;
use App\Hmo;
use App\MedicalPackage;
use App\PatientVisitMedicalPackage;
use App\PatientVisitHmo;
use App\HmoConsultantTypePf;

use App\IdentificationType;
use App\PersonIdentification;

class ImportController extends Controller
{
    
	public function his_posts(UserInterface $iUser, Request $request) {

		$returndata = array('success'=>true,'message'=>null, 'mobile'=>null);
		Log::info('Start of HIS post.');
		if ($request->user()->tokenCan('tpi')) {

			$user_id = $request->user()->id;

			$new_physician_user_id = null;
			$new_user_id = null;
			$hmo_id = null;
			$medical_package_id = null;
			$non_pay = false;
			DB::beginTransaction();
			Log::info($request);
			try {
				$transactions = $request->Transaction;
				foreach ($transactions as $tx) {
					$post_visit = $tx['PatientVisit'];
					$post_patient = $tx['Patient'];

					if($post_visit['hospitalization_plan'] == 'IHMO' || $post_visit['membership_id'] == '1036')  // NHIP membership_id = 1036
						$non_pay = true;
					if($post_visit['status'] == 'A' || $post_visit['status'] == 'F' || $post_visit['status'] == 'U' || $post_visit['status'] == 'X'){
						$patient_name = null;
						$patient_exist = Patient::select(
							'patients.id as patient_id',
							'patients.internal_id as patient_internal_id',
							'patients.laboratory_id as patient_laboratory_id',
							'patients.registered_date as patient_registered_date',
							'patients.registered_time as patient_registered_time',
							'people.*'
						)
						->leftJoin('people','patients.person_id','=','people.id')
						->where('patients.internal_id','=',$post_patient['id'])->first();
						
		
						// $mro_id = null;
						$patient_id = null;
						
						if ($patient_exist) {
							Log::info('Patient already exists. Update patient record.');
							Log::info($patient_exist);
							// $mro_id = $patient_exist->myresultonline_id;
							$patient_id = $patient_exist->patient_id;


							$save_people = People::findOrFail($patient_exist->id);
							// $save_people->myresultonline_id = $mro_id;
							$save_people->title_id = null;
							$save_people->firstname = $post_patient['first_name'];
							$save_people->middlename = $post_patient['middle_name'];
							$save_people->lastname = $post_patient['last_name'];
							$save_people->birthdate = date('Y-m-d', strtotime($post_patient['birthdate']));
							$save_people->sex = $post_patient['sex'];
							$save_people->marital_status = $post_patient['marital_status'];
							// $save_people->mobile = $post_patient['telephone_numbers'];
							// $save_people->posted = 1;
							// $save_people->entry_datetime = date('Y-m-d H:i:s');
							// $save_people->user_id = $user_id;
							$save_people->save();
						} else {
							Log::info('Patient does not exist. Create new patient record and people.');
							// $new_user = array(
							// 	'external_id' => $post_patient['id'],
							// 	'first_name' => $post_patient['first_name'],
							// 	'middle_name' => $post_patient['middle_name'],
							// 	'last_name' => $post_patient['last_name'],
							// 	'birthdate' => $post_patient['birthdate'],
							// 	'sex' => $post_patient['sex'],
							// 	'email' => $post_patient['email'],
							// 	'role' => 'ROLE_PATIENT'
							// );
							// $new_user['birthdate'] = date("dmY", strtotime($new_user['birthdate']));
							// Log::info($new_user['birthdate'].'Patient Password');
							// $password = Hash::make(strtoupper($new_user['birthdate']));

							// $save_user = new User;
							// $save_user->name = $new_user['first_name'].' '.$new_user['middle_name'].' '.$new_user['last_name'];
							// $save_user->email = (isset($new_user['email']) && !empty($new_user['email']))?$new_user['email']:$new_user['external_id'];
							// $save_user->username = $new_user['external_id'];
							// $save_user->password = $password;
							// $save_user->role = $new_user['role'];
							// $save_user->save();

							// $new_user_id = $save_user->id;


							// $mro_id = $new_user['external_id'];

							$save_people = new People;
							// $save_people->myresultonline_id = $mro_id;
							$save_people->title_id = null;
							$save_people->firstname = $post_patient['first_name'];
							$save_people->middlename = $post_patient['middle_name'];
							$save_people->lastname = $post_patient['last_name'];
							$save_people->birthdate = date('Y-m-d', strtotime($post_patient['birthdate']));
							$save_people->sex = $post_patient['sex'];
							$save_people->marital_status = $post_patient['marital_status'];
							// $save_people->mobile = $post_patient['telephone_numbers'];
							$save_people->posted = 1;
							$save_people->entry_datetime = date('Y-m-d H:i:s');
							$save_people->user_id = $user_id;
							
							if ($save_people->save()) {

								$save_patient = new Patient;
								$save_patient->internal_id = $post_patient['id'];
								$save_patient->person_id = $save_people->id;
								// $save_patient->laboratory_id = $mro_settings['mrolaboratory_id'];
								$save_patient->registered_date = date('Y-m-d');
								$save_patient->registered_time = date('H:i:s');
								$save_patient->entry_datetime = date('Y-m-d H:i:s');
								$save_patient->user_id = $user_id;
								$save_patient->save();
								$patient_id = $save_patient->id;
								// Log::info($save_patient);
							}
						}
						// Saving Patient Visit
						$existing_patient_visit = PatientVisit::select('patient_visits.*')
						->leftJoin('patient_care_providers','patient_care_providers.patient_visit_id','=','patient_visits.id')
						->leftJoin('practitioners','patient_care_providers.practitioner_id','=','practitioners.id')
						->where('patient_visits.external_id','=',$post_visit['id'])
						->where('patient_visits.patient_id','=',$patient_id)
						->where('practitioners.external_id','=',$post_visit['PatientCareProvider'][0]['practitioner_id'])
						->first();
						Log::info('external id: '.$post_visit['id']);
						Log::info('patient id: '.$patient_id);
						Log::info('practitionerid: '.$post_visit['PatientCareProvider'][0]['practitioner_id']);
						Log::info($existing_patient_visit);
						if (!$existing_patient_visit) {
							Log::info('Start process: new patient visit');
							$save_patient_visit = new PatientVisit;
							$save_patient_visit->external_id = $post_visit['id'];
							$save_patient_visit->external_visit_number = $post_visit['external_visit_number'];
							$save_patient_visit->patient_id = $patient_id;
							$save_patient_visit->patient_type = $post_visit['patient_type'];
							$save_patient_visit->created_at = $post_visit['created_at'];
							$save_patient_visit->chief_complaint = $post_visit['chief_complaint'];
							$save_patient_visit->membership_id = $post_visit['membership_id'];
							$save_patient_visit->bed_room = $post_visit['bed_room'];
							$save_patient_visit->final_diagnosis = $post_visit['final_diagnosis'];
							$save_patient_visit->status = $post_visit['status'];
							$save_patient_visit->hospitalization_plan = $post_visit['hospitalization_plan'];
							$save_patient_visit->mgh_datetime = $post_visit['mgh_datetime']; // Can be remove
							$save_patient_visit->untag_mgh_datetime = $post_visit['untag_mgh_datetime']; // Can be remove
							$save_patient_visit->cancel_datetime = $post_visit['cancel_datetime']; // Can be remove
							$save_patient_visit->save();

							$patient_visit_id = $save_patient_visit->id;

							// Start Patient Visit Hmo
							if(isset($post_visit['PatientVisitHmo']) && !empty($post_visit['PatientVisitHmo'])){
								Log::info('Has PatientVisitHmo.');
								foreach($post_visit['PatientVisitHmo'] as $patient_visit_hmo){
									
									$posted_patient_visit_hmo = $patient_visit_hmo['Hmo'];
									
									if (isset($posted_patient_visit_hmo['id']) && !empty($posted_patient_visit_hmo['id']) && ($posted_patient_visit_hmo['id'] != 0 || $posted_patient_visit_hmo['id'] != '0')) {
										$existing_hmo = Hmo::where('external_id','=',$posted_patient_visit_hmo['id'])->first();
										if ($existing_hmo) {
											Log::info('Hmo already exists.');
											$save_hmo = Hmo::findOrFail($existing_hmo->id);
											$save_hmo->name = $posted_patient_visit_hmo['name'];
											$save_hmo->save();
											$hmo_id = $save_hmo->id;
										} else {
											Log::info('Hmo does not exist.');
											$save_hmo = new Hmo;
											$save_hmo->external_id = $posted_patient_visit_hmo['id'];
											$save_hmo->name = $posted_patient_visit_hmo['name'];
											$save_hmo->save();
											$hmo_id = $save_hmo->id;
										}
										Log::info('Hmo ID: '.$hmo_id);
										if ($hmo_id) {
											Log::info('Saving of Patient Visit Hmo');
											$save_patient_visit_hmo = new PatientVisitHmo;
											$save_patient_visit_hmo->patient_visit_id = $patient_visit_id;
											$save_patient_visit_hmo->hmo_id = $hmo_id;
											
											if($save_patient_visit_hmo->save()){
												Log::info('Patient Visit Hmo has been saved.');
											}
										}
									}
								}
							}

							// Start Patient Visit Medical Package
							if(isset($post_visit['PatientVisitMedicalPackage']) && !empty($post_visit['PatientVisitMedicalPackage'])){
								Log::info('Has PatientVisitMedicalPackage.');
								$non_pay = true;
								foreach($post_visit['PatientVisitMedicalPackage'] as $patient_visit_medical_package){
									
									$posted_patient_visit_medical_package = $patient_visit_medical_package['MedicalPackage'];
									
									if (isset($posted_patient_visit_medical_package['id']) && !empty($posted_patient_visit_medical_package['id']) && ($posted_patient_visit_medical_package['id'] != 0 || $posted_patient_visit_medical_package['id'] != '0')) {
										$existing_medical_package = MedicalPackage::where('external_id','=',$posted_patient_visit_medical_package['id'])->first();
										if ($existing_medical_package) {
											Log::info('MedicalPackage already exists.');
											$save_medical_package = MedicalPackage::findOrFail($existing_medical_package->id);
											$save_medical_package->name = $posted_patient_visit_medical_package['name'];
											$save_medical_package->save();
											$medical_package_id = $save_medical_package->id;
										} else {
											Log::info('MedicalPackage does not exist.');
											$save_medical_package = new MedicalPackage;
											$save_medical_package->external_id = $posted_patient_visit_medical_package['id'];
											$save_medical_package->name = $posted_patient_visit_medical_package['name'];
											$save_medical_package->save();
											$medical_package_id = $save_medical_package->id;
										}
										Log::info('MedicalPackage ID: '.$medical_package_id);
										if ($medical_package_id) {
											Log::info('Saving of Patient Visit Medical Package');
											$save_patient_visit_medical_package = new PatientVisitMedicalPackage;
											$save_patient_visit_medical_package->patient_visit_id = $patient_visit_id;
											$save_patient_visit_medical_package->medical_package_id = $medical_package_id;
											
											if($save_patient_visit_medical_package->save()){
												Log::info('Patient Visit Medical Package has been saved.');
											}
										}
									}
								}
							}
							// Start Patient Care Provider
							if(isset($post_visit['PatientCareProvider']) && !empty($post_visit['PatientCareProvider'])){
								Log::info('Has Patient Care Provider.');
								foreach($post_visit['PatientCareProvider'] as $pcp){
									$physician_id = null;
									$consultant_type_id = null;
									$posted_physician = $pcp['Practitioner'];
									$posted_consultant_type = $pcp['ConsultantType'];
									
									if (isset($posted_consultant_type['id']) && !empty($posted_consultant_type['id']) && ($posted_consultant_type['id'] != 0 || $posted_consultant_type['id'] != '0')) {
										$existing_consultant_type = ConsultantType::where('external_id','=',$posted_consultant_type['id'])->first();
										if ($existing_consultant_type) {
											Log::info('Consultant Type already exists.');
											$save_consultant_type = ConsultantType::findOrFail($existing_consultant_type->id);
											$save_consultant_type->name = $posted_consultant_type['name'];
											$save_consultant_type->save();
											$consultant_type_id = $save_consultant_type->id;
										} else {
											Log::info('Consultant Type does not exist.');
											$save_consultant_type = new ConsultantType;
											$save_consultant_type->external_id = $posted_consultant_type['id'];
											$save_consultant_type->name = $posted_consultant_type['name'];
											$save_consultant_type->save();
											$consultant_type_id = $save_consultant_type->id;
										}
										Log::info('Consultant Type ID: '.$consultant_type_id);
									}
									if (isset($posted_physician['id']) && !empty($posted_physician['id']) && ($posted_physician['id'] != 0 || $posted_physician['id'] != '0')) {

										$existing_physician = Practitioner::where('external_id','=',$posted_physician['id'])->first();
										if ($existing_physician) {
											Log::info('Practitioner already exists. Update details.');
											// Update Practitioer or Physician Basic Details
											$practitioner = Practitioner::findOrFail($existing_physician->id); // TODO : REMOVE THIS LINE AND TEST.
											$save_physician_people = People::findOrFail($practitioner->person_id);
											$save_physician_people->firstname = $posted_physician['first_name'];
											$save_physician_people->middlename = $posted_physician['middle_name'];
											$save_physician_people->lastname = $posted_physician['last_name'];
											$save_physician_people->mobile = (isset($posted_physician['mobile']))?$posted_physician['mobile']:null;
											$save_physician_people->save();
											$physician_id = $existing_physician->id;
										} else { // create new user for physician
											Log::info('Practitioner does not exist.');
											$physician_username = null;
											if (isset($posted_physician['birthdate']) && !empty($posted_physician['birthdate'])) {
												$physician_password = date("dmY", strtotime($posted_physician['birthdate']));
												// Log::info($posted_physician['birthdate'].'physician bday');
												$physician_password = Hash::make($physician_password);
											}else
												$physician_password = Hash::make(strtoupper($posted_physician['last_name']));

											if (isset($posted_physician['license_number']) && !empty($posted_physician['license_number'])) { // use license number as username and password
												$physician_username = $posted_physician['license_number'];
											}

											if (!$physician_username) {
												$physician_username = 'DOCTOR'.$posted_physician['id'];
											}


											// start creating physician user and demog

											$save_physician_user = new User;
											$save_physician_user->name = $posted_physician['first_name'].' '.$posted_physician['middle_name'].' '.$posted_physician['last_name'];
											$save_physician_user->email = (isset($posted_physician['email']) && !empty($posted_physician['email']))?$posted_physician['email']:$physician_username;
											$save_physician_user->username = $physician_username;
											$save_physician_user->password = $physician_password;
											$save_physician_user->role = 'ROLE_PHYSICIAN';
											$save_physician_user->save();
											$new_physician_user_id = $save_physician_user->id;

											$save_physician_people = new People;
											$save_physician_people->myresultonline_id = $physician_username;
											$save_physician_people->title_id = null;
											$save_physician_people->firstname = $posted_physician['first_name'];
											$save_physician_people->middlename = $posted_physician['middle_name'];
											$save_physician_people->lastname = $posted_physician['last_name'];
											$save_physician_people->birthdate = (isset($posted_physician['birthdate']))?date('Y-m-d', strtotime($posted_physician['birthdate'])):null;
											$save_physician_people->sex = (isset($posted_physician['sex']))?$posted_physician['sex']:null;
											$save_physician_people->marital_status = (isset($posted_physician['marital_status']))?$posted_physician['marital_status']:null;
											$save_physician_people->posted = 1;
											$save_physician_people->entry_datetime = date('Y-m-d H:i:s');
											$save_physician_people->user_id = $user_id;
											$save_physician_people->mobile = (isset($posted_physician['mobile']))?$posted_physician['mobile']:null;
											if ($save_physician_people->save()) {

												// if (isset($posted_physician['license_number']) && !empty($posted_physician['license_number'])) {

												// 	$professional_id_type = null;
												// 	$existing_prc_id_type = IdentificationType::where('type','=',3)->first(); // professional id type
												// 	if ($existing_prc_id_type) {
												// 		$professional_id_type = $existing_prc_id_type->id;
												// 	} else {

												// 		$save_professional_id_type = new IdentificationType;
												// 		$save_professional_id_type->name = 'Professional ID';
												// 		$save_professional_id_type->description = 'Professional ID';
												// 		$save_professional_id_type->type = 3;
												// 		$save_professional_id_type->entry_datetime = date('Y-m-d H:i:s');
												// 		$save_professional_id_type->user_id = $user_id;
												// 		$save_professional_id_type->save();
												// 		$professional_id_type = $save_professional_id_type->id;
												// 	}


												// 	$save_physician_people_professional_identification = new PersonIdentification;
												// 	$save_physician_people_professional_identification->person_id = $save_physician_people->id;
												// 	$save_physician_people_professional_identification->identification_id = $professional_id_type;
												// 	$save_physician_people_professional_identification->reference_number = $posted_physician['license_number'];
												// 	$save_physician_people_professional_identification->entry_datetime = date('Y-m-d H:i:s');
												// 	$save_physician_people_professional_identification->user_id = $user_id;
												// 	$save_physician_people_professional_identification->save();
												// }



												$save_physician = new Practitioner;
												$save_physician->external_id = $posted_physician['id'];
												$save_physician->person_id = $save_physician_people->id;
												$save_physician->active = 1;
												$save_physician->entry_datetime = date('Y-m-d H:i:s');
												$save_physician->user_id = $user_id;
												$save_physician->save();

												$physician_id = $save_physician->id;
											}
										}
										Log::info('Practitioner ID: '.$physician_id);
										// Start Saving PCP
										if ($physician_id) {
											Log::info('Saving of Patient Care Provider');
											$save_patient_care_provider = new PatientCareProvider;
											$save_patient_care_provider->patient_visit_id = $patient_visit_id;
											$save_patient_care_provider->practitioner_id = $physician_id;
											$save_patient_care_provider->consultant_type_id = $consultant_type_id;
											$save_patient_care_provider->pf_amount = $pcp['pf_amount']; // Can be removed
											$save_patient_care_provider->phic_amount = $pcp['phic_amount']; // Can be removed
											$save_patient_care_provider->discount = $pcp['discount']; // Can be removed
											$save_patient_care_provider->instrument_fee = $pcp['instrument_fee']; // Can be removed
											$save_patient_care_provider->created_at = $pcp['created_at'];
											$save_patient_care_provider->created_by = $user_id;
											// Start Saving PCPT
											if($save_patient_care_provider->save()){
												Log::info('Patient Care Provider has been saved.');
												Log::info('Saving of Patient Care Provider Transaction');
												$pcp_id = $save_patient_care_provider->id;
												$save_patient_care_provider_tx = new PatientCareProviderTransaction;
												$save_patient_care_provider_tx->patient_care_provider_id = $pcp_id;
												
												if($non_pay){
													$config = Configuration::where('id','sms_admission_nonpay_template')->first();
													$save_patient_care_provider_tx->status = 1;
												}else
													$config = Configuration::where('id','sms_admission_template')->first();
												

												
												$save_patient_care_provider_tx->created_at = date('Y-m-d H:i:s');
												$save_patient_care_provider_tx->save();
												Log::info('Patient Care Provider Transaction has been saved.');
											}
											
										}
										if (empty($posted_physician['mobile'])) {
											throw new \Exception("Please provide mobile number.", 1);
										}
										$returndata['mobile'] = $posted_physician['mobile'];
										// if($post_visit['status'] == 'A'){
										// 	$config = Configuration::where('id','sms_admission_template')->first();
										// }elseif($post_visit['status'] == 'F'){
										// 	$config = Configuration::where('id','sms_mgh_template')->first();
										// }
										
										if($config->value){
											$sms = SmsTemplate::select('subject','content')
													->where('id',$config->value)
													->first();
											Log::info('SMS TEMPLATE');
											Log::info($sms);
											$onlinepf_link_domain_name = Configuration::where('id','domain_name')->first();
											$onlinepf_link = $onlinepf_link_domain_name->value.'/physicians/professional_fee/'.$post_visit['id'].'/'.$post_patient['id'].'/'.$posted_physician['id'];
											$patient_name = $post_patient['last_name'].', '.$post_patient['first_name'].' '.$post_patient['middle_name'];
											$sms->content = str_replace('$onlinepf_link', $onlinepf_link, $sms->content);
											$sms->content = str_replace('$patient_name', $patient_name, $sms->content);
											$returndata['message'] = $sms->content;
										}
									}
								}
							}

							
						}else{
							Log::info('Start process: existing patient visit');
							if($post_visit['PatientCareProvider'][0]['deleted']){
								Log::info($post_visit['PatientCareProvider'][0]['practitioner_id']);
								$practitioner = Practitioner::where('external_id','=',$post_visit['PatientCareProvider'][0]['practitioner_id'])->first();
								Log::info($practitioner);
								$pcp = PatientCareProvider::where([['practitioner_id','=',$practitioner->id],['patient_visit_id','=',$existing_patient_visit->id]])->first();
								Log::info($pcp);
								$existing_pcpt = PatientCareProviderTransaction::where('patient_care_provider_id','=',$pcp->id)->first();
								
								$existing_pcpt->status = 10;
								$existing_pcpt->cancel_datetime = date('Y-m-d H:i:s');
								Log::info($existing_pcpt);
								if($existing_pcpt->save()){ // AUDIT LOG HERE
									Log::info($post_visit['PatientCareProvider'][0]['practitioner_id'].' has been cancelled.');
									$audit_data = array(
										"user_id" => $request->user()->id,
										"url" => "his_posts",
										"action" => "his_posts",
										"remarks" => $post_visit['PatientCareProvider'][0]['practitioner_id'].' has been voided/cancelled.',
										"device" => null,
										"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
										"device_os" => null,
										"browser" => null,
										"browser_version" => null
									);
									$iUser->audit($audit_data);
								}
							}else{
							
								$change_hosp_plan = false;
								if($existing_patient_visit->hospitalization_plan != $post_visit['hospitalization_plan']){ //AUDIT LOG HERE
									$change_hosp_plan = true;
									Log::info('hosp plan was changed');
									$audit_data = array(
										"user_id" => $request->user()->id,
										"url" => "his_posts",
										"action" => "his_posts",
										"remarks" => 'PATIENT WITH REGISTRY NO:'.$post_visit['external_visit_number'].'. Hospital plan was changed from '.$existing_patient_visit->hospitalization_plan.' to '.$post_visit['hospitalization_plan'],
										"device" => null,
										"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
										"device_os" => null,
										"browser" => null,
										"browser_version" => null
									);
									$iUser->audit($audit_data);
								}
								$existing_patient_visit->chief_complaint = $post_visit['chief_complaint'];
								$existing_patient_visit->icd10 = $post_visit['icd10'];
								$existing_patient_visit->membership_id = $post_visit['membership_id'];
								$existing_patient_visit->bed_room = $post_visit['bed_room'];
								$existing_patient_visit->final_diagnosis = $post_visit['final_diagnosis'];
								$existing_patient_visit->status = $post_visit['status'];
								$existing_patient_visit->hospitalization_plan = $post_visit['hospitalization_plan'];
								$existing_patient_visit->mgh_datetime = ($post_visit['untag_mgh_datetime']?null:$post_visit['mgh_datetime']);
								$existing_patient_visit->untag_mgh_datetime = $post_visit['untag_mgh_datetime'];
								$existing_patient_visit->cancel_datetime = $post_visit['cancel_datetime'];
								if($existing_patient_visit->save())
									Log::info($existing_patient_visit);
								
								// Start Patient Visit Hmo
								if(isset($post_visit['PatientVisitHmo']) && !empty($post_visit['PatientVisitHmo'])){
									Log::info('Has PatientVisitHmo.');
									foreach($post_visit['PatientVisitHmo'] as $patient_visit_hmo){
										
										$posted_patient_visit_hmo = $patient_visit_hmo['Hmo'];
										
										if (isset($posted_patient_visit_hmo['id']) && !empty($posted_patient_visit_hmo['id']) && ($posted_patient_visit_hmo['id'] != 0 || $posted_patient_visit_hmo['id'] != '0')) {
											$existing_hmo = Hmo::where('external_id','=',$posted_patient_visit_hmo['id'])->first();
											if ($existing_hmo) {
												Log::info('Hmo already exists.');
												$save_hmo = Hmo::findOrFail($existing_hmo->id);
												$save_hmo->name = $posted_patient_visit_hmo['name'];
												$save_hmo->save();
												$hmo_id = $save_hmo->id;
											} else {
												Log::info('Hmo does not exist.');
												$save_hmo = new Hmo;
												$save_hmo->external_id = $posted_patient_visit_hmo['id'];
												$save_hmo->name = $posted_patient_visit_hmo['name'];
												$save_hmo->save();
												$hmo_id = $save_hmo->id;
											}
											Log::info('Hmo ID: '.$hmo_id);
											if ($hmo_id) {
												$existing_patient_visit_hmo = PatientVisitHmo::where([['patient_visit_id','=',$existing_patient_visit->id],['hmo_id','=',$hmo_id]])->first();
												if (!$existing_patient_visit_hmo) {
													Log::info('Saving of Patient Visit Hmo');
													$save_patient_visit_hmo = new PatientVisitHmo;
													$save_patient_visit_hmo->patient_visit_id = $existing_patient_visit->id;
													$save_patient_visit_hmo->hmo_id = $hmo_id;
													
													if($save_patient_visit_hmo->save()){
														Log::info('Patient Visit Hmo has been saved.');
													}
												}
											}
										}
									}
								}

								// Start Patient Visit Medical Package
								if(isset($post_visit['PatientVisitMedicalPackage']) && !empty($post_visit['PatientVisitMedicalPackage'])){
									Log::info('Has PatientVisitMedicalPackage.');
									$non_pay = true;
									foreach($post_visit['PatientVisitMedicalPackage'] as $patient_visit_medical_package){
										$medical_package_id = null;
										$posted_patient_visit_medical_package = $patient_visit_medical_package['MedicalPackage'];
										
										if (isset($posted_patient_visit_medical_package['id']) && !empty($posted_patient_visit_medical_package['id']) && ($posted_patient_visit_medical_package['id'] != 0 || $posted_patient_visit_medical_package['id'] != '0')) {
											$existing_medical_package = MedicalPackage::where('external_id','=',$posted_patient_visit_medical_package['id'])->first();
											if ($existing_medical_package) {
												Log::info('MedicalPackage already exists.');
												$save_medical_package = MedicalPackage::findOrFail($existing_medical_package->id);
												$save_medical_package->name = $posted_patient_visit_medical_package['name'];
												$save_medical_package->save();
												$medical_package_id = $save_medical_package->id;
											} else {
												Log::info('MedicalPackage does not exist.');
												$save_medical_package = new MedicalPackage;
												$save_medical_package->external_id = $posted_patient_visit_medical_package['id'];
												$save_medical_package->name = $posted_patient_visit_medical_package['name'];
												$save_medical_package->save();
												$medical_package_id = $save_medical_package->id;
											}
											Log::info('MedicalPackage ID: '.$medical_package_id);
											if ($medical_package_id) {
												$existing_patient_visit_medical_package = PatientVisitMedicalPackage::where([['patient_visit_id','=',$existing_patient_visit->id],['medical_package_id','=',$medical_package_id]])->first();
												if (!$existing_patient_visit_medical_package) {
													Log::info('Saving of Patient Visit Medical Package');
													$save_patient_visit_medical_package = new PatientVisitMedicalPackage;
													$save_patient_visit_medical_package->patient_visit_id = $existing_patient_visit->id;
													$save_patient_visit_medical_package->medical_package_id = $medical_package_id;
													
													if($save_patient_visit_medical_package->save()){
														Log::info('Patient Visit Medical Package has been saved.');
													}
												}
											}
										}
									}
								}
								// Start Patient Care Provider
								if(isset($post_visit['PatientCareProvider']) && !empty($post_visit['PatientCareProvider'])){
									
									foreach($post_visit['PatientCareProvider'] as $pcp){
										$physician_id = null;
										$consultant_type_id = null;
										$consultant_type_pf = null;
										$posted_physician = $pcp['Practitioner'];
										$posted_consultant_type = $pcp['ConsultantType'];
										if (isset($posted_consultant_type['id']) && !empty($posted_consultant_type['id']) && ($posted_consultant_type['id'] != 0 || $posted_consultant_type['id'] != '0')) {
											$existing_consultant_type = ConsultantType::where('external_id','=',$posted_consultant_type['id'])->first();
											if ($existing_consultant_type) {
												$save_consultant_type = ConsultantType::findOrFail($existing_consultant_type->id);
												$save_consultant_type->name = $posted_consultant_type['name'];
												$save_consultant_type->save();
												$consultant_type_pf = $save_consultant_type->default_pf_amount;
												$consultant_type_id = $save_consultant_type->id;
											} else {
												$save_consultant_type = new ConsultantType;
												$save_consultant_type->external_id = $posted_consultant_type['id'];
												$save_consultant_type->name = $posted_consultant_type['name'];
												$save_consultant_type->save();
												$consultant_type_id = $save_consultant_type->id;
											}
										}
										if (isset($posted_physician['id']) && !empty($posted_physician['id']) && ($posted_physician['id'] != 0 || $posted_physician['id'] != '0')) {
											
											$existing_physician = Practitioner::where('external_id','=',$posted_physician['id'])->first();
											if ($existing_physician) {
												// Update Practitioer or Physician Basic Details
												$practitioner = Practitioner::findOrFail($existing_physician->id);
												$save_physician_people = People::findOrFail($practitioner->person_id);
												$save_physician_people->firstname = $posted_physician['first_name'];
												$save_physician_people->middlename = $posted_physician['middle_name'];
												$save_physician_people->lastname = $posted_physician['last_name'];
												$save_physician_people->mobile = (isset($posted_physician['mobile']))?$posted_physician['mobile']:null;
												$save_physician_people->save();
												$physician_id = $existing_physician->id;
											} else { // create new user for physician
												
												$physician_username = null;
												if (isset($posted_physician['birthdate']) && !empty($posted_physician['birthdate'])) {
													$physician_password = date("dmY", strtotime($posted_physician['birthdate']));
													// Log::info($posted_physician['birthdate'].'physician bday');
													$physician_password = Hash::make($physician_password);
												}else
													$physician_password = Hash::make(strtoupper($posted_physician['last_name']));

												if (isset($posted_physician['license_number']) && !empty($posted_physician['license_number'])) { // use license number as username and password
													$physician_username = $posted_physician['license_number'];
												}

												if (!$physician_username) {
													$physician_username = 'DOCTOR'.$posted_physician['id'];
												}


												// start creating physician user and demog

												$save_physician_user = new User;
												$save_physician_user->name = $posted_physician['first_name'].' '.$posted_physician['middle_name'].' '.$posted_physician['last_name'];
												$save_physician_user->email = (isset($posted_physician['email']) && !empty($posted_physician['email']))?$posted_physician['email']:$physician_username;
												$save_physician_user->username = $physician_username;
												$save_physician_user->password = $physician_password;
												$save_physician_user->role = 'ROLE_PHYSICIAN';
												$save_physician_user->save();
												$new_physician_user_id = $save_physician_user->id;

												$save_physician_people = new People;
												$save_physician_people->myresultonline_id = $physician_username;
												$save_physician_people->title_id = null;
												$save_physician_people->firstname = $posted_physician['first_name'];
												$save_physician_people->middlename = $posted_physician['middle_name'];
												$save_physician_people->lastname = $posted_physician['last_name'];
												$save_physician_people->birthdate = (isset($posted_physician['birthdate']))?date('Y-m-d', strtotime($posted_physician['birthdate'])):null;
												$save_physician_people->sex = (isset($posted_physician['sex']))?$posted_physician['sex']:null;
												$save_physician_people->marital_status = (isset($posted_physician['marital_status']))?$posted_physician['marital_status']:null;
												$save_physician_people->posted = 1;
												$save_physician_people->entry_datetime = date('Y-m-d H:i:s');
												$save_physician_people->user_id = $user_id;
												$save_physician_people->mobile = (isset($posted_physician['mobile']))?$posted_physician['mobile']:null;
												if ($save_physician_people->save()) {
													
													// if (isset($posted_physician['license_number']) && !empty($posted_physician['license_number'])) {

													// 	$professional_id_type = null;
													// 	$existing_prc_id_type = IdentificationType::where('type','=',3)->first(); // professional id type
													// 	if ($existing_prc_id_type) {
													// 		$professional_id_type = $existing_prc_id_type->id;
													// 	} else {

													// 		$save_professional_id_type = new IdentificationType;
													// 		$save_professional_id_type->name = 'Professional ID';
													// 		$save_professional_id_type->description = 'Professional ID';
													// 		$save_professional_id_type->type = 3;
													// 		$save_professional_id_type->entry_datetime = date('Y-m-d H:i:s');
													// 		$save_professional_id_type->user_id = $user_id;
													// 		$save_professional_id_type->save();
													// 		$professional_id_type = $save_professional_id_type->id;
													// 	}


													// 	$save_physician_people_professional_identification = new PersonIdentification;
													// 	$save_physician_people_professional_identification->person_id = $save_physician_people->id;
													// 	$save_physician_people_professional_identification->identification_id = $professional_id_type;
													// 	$save_physician_people_professional_identification->reference_number = $posted_physician['license_number'];
													// 	$save_physician_people_professional_identification->entry_datetime = date('Y-m-d H:i:s');
													// 	$save_physician_people_professional_identification->user_id = $user_id;
													// 	$save_physician_people_professional_identification->save();
													// }

													

													$save_physician = new Practitioner;
													$save_physician->external_id = $posted_physician['id'];
													$save_physician->person_id = $save_physician_people->id;
													$save_physician->active = 1;
													$save_physician->entry_datetime = date('Y-m-d H:i:s');
													$save_physician->user_id = $user_id;
													
													if($save_physician->save())
														Log::info($save_physician);
														
													$physician_id = $save_physician->id;
												}
											}

											// Start Saving PCP
											$hmo_default_pf = null;
											$default_pf_config = Configuration::where('id','physician_default_pf')->first();
											// if($hmo_id){ // TODO : REPOSITION & GET THE FIRST HMO, THIS FUNCTION WILL FAIL IF IT DOES HAVE MULTIPLE HMO.
											// 	$hmo_consultant_type_pf = HmoConsultantTypePf::where([['hmo_id','=',$hmo_id],['consultant_type_id','=',$consultant_type_id]])->first();
											// 	$hmo_default_pf = $hmo_consultant_type_pf->default_pf_amount; //Add times the number of days stayed.
											// 	if($post_visit['mgh_datetime']){
											// 		Log::info('Start computation of days stayed');
											// 		$now = time();
											// 		$mgh_datetime = strtotime($post_visit['mgh_datetime']);
											// 		$admission_date = strtotime($post_visit['created_at']);
											// 		$datediff = ($mgh_datetime?$mgh_datetime:$now) - $admission_date;

											// 		$no_of_days = round($datediff / (60 * 60 * 24));
											// 		if($no_of_days)
											// 			$hmo_default_pf = $hmo_consultant_type_pf->default_pf_amount * $no_of_days;
											// 		Log::info('No. of days: '.$no_of_days);
											// 		Log::info('Total default PF: '.$hmo_default_pf);
											// 		Log::info('Start computation of days stayed');
											// 	}
											// }
											$link_expiration_config = Configuration::where('id','physician_link_expiration')->first();
											$mgh_follow_up_config = Configuration::where('id','physician_mgh_follow_up')->first();
											$existing_pcp = PatientCareProvider::where([['patient_visit_id','=',$existing_patient_visit->id],['practitioner_id','=',$physician_id]])->first();
											if (!$existing_pcp) { // Add new Patient Care Provider
												Log::info("PCP does not exist. Create PCP and PCPT.");
												$save_patient_care_provider = new PatientCareProvider;
												$save_patient_care_provider->patient_visit_id = $existing_patient_visit->id;
												$save_patient_care_provider->practitioner_id = $physician_id;
												$save_patient_care_provider->consultant_type_id = $consultant_type_id;
												$save_patient_care_provider->pf_amount = $pcp['pf_amount'];
												$save_patient_care_provider->phic_amount = $pcp['phic_amount'];
												$save_patient_care_provider->discount = $pcp['discount'];
												$save_patient_care_provider->instrument_fee = $pcp['instrument_fee'];
												$save_patient_care_provider->created_at = $pcp['created_at'];
												$save_patient_care_provider->created_by = $user_id;
												if($save_patient_care_provider->save()){
													$save_patient_care_provider_tx = new PatientCareProviderTransaction;
													if($non_pay){
														$config = Configuration::where('id','sms_admission_nonpay_template')->first();
														$save_patient_care_provider_tx->status = 1;
													}else
														$config = Configuration::where('id','sms_admission_template')->first();
													$save_patient_care_provider_tx->patient_care_provider_id = $save_patient_care_provider->id;
													$save_patient_care_provider_tx->save();
												}
											}else{
												Log::info('PCP does exist. Update if necessary.');
												$existing_pcpt = PatientCareProviderTransaction::where('patient_care_provider_id','=',$existing_pcp->id)->first();
												if($hmo_default_pf)
													$existing_pcp->pf_amount = $hmo_default_pf;
												// $existing_pcp->phic_amount = $pcp['phic_amount'];
												// $existing_pcp->discount = $pcp['discount'];

												// RESET PCPT BY DEFAULT
												$save_patient_care_provider_tx = PatientCareProviderTransaction::findOrFail($existing_pcpt->id);
												$save_patient_care_provider_tx->pf_amount = null;
												$save_patient_care_provider_tx->status = null;
												$save_patient_care_provider_tx->follow_up_at = null;
												$save_patient_care_provider_tx->expired_at = null;

												if(!$post_visit['untag_mgh_datetime']){
													if($post_visit['mgh_datetime'] && $post_visit['status'] == 'A'){ // HAS BEEN TAG AS MGHC BEFORE THEN RE-ADMIT
														Log::info('Condition: Empty Untag mghc and has been mghc with Status A');
														$config = Configuration::where('id','sms_admission_template')->first();
														$existing_pcp->pf_amount = 0;
														$audit_data = array(
															"user_id" => $request->user()->id,
															"url" => "his_posts",
															"action" => "his_posts",
															"remarks" => 'CODE #0001 PATIENT WITH REGISTRY NO:'.$post_visit['external_visit_number'].'. Status Changed. Current patient registry status: '.$post_visit['status'],
															"device" => null,
															"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
															"device_os" => null,
															"browser" => null,
															"browser_version" => null
														);
														$iUser->audit($audit_data);
													}elseif($post_visit['status'] == 'U'){ // MAY HAPPEN UPON UNTAGGED MGH
														Log::info('Condition: UNTAGGED MGH');
														$config = Configuration::where('id','sms_untag_mgh_template')->first();
														$existing_pcp->pf_amount = 0;
														$audit_data = array(
															"user_id" => $request->user()->id,
															"url" => "his_posts",
															"action" => "his_posts",
															"remarks" => 'CODE #0002 PATIENT WITH REGISTRY NO:'.$post_visit['external_visit_number'].'. Status Changed. Current patient registry status: '.$post_visit['status'],
															"device" => null,
															"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
															"device_os" => null,
															"browser" => null,
															"browser_version" => null
														);
														$iUser->audit($audit_data);
													}
												}else{ // HAS BEEN UNTAGGED MGHC. DO THIS
													if($post_visit['status'] == 'A' || $post_visit['status'] == 'U'){ //1. UNTAGGED MGH CLEARANCE/MGH RESET PCPT AND PCP AND SET SMS TEMPLATE
														Log::info('Condition: Has been untagged MGHC. Status A OR U.');
														$config = Configuration::where('id','sms_untag_mgh_template')->first();
														$existing_pcp->pf_amount = 0; // RESET VALUE OF PF AMOUNT IN PCP
														$audit_data = array(
															"user_id" => $request->user()->id,
															"url" => "his_posts",
															"action" => "his_posts",
															"remarks" => 'CODE #0003 PATIENT WITH REGISTRY NO:'.$post_visit['external_visit_number'].'. Status Changed. Current patient registry status: '.$post_visit['status'],
															"device" => null,
															"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
															"device_os" => null,
															"browser" => null,
															"browser_version" => null
														);
														$iUser->audit($audit_data);
													}
												}
												if($post_visit['status'] == 'F'){ // MGHC
													Log::info('Condition: Status F.');
													if($existing_pcp->pf_amount == 0  && !$existing_pcpt->status){ // ON QUEUE SET DEFAULT PF AND EXP. DATETIME
														Log::info('Condition: PF amount is eq to 0 and PCPT status is not 1.');
														$config = Configuration::where('id','sms_mgh_template')->first();
														if($hmo_default_pf){
															$save_patient_care_provider_tx->status = 0;
															$save_patient_care_provider_tx->pf_amount = $hmo_default_pf; // DEFAULT PF PER HMO
														}elseif($consultant_type_pf)
															$save_patient_care_provider_tx->pf_amount = $consultant_type_pf; // DEFAULT PF PER ROLE TYPE
														else
															$save_patient_care_provider_tx->pf_amount = $default_pf_config->value; // GENERAL DEFAULT PF

														$save_patient_care_provider_tx->follow_up_at = date('Y-m-d H:i:s',strtotime($mgh_follow_up_config->value,strtotime($post_visit['mgh_datetime'])));
														$save_patient_care_provider_tx->expired_at = date('Y-m-d H:i:s',strtotime($link_expiration_config->value,strtotime($post_visit['mgh_datetime'])));
													}else{ // ALREADY COMPLETED? RETAIN PF AMOUNT AND STATUS 
														Log::info('Condition: PF amount is not eq to 0 and PCPT status is 1.');
														$save_patient_care_provider_tx->pf_amount = $existing_pcpt->pf_amount;
														$save_patient_care_provider_tx->status = $existing_pcpt->status;
													}
												}
												Log::info($existing_pcp);
												if($non_pay){
													Log::info("PCP amount set to 0 when Non-pay"); //This may happen upon change of hosp plan. From SP to Non-pay
													$existing_pcp->pf_amount = 0; // CAN BE REMOVED IF THEY DON'T WANT TO RESET PF AMOUNT TO 0 WHEN CHANGE HOSP PLAN TO IHMO.
													$save_patient_care_provider_tx->status = 1;
													if($post_visit['status'] == 'F'){ // DONT SEND SMS WHEN NONPAY UPON MGHC
														unset($config);
													}
													$has_med_package = '';
													$has_med_package = (isset($medical_package_id)?'ID '.$medical_package_id:'NONE');
													$audit_data = array(
														"user_id" => $request->user()->id,
														"url" => "his_posts",
														"action" => "his_posts",
														"remarks" => 'CODE #0004 PATIENT WITH REGISTRY NO:'.$post_visit['external_visit_number'].' WAS TAGGED AS '.$post_visit['hospitalization_plan'].' '.$post_visit['membership_id'].'. Has packaged? '.$has_med_package ,
														"device" => null,
														"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
														"device_os" => null,
														"browser" => null,
														"browser_version" => null
													);
													$iUser->audit($audit_data);
												}
												// SAVING OF PCP AND PCPT
												if($existing_pcp->save()){
													Log::info($save_patient_care_provider_tx);
													// if($change_hosp_plan)
													// 	if($non_pay)
													// 		$save_patient_care_provider_tx->status = 1;
													// 	else
													// 		$save_patient_care_provider_tx->status = null;
													$save_patient_care_provider_tx->save();
												}
												
											}
											
											if($change_hosp_plan){
												unset($config);
												$config = Configuration::where('id','sms_change_hosp_plan')->first();
											}

											if(isset($config->value)){
												$sms = SmsTemplate::select('subject','content')
														->where('id',$config->value)
														->first();
												Log::info('SMS TEMPLATE');
												Log::info($sms);
												$onlinepf_link_domain_name = Configuration::where('id','domain_name')->first();
												$onlinepf_link = $onlinepf_link_domain_name->value.'/physicians/professional_fee/'.$post_visit['id'].'/'.$post_patient['id'].'/'.$posted_physician['id'];
												$patient_name = $post_patient['last_name'].', '.$post_patient['first_name'].' '.$post_patient['middle_name'];
												$sms->content = str_replace('$onlinepf_link', $onlinepf_link, $sms->content);
												$sms->content = str_replace('$patient_name', $patient_name, $sms->content);
												$returndata['message'] = $sms->content;
											}

											$returndata['mobile'] = $posted_physician['mobile'];
										}
									}
								}
							
							}
						}
					}else
						$returndata = array('success'=>false,'message'=>'Invalid Status', 'mobile'=>null);
				}
				Log::info($returndata);
				DB::commit();
				// DB::rollBack();
			} catch (\Exception $e) {

				// deleting created user for patient and physician because users table is MyISAM
				// if ($new_user_id) { User::findOrFail($new_user_id)->delete(); }
				// if ($new_physician_user_id) { User::findOrFail($new_physician_user_id)->delete(); }
				
				DB::rollBack();

				$returndata['success'] = false;
				$returndata['message'] = 'HIS post failed! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {
			$returndata['success'] = false;
			$returndata['message'] = 'HIS post failed! Stacktrace: Invalid token scope!';
		}

		return Response::json($returndata);
	}
}
