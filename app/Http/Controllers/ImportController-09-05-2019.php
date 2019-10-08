<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Response;
use Illuminate\Support\Facades\Hash;
use DB;



use App\People;
use App\Patient;
use App\User;
use App\Physician;
use App\LaboratoryTestGroup;
use App\LaboratoryTest;
use App\LaboratoryTestGroupDetail;
use App\LaboratoryTestOrder;
use App\LaboratoryPatientOrder;
use App\LaboratoryPatientTransaction;
use App\LaboratoryTestResult;
use App\LaboratoryTestOrderDetail;
use App\LaboratoryTestOrderResult;
use App\LaboratoryTestResultSpecimen;
use App\IdentificationType;
use App\PersonIdentification;
use App\LaboratoryPatientOrderPhysician;

class ImportController extends Controller
{
    
	public function weblis_post_results(Request $request) {

		$returndata = array('success'=>true,'message'=>null);

		if ($request->user()->tokenCan('weblis')) {

			$user_id = $request->user()->id;

			$new_physician_user_id = null;
			$new_user_id = null;

			DB::beginTransaction();

			try {
			
				$mro_settings = $request->MROSetting;
				$post_patient = $request->Patient;
				$post_orders = $request->Orders;
				// Log::info($post_orders);
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

				
				$mro_id = null;
				$patient_id = null;
				
				if ($patient_exist) {
					$mro_id = $patient_exist->myresultonline_id;
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
					$save_people->mobile = $post_patient['telephone_numbers'];
					// $save_people->posted = 1;
					// $save_people->entry_datetime = date('Y-m-d H:i:s');
					// $save_people->user_id = $user_id;
					$save_people->save();
				} else {

					$new_user = array(
						'external_id' => $post_patient['id'],
						'first_name' => $post_patient['first_name'],
						'middle_name' => $post_patient['middle_name'],
						'last_name' => $post_patient['last_name'],
						'birthdate' => $post_patient['birthdate'],
						'sex' => $post_patient['sex'],
						'email' => $post_patient['email'],
						'role' => 'ROLE_PATIENT'
					);
					$new_user['birthdate'] = date("dmY", strtotime($new_user['birthdate']));
					Log::info($new_user['birthdate'].'Patient Password');
					$password = Hash::make(strtoupper($new_user['birthdate']));

					$save_user = new User;
					$save_user->name = $new_user['first_name'].' '.$new_user['middle_name'].' '.$new_user['last_name'];
					$save_user->email = (isset($new_user['email']) && !empty($new_user['email']))?$new_user['email']:$new_user['external_id'];
					$save_user->username = $new_user['external_id'];
					$save_user->password = $password;
					$save_user->role = $new_user['role'];
			        $save_user->save();

			        $new_user_id = $save_user->id;


			        $mro_id = $new_user['external_id'];

			        $save_people = new People;
			        $save_people->myresultonline_id = $mro_id;
		        	$save_people->title_id = null;
		        	$save_people->firstname = $post_patient['first_name'];
					$save_people->middlename = $post_patient['middle_name'];
					$save_people->lastname = $post_patient['last_name'];
					$save_people->birthdate = date('Y-m-d', strtotime($post_patient['birthdate']));
					$save_people->sex = $post_patient['sex'];
					$save_people->marital_status = $post_patient['marital_status'];
					$save_people->mobile = $post_patient['telephone_numbers'];
					$save_people->posted = 1;
					$save_people->entry_datetime = date('Y-m-d H:i:s');
					$save_people->user_id = $user_id;
			        
			        if ($save_people->save()) {

			        	$save_patient = new Patient;
			        	$save_patient->internal_id = $post_patient['id'];
			        	$save_patient->person_id = $save_people->id;
			        	$save_patient->laboratory_id = $mro_settings['mrolaboratory_id'];
			        	$save_patient->registered_date = date('Y-m-d');
			        	$save_patient->registered_time = date('H:i:s');
			        	$save_patient->entry_datetime = date('Y-m-d H:i:s');
			        	$save_patient->user_id = $user_id;
			        	$save_patient->save();
						$patient_id = $save_patient->id;
			        }
				}



				// process test_groups first, formatting all test groups
				$posted_test_groups = array();
	    		$posted_test_group_ids = array();

	    		foreach ($post_orders as $order) {

	    			foreach ($order['TestOrder']['TestResult'] as $order_test_result) {

	    				if (!isset($posted_test_groups[$order_test_result['test_group_id']])) {

	    					array_push($posted_test_group_ids, $order_test_result['test_group_id']);

	    					$posted_test_groups[$order_test_result['test_group_id']] = array(
	    						"id" => $order_test_result['test_group_id'],
	    						"internal_id" => null,
	    						"name" => $order_test_result['TestGroup']['name'],
		                        "description" => $order_test_result['TestGroup']['description'],
		                        "short_code" => $order_test_result['TestGroup']['short_code'],
		                        "specimen_type_id" => $order_test_result['TestGroup']['specimen_type_id'],
		                        "panel_test" => $order_test_result['TestGroup']['panel_test'],
		                        "primary_test_group_id" => $order_test_result['TestGroup']['primary_test_group_id'],
		                        "price" => $order_test_result['TestGroup']['price'],
		                        "department_id" => $order_test_result['TestGroup']['department_id'],
		                        "enabled" => $order_test_result['TestGroup']['enabled'],
		                        "TestCode" => array()
	    					);
	    				}


	    				foreach ($order_test_result['TestOrderDetail'] as $order_test_order_detail) {

	    					if (!isset($posted_test_groups[$order_test_result['test_group_id']]['TestCode'][$order_test_order_detail['test_id']])) {

	    						$posted_test_groups[$order_test_result['test_group_id']]['TestCode'][$order_test_order_detail['test_id']] = array(
	    							"id" => $order_test_order_detail['TestCode']['id'],
	    							"internal_id" => null,
	                                "type" => $order_test_order_detail['TestCode']['type'],
	                                "name" => $order_test_order_detail['TestCode']['name'],
	                                "description" => $order_test_order_detail['TestCode']['description'],
	                                "short_code" => $order_test_order_detail['TestCode']['short_code'],
	                                "enabled" => $order_test_order_detail['TestCode']['enabled']
	    						);
	    					}

	    					if (isset($order_test_order_detail['panel_test_group_id']) && !empty($order_test_order_detail['panel_test_group_id'])) { // panel test


	    						if (!isset($posted_test_groups[$order_test_order_detail['panel_test_group_id']])) {

		        					array_push($posted_test_group_ids, $order_test_order_detail['panel_test_group_id']);

		        					$posted_test_groups[$order_test_order_detail['panel_test_group_id']] = array(
		        						"id" => $order_test_order_detail['panel_test_group_id'],
		        						"internal_id" => null,
		        						"name" => $order_test_order_detail['TestGroup']['name'],
				                        "description" => $order_test_order_detail['TestGroup']['description'],
				                        "short_code" => $order_test_order_detail['TestGroup']['short_code'],
				                        "specimen_type_id" => $order_test_order_detail['TestGroup']['specimen_type_id'],
				                        "panel_test" => 1,
				                        "primary_test_group_id" => $order_test_result['test_group_id'],
				                        "price" => $order_test_order_detail['TestGroup']['price'],
				                        "department_id" => $order_test_order_detail['TestGroup']['department_id'],
				                        "enabled" => $order_test_order_detail['TestGroup']['enabled'],
				                        "TestCode" => array()
		        					);
		        				}


	        					if (!isset($posted_test_groups[$order_test_order_detail['panel_test_group_id']]['TestCode'][$order_test_order_detail['test_id']])) {

	        						$posted_test_groups[$order_test_order_detail['panel_test_group_id']]['TestCode'][$order_test_order_detail['test_id']] = array(
	        							"id" => $order_test_order_detail['TestCode']['id'],
	        							"internal_id" => null,
		                                "type" => $order_test_order_detail['TestCode']['type'],
		                                "name" => $order_test_order_detail['TestCode']['name'],
		                                "description" => $order_test_order_detail['TestCode']['description'],
		                                "short_code" => $order_test_order_detail['TestCode']['short_code'],
		                                "enabled" => $order_test_order_detail['TestCode']['enabled']
	        						);
	        					}
	    					}
	    				}
	    			}
	    		}

	    		$test_groups = LaboratoryTestGroup::select(
	    							'laboratory_test_groups.id as group_id',
	    							'laboratory_test_groups.internal_id as group_internal_id',
	    							'laboratory_test_groups.name as group_name',
	    							'laboratory_test_groups.description as group_description',
	    							'laboratory_test_groups.short_code as group_short_code',
	    							'laboratory_test_groups.panel_test as group_panel_test',
	    							'laboratory_test_groups.primary_test_group_id as group_primary_test_group_id',
	    							'laboratory_tests.id as test_id',
	    							'laboratory_tests.internal_id as test_internal_id',
	    							'laboratory_tests.name as test_name',
	    							'laboratory_tests.description as test_description'
	    						)
	    						->leftJoin('laboratory_test_group_details','laboratory_test_groups.id','=','laboratory_test_group_details.test_group_id')
	    						->leftJoin('laboratory_tests','laboratory_test_group_details.test_id','=','laboratory_tests.id')
	    						->whereIn('laboratory_test_groups.internal_id',$posted_test_group_ids)
	    						->get();

	    		// $returndata['data'] = $posted_test_group_ids;

	    		$formatted_test_groups = array();
	    		if ($test_groups) {
	    			foreach ($test_groups as $test_group) {
	    				if (!isset($formatted_test_groups[$test_group->group_internal_id])) {
	    					$formatted_test_groups[$test_group->group_internal_id] = array(
	    						'id' => $test_group->group_id,
	    						'internal_id' => $test_group->group_internal_id,
	    						'name' => $test_group->group_name,
	    						'TestCode' => array()
	    					);
	    				}

	    				if (!isset($formatted_test_groups[$test_group->group_internal_id]['TestCode'][$test_group->test_internal_id])) {
	    					$formatted_test_groups[$test_group->group_internal_id]['TestCode'][$test_group->test_internal_id] = array(
	    						'id' => $test_group->test_id,
	    						'internal_id' => $test_group->test_internal_id,
	    						'name' => $test_group->test_name
	    					);
	    				}
	    			}
	    		}
	    		
	    		// checking of posted test groups and test code existence
	    		foreach ($posted_test_groups as &$posted_test_group) {

	    			if ($formatted_test_groups && isset($formatted_test_groups[$posted_test_group['id']])) { //existing test group

	    				$test_group_id = $formatted_test_groups[$posted_test_group['id']]['id'];
	    				$posted_test_group['internal_id'] = $test_group_id;

	    				foreach ($posted_test_group['TestCode'] as &$posted_test_code) {

	    					if (isset($formatted_test_groups[$posted_test_group['id']]['TestCode'][$posted_test_code['id']])) { //existing test code
	    						$posted_test_code['internal_id'] = $formatted_test_groups[$posted_test_group['id']]['TestCode'][$posted_test_code['id']]['id'];
	    					} else { // not existing test code in test group

	    						$test_id = null;
	    						$existing_test_code = LaboratoryTest::where('internal_id','=',$posted_test_code['id'])->first();
	    						if ($existing_test_code) {
	    							$test_id = $existing_test_code->id;
	    						} else {

	    							$save_test_code = new LaboratoryTest;
	    							$save_test_code->laboratory_id = $mro_settings['mrolaboratory_id'];
	    							$save_test_code->internal_id = $posted_test_code['id'];
	    							$save_test_code->name = $posted_test_code['name'];
	    							$save_test_code->text = $posted_test_code['name'];
	    							$save_test_code->description = $posted_test_code['description'];
	    							$save_test_code->enabled = $posted_test_code['enabled'];
	    							$save_test_code->entry_datetime = date('Y-m-d H:i:s');
	    							$save_test_code->user_id = $user_id;
	    							$save_test_code->validated = 1;
	    							$save_test_code->validated_datetime = date('Y-m-d H:i:s');
	    							$save_test_code->validating_user_id = $user_id;
	    							$save_test_code->posted = 1;
	    							$save_test_code->posted_datetime = date('Y-m-d H:i:s');

	    							if ($save_test_code->save()) {
	    								$test_id = $save_test_code->id;
	    							}
	    						}

	    						if ($test_id) {

	    							$save_test_group_detail = new LaboratoryTestGroupDetail;
	    							$save_test_group_detail->test_group_id = $test_group_id;
	    							$save_test_group_detail->test_id = $test_id;
	    							$save_test_group_detail->enabled = 1;
	    							$save_test_group_detail->entry_datetime = date('Y-m-d H:i:s');
	    							$save_test_group_detail->user_id = $user_id;
	    							$save_test_group_detail->validated = 1;
	    							$save_test_group_detail->validated_datetime = date('Y-m-d H:i:s');
	    							$save_test_group_detail->validating_user_id = $user_id;
	    							$save_test_group_detail->posted = 1;
	    							$save_test_group_detail->posted_datetime = date('Y-m-d H:i:s');
	    							$save_test_group_detail->save();
	    						} else {
	    							throw new \Exception("Unable to save laboratory test", 1);
	    						}
	    					}
	    				}
	    			} else { // not existing test group


	    				$primary_test_group_id = null;
	    				if ($posted_test_group['primary_test_group_id']) {
	    					$primary_test_group_exist = LaboratoryTestGroup::where('internal_id','=',$posted_test_group['primary_test_group_id'])->first();

	    					if ($primary_test_group_exist) {
	    						$primary_test_group_id = $primary_test_group_exist->id;
	    					} else {

	    						if ($posted_test_groups[$posted_test_group['primary_test_group_id']]) {

	    							$this_primary_test_group = $posted_test_groups[$posted_test_group['primary_test_group_id']];
	    							$save_new_test_group = new LaboratoryTestGroup;
	    							$save_new_test_group->laboratory_id = $mro_settings['mrolaboratory_id'];
			        				$save_new_test_group->internal_id = $this_primary_test_group['id'];
			        				$save_new_test_group->name = $this_primary_test_group['name'];
			        				$save_new_test_group->text = $this_primary_test_group['name'];
			        				$save_new_test_group->description = $this_primary_test_group['description'];
			        				$save_new_test_group->short_code = $this_primary_test_group['short_code'];
			        				$save_new_test_group->panel_test = $this_primary_test_group['panel_test'];
			        				$save_new_test_group->primary_test_group_id = null;
			        				$save_new_test_group->price = $this_primary_test_group['price'];
			        				$save_new_test_group->enabled = $this_primary_test_group['enabled'];
			        				$save_new_test_group->entry_datetime = date('Y-m-d H:i:s');
	    							$save_new_test_group->user_id = $user_id;
	    							$save_new_test_group->validated = 1;
	    							$save_new_test_group->validated_datetime = date('Y-m-d H:i:s');
	    							$save_new_test_group->validating_user_id = $user_id;
	    							$save_new_test_group->posted = 1;
	    							$save_new_test_group->posted_datetime = date('Y-m-d H:i:s');
	    							$save_new_test_group->save();
	    							$primary_test_group_id = $save_new_test_group->id;
	    						}
	    					}
	    				}

	    				$save_test_group = new LaboratoryTestGroup;
	    				$save_test_group->laboratory_id = $mro_settings['mrolaboratory_id'];
	    				$save_test_group->internal_id = $posted_test_group['id'];
	    				$save_test_group->name = $posted_test_group['name'];
	    				$save_test_group->text = $posted_test_group['name'];
	    				$save_test_group->description = $posted_test_group['description'];
	    				$save_test_group->short_code = $posted_test_group['short_code'];
	    				$save_test_group->panel_test = $posted_test_group['panel_test'];
	    				$save_test_group->primary_test_group_id = $primary_test_group_id;
	    				$save_test_group->price = $posted_test_group['price'];
	    				$save_test_group->enabled = $posted_test_group['enabled'];
	    				$save_test_group->entry_datetime = date('Y-m-d H:i:s');
						$save_test_group->user_id = $user_id;
						$save_test_group->validated = 1;
						$save_test_group->validated_datetime = date('Y-m-d H:i:s');
						$save_test_group->validating_user_id = $user_id;
						$save_test_group->posted = 1;
						$save_test_group->posted_datetime = date('Y-m-d H:i:s');

						if ($save_test_group->save()) {

							$posted_test_group['internal_id'] = $save_test_group->id;

							foreach ($posted_test_group['TestCode'] as &$posted_test_code) {

								$test_id = null;
	    						$existing_test_code = LaboratoryTest::where('internal_id','=',$posted_test_code['id'])->first();
	    						if ($existing_test_code) {
	    							$test_id = $existing_test_code->id;
	    						} else {

	    							$save_test_code = new LaboratoryTest;
	    							$save_test_code->laboratory_id = $mro_settings['mrolaboratory_id'];
	    							$save_test_code->internal_id = $posted_test_code['id'];
	    							$save_test_code->name = $posted_test_code['name'];
	    							$save_test_code->text = $posted_test_code['name'];
	    							$save_test_code->description = $posted_test_code['description'];
	    							$save_test_code->enabled = $posted_test_code['enabled'];
	    							$save_test_code->entry_datetime = date('Y-m-d H:i:s');
	    							$save_test_code->user_id = $user_id;
	    							$save_test_code->validated = 1;
	    							$save_test_code->validated_datetime = date('Y-m-d H:i:s');
	    							$save_test_code->validating_user_id = $user_id;
	    							$save_test_code->posted = 1;
	    							$save_test_code->posted_datetime = date('Y-m-d H:i:s');

	    							if ($save_test_code->save()) {
	    								$test_id = $save_test_code->id;
	    							}
	    						}

	    						if ($test_id) {

	    							$posted_test_code['internal_id'] = $test_id;
	    							
	    							$save_test_group_detail = new LaboratoryTestGroupDetail;
	    							$save_test_group_detail->test_group_id = $save_test_group->id;
	    							$save_test_group_detail->test_id = $test_id;
	    							$save_test_group_detail->enabled = 1;
	    							$save_test_group_detail->entry_datetime = date('Y-m-d H:i:s');
	    							$save_test_group_detail->user_id = $user_id;
	    							$save_test_group_detail->validated = 1;
	    							$save_test_group_detail->validated_datetime = date('Y-m-d H:i:s');
	    							$save_test_group_detail->validating_user_id = $user_id;
	    							$save_test_group_detail->posted = 1;
	    							$save_test_group_detail->posted_datetime = date('Y-m-d H:i:s');
	    							$save_test_group_detail->save();
	    						} else {
	    							throw new \Exception("Unable to save laboratory test", 1);
	    						}
	    					}
						}
	    			}
	    		}



				// start saving order to order tables
        		foreach ($post_orders as $order) {

        			$transaction_id = null;
        			if (isset($order['TestOrder']['PatientOrder']['PatientTransaction']) && isset($order['TestOrder']['PatientOrder']['PatientTransaction']['id']) && !empty($order['TestOrder']['PatientOrder']['PatientTransaction']['id'])) {

        				$existing_transaction = LaboratoryPatientTransaction::where([['patient_id','=',$patient_id],['external_id','=',$order['TestOrder']['PatientOrder']['PatientTransaction']['id']]])->first();
        				if ($existing_transaction) {
        					$transaction_id = $existing_transaction->id;
        				}
        			} 

        			if (!$transaction_id) {

        				$new_transaction = new LaboratoryPatientTransaction;
        				$new_transaction->patient_id = $patient_id;
        				$new_transaction->entry_datetime = date('Y-m-d H:i:s');
        				$new_transaction->user_id = $user_id;
        				$new_transaction->save();
        				$transaction_id = $new_transaction->id;
        			}


        			$specimen_id = $order['TestOrder']['specimen_id'];

        			//check existence
        			$existing_patient_order = LaboratoryPatientOrder::where([['specimen_id','=',$specimen_id],['patient_id','=',$patient_id]])->first();
        			if (!$existing_patient_order) {

        				$save_patient_order = new LaboratoryPatientOrder;
        				$save_patient_order->laboratory_id = $mro_settings['mrolaboratory_id'];
        				$save_patient_order->company_branch_id = $mro_settings['mrocompanybranch_id'];
        				$save_patient_order->internal_id = null;
        				$save_patient_order->patient_id = $patient_id;
        				$save_patient_order->total_amount_due = $order['TestOrder']['PatientOrder']['amount_due'];
        				$save_patient_order->entry_datetime = date('Y-m-d H:i:s');
        				$save_patient_order->user_id = $user_id;
        				$save_patient_order->status = 3;
        				$save_patient_order->posted = 1;
        				$save_patient_order->posted_datetime = date('Y-m-d H:i:s');
        				$save_patient_order->patient_transaction_id = $transaction_id;
        				$save_patient_order->specimen_id = $specimen_id;
        				$save_patient_order->external_specimen_id = $order['TestOrder']['specimen_id'];
        				$save_patient_order->entry_type = 1;
        				$save_patient_order->admission_date = ($order['TestOrder']['PatientOrder']['admission_date'])?date('Y-m-d', strtotime($order['TestOrder']['PatientOrder']['admission_date'])):null;
        				$save_patient_order->admission_time = ($order['TestOrder']['PatientOrder']['admission_time'])?date('H:i:s', strtotime($order['TestOrder']['PatientOrder']['admission_time'])):null;
        				$save_patient_order->location_id = $order['TestOrder']['PatientOrder']['location_id'];
        				$save_patient_order->date_requested = ($order['TestOrder']['PatientOrder']['date_requested'])?date('Y-m-d', strtotime($order['TestOrder']['PatientOrder']['date_requested'])):null;
        				$save_patient_order->time_requested = ($order['TestOrder']['PatientOrder']['time_requested'])?date('H:i:s', strtotime($order['TestOrder']['PatientOrder']['time_requested'])):null;
        				$save_patient_order->save();

        				$patient_order_id = $save_patient_order->id;


        				// savig of physician
        				if (isset($order['TestOrder']['PatientOrder']['PatientOrderPhysician']) && !empty($order['TestOrder']['PatientOrder']['PatientOrderPhysician'])) {
        				
        					foreach ($order['TestOrder']['PatientOrder']['PatientOrderPhysician'] as $patient_order_physician) {

        						$physician_id = null;
	        					$posted_physician = $patient_order_physician['Physician'];

	        					if (isset($posted_physician['id']) && !empty($posted_physician['id']) && ($posted_physician['id'] != 0 || $posted_physician['id'] != '0')) {

		        					$existing_physician = Physician::where('external_id','=',$posted_physician['id'])->first();
		        					if ($existing_physician) {

		        						$physician_id = $existing_physician->id;
		        					} else { // create new user for physician

										$physician_username = null;
										if (isset($posted_physician['birthdate']) && !empty($posted_physician['birthdate'])) {
											$physician_password = date("dmY", strtotime($posted_physician['birthdate']));
											Log::info($posted_physician['birthdate'].'physician bday');
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
										$save_physician_people->mobile = (isset($posted_physician['contact_numbers']))?$posted_physician['contact_numbers']:null;
										if ($save_physician_people->save()) {

											if (isset($posted_physician['license_number']) && !empty($posted_physician['license_number'])) {

												$professional_id_type = null;
												$existing_prc_id_type = IdentificationType::where('type','=',3)->first(); // professional id type
												if ($existing_prc_id_type) {
													$professional_id_type = $existing_prc_id_type->id;
												} else {

													$save_professional_id_type = new IdentificationType;
													$save_professional_id_type->name = 'Professional ID';
													$save_professional_id_type->description = 'Professional ID';
													$save_professional_id_type->type = 3;
													$save_professional_id_type->entry_datetime = date('Y-m-d H:i:s');
													$save_professional_id_type->user_id = $user_id;
													$save_professional_id_type->save();
													$professional_id_type = $save_professional_id_type->id;
												}


												$save_physician_people_professional_identification = new PersonIdentification;
												$save_physician_people_professional_identification->person_id = $save_physician_people->id;
												$save_physician_people_professional_identification->identification_id = $professional_id_type;
												$save_physician_people_professional_identification->reference_number = $posted_physician['license_number'];
												$save_physician_people_professional_identification->entry_datetime = date('Y-m-d H:i:s');
												$save_physician_people_professional_identification->user_id = $user_id;
												$save_physician_people_professional_identification->save();
											}



											$save_physician = new Physician;
											$save_physician->external_id = $posted_physician['id'];
											$save_physician->users_id = $save_physician_user->id;
											$save_physician->laboratory_id = $mro_settings['mrolaboratory_id'];
											$save_physician->entry_datetime = date('Y-m-d H:i:s');
											$save_physician->user_id = $user_id;
											$save_physician->save();

											$physician_id = $save_physician->id;
										}
		        					}



		        					if ($physician_id) {

		        						$save_patient_order_physician = new LaboratoryPatientOrderPhysician;
		        						$save_patient_order_physician->patient_order_id = $patient_order_id;
		        						$save_patient_order_physician->laboratory_id = $mro_settings['mrolaboratory_id'];
		        						$save_patient_order_physician->physician_id = $physician_id;
		        						$save_patient_order_physician->entry_datetime = date('Y-m-d H:i:s');
										$save_patient_order_physician->user_id = $user_id;
										$save_patient_order_physician->save();
		        					}
		        				}
        					}
        				}


        				$save_test_order = new LaboratoryTestOrder;
        				$save_test_order->patient_order_id = $patient_order_id;
        				$save_test_order->status = 3; //released
        				$save_test_order->release_date = ($order['TestOrder']['release_date'])?date('Y-m-d', strtotime($order['TestOrder']['release_date'])):null;
        				$save_test_order->release_time = ($order['TestOrder']['release_time'])?date('H:i:s', strtotime($order['TestOrder']['release_time'])):null;
        				$save_test_order->release_level_id = $order['TestOrder']['release_level_id'];
        				$save_test_order->entry_datetime = date('Y-m-d H:i:s');
        				$save_test_order->user_id = $user_id;
        				$save_test_order->posted = 1;
        				$save_test_order->posted_datetime = date('Y-m-d H:i:s');
        				$save_test_order->save();

        				$test_order_id = $save_test_order->id;


        				foreach ($order['TestOrder']['TestResult'] as $test_result) {

        					$save_test_result = new LaboratoryTestResult;
        					$save_test_result->test_order_id = $test_order_id;
        					$save_test_result->test_group_id = $posted_test_groups[$test_result['test_group_id']]['internal_id'];
        					$save_test_result->order_type = $test_result['order_type'];
        					$save_test_result->result_status = $test_result['result_status'];
        					$save_test_result->remarks = $test_result['remarks'];
        					$save_test_result->order_status = $test_result['order_status'];
        					$save_test_result->release_level_id = $test_result['release_level_id'];
        					$save_test_result->release_date = ($test_result['release_date'])?date('Y-m-d', strtotime($test_result['release_date'])):null;
        					$save_test_result->release_time = ($test_result['release_time'])?date('H:i:s', strtotime($test_result['release_time'])):null;
        					$save_test_result->cancel_date = ($test_result['cancel_date'])?date('Y-m-d', strtotime($test_result['cancel_date'])):null;
        					$save_test_result->cancel_time = ($test_result['cancel_time'])?date('H:i:s', strtotime($test_result['cancel_time'])):null;
        					$save_test_result->cancel_comments = $test_result['cancel_comments'];
        					$save_test_result->cancelling_user_id = $test_result['cancelling_user_id'];
        					$save_test_result->lab_notes = $test_result['lab_notes'];
        					$save_test_result->medtech_user_id = $test_result['medtech_user_id'];
        					$save_test_result->other_medtech_user_id = $test_result['other_medtech_user_id'];
        					$save_test_result->pathologist_user_id = $test_result['pathologist_user_id'];
        					$save_test_result->pdf_result = 1;
        					$save_test_result->pdf_filename = $test_result['pdf_filename'];
        					$save_test_result->entry_datetime = date('Y-m-d H:i:s');
        					$save_test_result->user_id = $user_id;
        					$save_test_result->save();

        					$test_result_id = $save_test_result->id;


        					if (isset($test_result['TestResultSpecimen']) && isset($test_result['TestResultSpecimen']['id']) && !empty($test_result['TestResultSpecimen']['id'])) {

        						$save_test_result_specimen = new LaboratoryTestResultSpecimen;
        						$save_test_result_specimen->test_result_id = $test_result_id;
        						$save_test_result_specimen->status = $test_result['TestResultSpecimen']['status'];
        						$save_test_result_specimen->extract_date = ($test_result['TestResultSpecimen']['extract_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['extract_date'])):null;
        						$save_test_result_specimen->extract_time = ($test_result['TestResultSpecimen']['extract_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['extract_time'])):null;
        						$save_test_result_specimen->extracting_user_id = $test_result['TestResultSpecimen']['extracting_user_id'];
        						$save_test_result_specimen->checkin_date = ($test_result['TestResultSpecimen']['checkin_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['checkin_date'])):null;
        						$save_test_result_specimen->checkin_time = ($test_result['TestResultSpecimen']['checkin_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['checkin_time'])):null;
        						$save_test_result_specimen->checkin_user_id = $test_result['TestResultSpecimen']['checkin_user_id'];
        						$save_test_result_specimen->accepted_date = ($test_result['TestResultSpecimen']['accepted_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['accepted_date'])):null;
        						$save_test_result_specimen->accepted_time = ($test_result['TestResultSpecimen']['accepted_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['accepted_time'])):null;
	        					$save_test_result_specimen->accepting_user_id = $test_result['TestResultSpecimen']['accepting_user_id'];
        						$save_test_result_specimen->reading_date = ($test_result['TestResultSpecimen']['reading_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['reading_date'])):null;
        						$save_test_result_specimen->reading_time = ($test_result['TestResultSpecimen']['reading_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['reading_time'])):null;
	        					$save_test_result_specimen->reading_user_id = $test_result['TestResultSpecimen']['reading_user_id'];
        						$save_test_result_specimen->remarks = $test_result['TestResultSpecimen']['remarks'];
        						$save_test_result_specimen->entry_datetime = date('Y-m-d H:i:s');
        						$save_test_result_specimen->user_id = $user_id;
        						$save_test_result_specimen->save();
        					}

        					foreach ($test_result['TestOrderDetail'] as $test_order_detail) {

        						$save_test_order_detail = new LaboratoryTestOrderDetail;
	        					$save_test_order_detail->test_result_id = $test_result_id;
	        					$save_test_order_detail->patient_order_id = $patient_order_id;
	        					$save_test_order_detail->order_status = $test_order_detail['order_status'];
	        					$save_test_order_detail->test_id = $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id'];
	        					$save_test_order_detail->panel_test_group_id = ($test_order_detail['panel_test_group_id'])?$posted_test_groups[$test_order_detail['panel_test_group_id']]['internal_id']:null;
	        					$save_test_order_detail->instrument_id = $test_order_detail['instrument_id'];
	        					$save_test_order_detail->start_test_datetime = $test_order_detail['start_test_datetime'];
	        					$save_test_order_detail->end_test_datetime = $test_order_detail['end_test_datetime'];
	        					$save_test_order_detail->result_type = $test_order_detail['result_type'];
	        					$save_test_order_detail->result_status = $test_order_detail['result_status'];
	        					$save_test_order_detail->result_count = $test_order_detail['result_count'];
	        					$save_test_order_detail->cancel_date = ($test_order_detail['cancel_date'])?date('Y-m-d', strtotime($test_order_detail['cancel_date'])):null;
        						$save_test_order_detail->cancel_time = ($test_order_detail['cancel_time'])?date('H:i:s', strtotime($test_order_detail['cancel_time'])):null;
	        					$save_test_order_detail->cancel_comments = $test_order_detail['cancel_comments'];
	        					$save_test_order_detail->cancelling_user_id = $test_order_detail['cancelling_user_id'];
	        					$save_test_order_detail->action_status = $test_order_detail['action_status'];
	        					$save_test_order_detail->action_datetime = ($test_order_detail['action_datetime'])?date('Y-m-d H:i:s', strtotime($test_order_detail['action_datetime'])):null;
	        					$save_test_order_detail->action_user_id = $test_order_detail['action_user_id'];
	        					$save_test_order_detail->repeated_test = $test_order_detail['repeated_test'];
	        					$save_test_order_detail->preceeding_test_id = (isset($test_order_detail['preceeding_test_id']) && !empty($test_order_detail['preceeding_test_id']) && $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']])?$posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']]['internal_id']:null;
	        					$save_test_order_detail->status = $test_order_detail['status'];
	        					$save_test_order_detail->printed = $test_order_detail['printed'];
	        					$save_test_order_detail->secondary_specimen_id = $test_order_detail['secondary_specimen_id'];
	        					$save_test_order_detail->patient_package_detail_id = null;
	        					$save_test_order_detail->save();

	        					$test_order_detail_id = $save_test_order_detail->id;


	        					$save_test_order_result = new LaboratoryTestOrderResult;
	        					$save_test_order_result->test_order_detail_id = $test_order_detail_id;
	        					$save_test_order_result->test_id = $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id'];
	        					$save_test_order_result->test_set_id = null;
	        					$save_test_order_result->value = $test_order_detail['TestOrderResult']['value'];
	        					$save_test_order_result->unit = $test_order_detail['TestOrderResult']['unit'];
	        					$save_test_order_result->si_value = $test_order_detail['TestOrderResult']['value'];
	        					$save_test_order_result->si_unit = $test_order_detail['TestOrderResult']['unit'];
	        					$save_test_order_result->si_reference_range = $test_order_detail['TestOrderResult']['reference_range'];
	        					$save_test_order_result->conventional_value = $test_order_detail['TestOrderResult']['conventional_value'];
	        					$save_test_order_result->conventional_unit = $test_order_detail['TestOrderResult']['conventional_unit'];
	        					$save_test_order_result->conventional_reference_range = $test_order_detail['TestOrderResult']['conventional_reference_range'];
	        					$save_test_order_result->result_flag = $test_order_detail['TestOrderResult']['result_flag'];
	        					$save_test_order_result->status = $test_order_detail['TestOrderResult']['status'];
	        					$save_test_order_result->remarks = $test_order_detail['TestOrderResult']['remarks'];
	        					$save_test_order_result->web_patient_viewable = null;
	        					$save_test_order_result->web_physician_viewable = null;
	        					$save_test_order_result->entry_datetime = date('Y-m-d H:i:s');
        						$save_test_order_result->user_id = $user_id;
        						$save_test_order_result->posted = 1;
        						$save_test_order_result->posted_datetime = date('Y-m-d H:i:s');
        						$save_test_order_result->save();
        					}
        				}
        			} else {
        				// TODO: append order

        				$existing_patient_order->total_amount_due = $order['TestOrder']['PatientOrder']['amount_due'];
        				if (isset($order['TestOrder']['PatientOrder']['admission_date']) && !empty($order['TestOrder']['PatientOrder']['admission_date'])) {
        					$existing_patient_order->admission_date = date('Y-m-d', strtotime($order['TestOrder']['PatientOrder']['admission_date']));
        				}
        				if (isset($order['TestOrder']['PatientOrder']['admission_time']) && !empty($order['TestOrder']['PatientOrder']['admission_time'])) {
        					$existing_patient_order->admission_time = date('Y-m-d', strtotime($order['TestOrder']['PatientOrder']['admission_time']));
        				}
        				$existing_patient_order->location_id = $order['TestOrder']['PatientOrder']['location_id'];
        				$existing_patient_order->save();
        				$patient_order_id = $existing_patient_order->id;

        				//updating of patient order physician
        				if (isset($order['TestOrder']['PatientOrder']['PatientOrderPhysician']) && !empty($order['TestOrder']['PatientOrder']['PatientOrderPhysician'])) {
        				
        					$existing_patient_order_physicians = LaboratoryPatientOrderPhysician::where('patient_order_id','=',$patient_order_id)->get();
        					$posted_physician_ids = array();

        					foreach ($order['TestOrder']['PatientOrder']['PatientOrderPhysician'] as $patient_order_physician) {

        						$physician_id = null;
	        					$posted_physician = $patient_order_physician['Physician'];

	        					if (isset($posted_physician['id']) && !empty($posted_physician['id']) && ($posted_physician['id'] != 0 || $posted_physician['id'] != '0')) {

		        					$existing_physician = Physician::where('external_id','=',$posted_physician['id'])->first();
		        					if ($existing_physician) {

		        						$physician_id = $existing_physician->id;
		        					} else { // create new user for physician

		        						$physician_username = null;
		        						if (isset($posted_physician['birthdate']) && !empty($posted_physician['birthdate'])) {
											$physician_password = date("dmY", strtotime($posted_physician['birthdate']));
											Log::info($posted_physician['birthdate'].'Physician bday');
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
										$save_physician_people->mobile = (isset($posted_physician['contact_numbers']))?$posted_physician['contact_numbers']:null;
										if ($save_physician_people->save()) {

											if (isset($posted_physician['license_number']) && !empty($posted_physician['license_number'])) {

												$professional_id_type = null;
												$existing_prc_id_type = IdentificationType::where('type','=',3)->first(); // professional id type
												if ($existing_prc_id_type) {
													$professional_id_type = $existing_prc_id_type->id;
												} else {

													$save_professional_id_type = new IdentificationType;
													$save_professional_id_type->name = 'Professional ID';
													$save_professional_id_type->description = 'Professional ID';
													$save_professional_id_type->type = 3;
													$save_professional_id_type->entry_datetime = date('Y-m-d H:i:s');
													$save_professional_id_type->user_id = $user_id;
													$save_professional_id_type->save();
													$professional_id_type = $save_professional_id_type->id;
												}


												$save_physician_people_professional_identification = new PersonIdentification;
												$save_physician_people_professional_identification->person_id = $save_physician_people->id;
												$save_physician_people_professional_identification->identification_id = $professional_id_type;
												$save_physician_people_professional_identification->reference_number = $posted_physician['license_number'];
												$save_physician_people_professional_identification->entry_datetime = date('Y-m-d H:i:s');
												$save_physician_people_professional_identification->user_id = $user_id;
												$save_physician_people_professional_identification->save();
											}



											$save_physician = new Physician;
											$save_physician->external_id = $posted_physician['id'];
											$save_physician->users_id = $save_physician_user->id;
											$save_physician->laboratory_id = $mro_settings['mrolaboratory_id'];
											$save_physician->entry_datetime = date('Y-m-d H:i:s');
											$save_physician->user_id = $user_id;
											$save_physician->save();

											$physician_id = $save_physician->id;
										}
		        					}


		        					if ($physician_id) {

		        						$patient_order_physician_exist = LaboratoryPatientOrderPhysician::where([['patient_order_id','=',$patient_order_id],['physician_id','=',$physician_id]])->first();
		        						
		        						if (!$patient_order_physician_exist) {
		        							$save_patient_order_physician = new LaboratoryPatientOrderPhysician;
			        						$save_patient_order_physician->patient_order_id = $patient_order_id;
			        						$save_patient_order_physician->laboratory_id = $mro_settings['mrolaboratory_id'];
			        						$save_patient_order_physician->physician_id = $physician_id;
			        						$save_patient_order_physician->entry_datetime = date('Y-m-d H:i:s');
											$save_patient_order_physician->user_id = $user_id;
											$save_patient_order_physician->save();
		        						}
		        						
		        						array_push($posted_physician_ids,$physician_id);
		        					}
		        				}
        					}

        					
    						if ($existing_patient_order_physicians) {

    							foreach ($existing_patient_order_physicians as $existing_patient_order_physician) {

    								if (!in_array($existing_patient_order_physician->physician_id, $posted_physician_ids)) {
    									$existing_patient_order_physician->delete();
    								}
    							}
    						}
        				}


        				// update test orders
        				$existing_test_orders = LaboratoryTestOrder::where('patient_order_id','=',$patient_order_id)->get();

        				foreach ($existing_test_orders as $existing_test_order) {

	        				$existing_test_order->release_date = ($order['TestOrder']['release_date'])?date('Y-m-d', strtotime($order['TestOrder']['release_date'])):null;
	        				$existing_test_order->release_time = ($order['TestOrder']['release_time'])?date('H:i:s', strtotime($order['TestOrder']['release_time'])):null;
	        				$existing_test_order->release_level_id = $order['TestOrder']['release_level_id'];
	        				$existing_test_order->save();

	        				$test_order_id = $existing_test_order->id;


	        				foreach ($order['TestOrder']['TestResult'] as $test_result) {


	        					$existing_test_result = LaboratoryTestResult::where([['test_order_id','=',$test_order_id],['test_group_id','=',$posted_test_groups[$test_result['test_group_id']]['internal_id']]])->first();

	        					if ($existing_test_result) {

	        						$existing_test_result->order_type = $test_result['order_type'];
		        					$existing_test_result->result_status = $test_result['result_status'];
		        					$existing_test_result->remarks = $test_result['remarks'];
		        					$existing_test_result->order_status = $test_result['order_status'];
		        					$existing_test_result->release_level_id = $test_result['release_level_id'];
		        					$existing_test_result->release_date = ($test_result['release_date'])?date('Y-m-d', strtotime($test_result['release_date'])):null;
		        					$existing_test_result->release_time = ($test_result['release_time'])?date('H:i:s', strtotime($test_result['release_time'])):null;
		        					$existing_test_result->cancel_date = ($test_result['cancel_date'])?date('Y-m-d', strtotime($test_result['cancel_date'])):null;
		        					$existing_test_result->cancel_time = ($test_result['cancel_time'])?date('H:i:s', strtotime($test_result['cancel_time'])):null;
		        					$existing_test_result->cancel_comments = $test_result['cancel_comments'];
		        					$existing_test_result->cancelling_user_id = $test_result['cancelling_user_id'];
		        					$existing_test_result->lab_notes = $test_result['lab_notes'];
		        					$existing_test_result->medtech_user_id = $test_result['medtech_user_id'];
		        					$existing_test_result->other_medtech_user_id = $test_result['other_medtech_user_id'];
		        					$existing_test_result->pathologist_user_id = $test_result['pathologist_user_id'];
		        					$existing_test_result->pdf_result = 1;
		        					$existing_test_result->pdf_filename = $test_result['pdf_filename'];
		        					$existing_test_result->save();

		        					$test_result_id = $existing_test_result->id;

		        					if (isset($test_result['TestResultSpecimen']) && isset($test_result['TestResultSpecimen']['id']) && !empty($test_result['TestResultSpecimen']['id'])) {

		        						$existing_test_result_specimen = LaboratoryTestResultSpecimen::where('test_result_id','=',$test_result_id)->first();
		        						if ($existing_test_result_specimen) {

		        							$existing_test_result_specimen->status = $test_result['TestResultSpecimen']['status'];
			        						$existing_test_result_specimen->extract_date = ($test_result['TestResultSpecimen']['extract_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['extract_date'])):null;
			        						$existing_test_result_specimen->extract_time = ($test_result['TestResultSpecimen']['extract_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['extract_time'])):null;
			        						$existing_test_result_specimen->extracting_user_id = $test_result['TestResultSpecimen']['extracting_user_id'];
			        						$existing_test_result_specimen->checkin_date = ($test_result['TestResultSpecimen']['checkin_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['checkin_date'])):null;
			        						$existing_test_result_specimen->checkin_time = ($test_result['TestResultSpecimen']['checkin_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['checkin_time'])):null;
			        						$existing_test_result_specimen->checkin_user_id = $test_result['TestResultSpecimen']['checkin_user_id'];
			        						$existing_test_result_specimen->accepted_date = ($test_result['TestResultSpecimen']['accepted_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['accepted_date'])):null;
			        						$existing_test_result_specimen->accepted_time = ($test_result['TestResultSpecimen']['accepted_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['accepted_time'])):null;
				        					$existing_test_result_specimen->accepting_user_id = $test_result['TestResultSpecimen']['accepting_user_id'];
			        						$existing_test_result_specimen->reading_date = ($test_result['TestResultSpecimen']['reading_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['reading_date'])):null;
			        						$existing_test_result_specimen->reading_time = ($test_result['TestResultSpecimen']['reading_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['reading_time'])):null;
				        					$existing_test_result_specimen->reading_user_id = $test_result['TestResultSpecimen']['reading_user_id'];
			        						$existing_test_result_specimen->remarks = $test_result['TestResultSpecimen']['remarks'];
			        						$existing_test_result_specimen->save();
		        						} else {

		        							$save_test_result_specimen = new LaboratoryTestResultSpecimen;
			        						$save_test_result_specimen->test_result_id = $test_result_id;
			        						$save_test_result_specimen->status = $test_result['TestResultSpecimen']['status'];
			        						$save_test_result_specimen->extract_date = ($test_result['TestResultSpecimen']['extract_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['extract_date'])):null;
			        						$save_test_result_specimen->extract_time = ($test_result['TestResultSpecimen']['extract_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['extract_time'])):null;
			        						$save_test_result_specimen->extracting_user_id = $test_result['TestResultSpecimen']['extracting_user_id'];
			        						$save_test_result_specimen->checkin_date = ($test_result['TestResultSpecimen']['checkin_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['checkin_date'])):null;
			        						$save_test_result_specimen->checkin_time = ($test_result['TestResultSpecimen']['checkin_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['checkin_time'])):null;
			        						$save_test_result_specimen->checkin_user_id = $test_result['TestResultSpecimen']['checkin_user_id'];
			        						$save_test_result_specimen->accepted_date = ($test_result['TestResultSpecimen']['accepted_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['accepted_date'])):null;
			        						$save_test_result_specimen->accepted_time = ($test_result['TestResultSpecimen']['accepted_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['accepted_time'])):null;
				        					$save_test_result_specimen->accepting_user_id = $test_result['TestResultSpecimen']['accepting_user_id'];
			        						$save_test_result_specimen->reading_date = ($test_result['TestResultSpecimen']['reading_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['reading_date'])):null;
			        						$save_test_result_specimen->reading_time = ($test_result['TestResultSpecimen']['reading_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['reading_time'])):null;
				        					$save_test_result_specimen->reading_user_id = $test_result['TestResultSpecimen']['reading_user_id'];
			        						$save_test_result_specimen->remarks = $test_result['TestResultSpecimen']['remarks'];
			        						$save_test_result_specimen->entry_datetime = date('Y-m-d H:i:s');
			        						$save_test_result_specimen->user_id = $user_id;
			        						$save_test_result_specimen->save();
		        						}
		        					}


		        					foreach ($test_result['TestOrderDetail'] as $test_order_detail) {


		        						$existing_test_order_detail = LaboratoryTestOrderDetail::where([['test_result_id','=',$test_result_id],['test_id','=',$posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id']]])->first();
		        						if ($existing_test_order_detail) {

				        					$existing_test_order_detail->order_status = $test_order_detail['order_status'];
				        					$existing_test_order_detail->instrument_id = $test_order_detail['instrument_id'];
				        					$existing_test_order_detail->start_test_datetime = $test_order_detail['start_test_datetime'];
				        					$existing_test_order_detail->end_test_datetime = $test_order_detail['end_test_datetime'];
				        					$existing_test_order_detail->result_type = $test_order_detail['result_type'];
				        					$existing_test_order_detail->result_status = $test_order_detail['result_status'];
				        					$existing_test_order_detail->result_count = $test_order_detail['result_count'];
				        					$existing_test_order_detail->cancel_date = ($test_order_detail['cancel_date'])?date('Y-m-d', strtotime($test_order_detail['cancel_date'])):null;
			        						$existing_test_order_detail->cancel_time = ($test_order_detail['cancel_time'])?date('H:i:s', strtotime($test_order_detail['cancel_time'])):null;
				        					$existing_test_order_detail->cancel_comments = $test_order_detail['cancel_comments'];
				        					$existing_test_order_detail->cancelling_user_id = $test_order_detail['cancelling_user_id'];
				        					$existing_test_order_detail->action_status = $test_order_detail['action_status'];
				        					$existing_test_order_detail->action_datetime = ($test_order_detail['action_datetime'])?date('Y-m-d H:i:s', strtotime($test_order_detail['action_datetime'])):null;
				        					$existing_test_order_detail->action_user_id = $test_order_detail['action_user_id'];
				        					$existing_test_order_detail->repeated_test = $test_order_detail['repeated_test'];
				        					$existing_test_order_detail->preceeding_test_id = (isset($test_order_detail['preceeding_test_id']) && !empty($test_order_detail['preceeding_test_id']) && $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']])?$posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']]['internal_id']:null;
				        					$existing_test_order_detail->status = $test_order_detail['status'];
				        					$existing_test_order_detail->printed = $test_order_detail['printed'];
				        					$existing_test_order_detail->secondary_specimen_id = $test_order_detail['secondary_specimen_id'];
				        					$existing_test_order_detail->patient_package_detail_id = null;
				        					$existing_test_order_detail->save();

				        					$test_order_detail_id = $existing_test_order_detail->id;

				        					$existing_test_order_result = LaboratoryTestOrderResult::where('test_order_detail_id','=',$test_order_detail_id)->first();
				        					$existing_test_order_result->value = $test_order_detail['TestOrderResult']['value'];
				        					$existing_test_order_result->unit = $test_order_detail['TestOrderResult']['unit'];
				        					$existing_test_order_result->si_value = $test_order_detail['TestOrderResult']['value'];
				        					$existing_test_order_result->si_unit = $test_order_detail['TestOrderResult']['unit'];
				        					$existing_test_order_result->si_reference_range = $test_order_detail['TestOrderResult']['reference_range'];
				        					$existing_test_order_result->conventional_value = $test_order_detail['TestOrderResult']['conventional_value'];
				        					$existing_test_order_result->conventional_unit = $test_order_detail['TestOrderResult']['conventional_unit'];
				        					$existing_test_order_result->conventional_reference_range = $test_order_detail['TestOrderResult']['conventional_reference_range'];
				        					$existing_test_order_result->result_flag = $test_order_detail['TestOrderResult']['result_flag'];
				        					$existing_test_order_result->status = $test_order_detail['TestOrderResult']['status'];
				        					$existing_test_order_result->remarks = $test_order_detail['TestOrderResult']['remarks'];
			        						$existing_test_order_result->save();
		        						} else {

		        							$save_test_order_detail = new LaboratoryTestOrderDetail;
				        					$save_test_order_detail->test_result_id = $test_result_id;
				        					$save_test_order_detail->patient_order_id = $patient_order_id;
				        					$save_test_order_detail->order_status = $test_order_detail['order_status'];
				        					$save_test_order_detail->test_id = $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id'];
				        					$save_test_order_detail->panel_test_group_id = ($test_order_detail['panel_test_group_id'])?$posted_test_groups[$test_order_detail['panel_test_group_id']]['internal_id']:null;
				        					$save_test_order_detail->instrument_id = $test_order_detail['instrument_id'];
				        					$save_test_order_detail->start_test_datetime = $test_order_detail['start_test_datetime'];
				        					$save_test_order_detail->end_test_datetime = $test_order_detail['end_test_datetime'];
				        					$save_test_order_detail->result_type = $test_order_detail['result_type'];
				        					$save_test_order_detail->result_status = $test_order_detail['result_status'];
				        					$save_test_order_detail->result_count = $test_order_detail['result_count'];
				        					$save_test_order_detail->cancel_date = ($test_order_detail['cancel_date'])?date('Y-m-d', strtotime($test_order_detail['cancel_date'])):null;
			        						$save_test_order_detail->cancel_time = ($test_order_detail['cancel_time'])?date('H:i:s', strtotime($test_order_detail['cancel_time'])):null;
				        					$save_test_order_detail->cancel_comments = $test_order_detail['cancel_comments'];
				        					$save_test_order_detail->cancelling_user_id = $test_order_detail['cancelling_user_id'];
				        					$save_test_order_detail->action_status = $test_order_detail['action_status'];
				        					$save_test_order_detail->action_datetime = ($test_order_detail['action_datetime'])?date('Y-m-d H:i:s', strtotime($test_order_detail['action_datetime'])):null;
				        					$save_test_order_detail->action_user_id = $test_order_detail['action_user_id'];
				        					$save_test_order_detail->repeated_test = $test_order_detail['repeated_test'];
				        					$save_test_order_detail->preceeding_test_id = ($posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']])?$posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']]['internal_id']:null;
				        					$save_test_order_detail->status = $test_order_detail['status'];
				        					$save_test_order_detail->printed = $test_order_detail['printed'];
				        					$save_test_order_detail->secondary_specimen_id = $test_order_detail['secondary_specimen_id'];
				        					$save_test_order_detail->patient_package_detail_id = null;
				        					$save_test_order_detail->save();

				        					$test_order_detail_id = $save_test_order_detail->id;


				        					$save_test_order_result = new LaboratoryTestOrderResult;
				        					$save_test_order_result->test_order_detail_id = $test_order_detail_id;
				        					$save_test_order_result->test_id = $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id'];
				        					$save_test_order_result->test_set_id = null;
				        					$save_test_order_result->value = $test_order_detail['TestOrderResult']['value'];
				        					$save_test_order_result->unit = $test_order_detail['TestOrderResult']['unit'];
				        					$save_test_order_result->si_value = $test_order_detail['TestOrderResult']['value'];
				        					$save_test_order_result->si_unit = $test_order_detail['TestOrderResult']['unit'];
				        					$save_test_order_result->si_reference_range = $test_order_detail['TestOrderResult']['reference_range'];
				        					$save_test_order_result->conventional_value = $test_order_detail['TestOrderResult']['conventional_value'];
				        					$save_test_order_result->conventional_unit = $test_order_detail['TestOrderResult']['conventional_unit'];
				        					$save_test_order_result->conventional_reference_range = $test_order_detail['TestOrderResult']['conventional_reference_range'];
				        					$save_test_order_result->result_flag = $test_order_detail['TestOrderResult']['result_flag'];
				        					$save_test_order_result->status = $test_order_detail['TestOrderResult']['status'];
				        					$save_test_order_result->remarks = $test_order_detail['TestOrderResult']['remarks'];
				        					$save_test_order_result->web_patient_viewable = null;
				        					$save_test_order_result->web_physician_viewable = null;
				        					$save_test_order_result->entry_datetime = date('Y-m-d H:i:s');
			        						$save_test_order_result->user_id = $user_id;
			        						$save_test_order_result->posted = 1;
			        						$save_test_order_result->posted_datetime = date('Y-m-d H:i:s');
			        						$save_test_order_result->save();
		        						}
		        					}

	        					} else {

	        						$save_test_result = new LaboratoryTestResult;
		        					$save_test_result->test_order_id = $test_order_id;
		        					$save_test_result->test_group_id = $posted_test_groups[$test_result['test_group_id']]['internal_id'];
		        					$save_test_result->order_type = $test_result['order_type'];
		        					$save_test_result->result_status = $test_result['result_status'];
		        					$save_test_result->remarks = $test_result['remarks'];
		        					$save_test_result->order_status = $test_result['order_status'];
		        					$save_test_result->release_level_id = $test_result['release_level_id'];
		        					$save_test_result->release_date = ($test_result['release_date'])?date('Y-m-d', strtotime($test_result['release_date'])):null;
		        					$save_test_result->release_time = ($test_result['release_time'])?date('H:i:s', strtotime($test_result['release_time'])):null;
		        					$save_test_result->cancel_date = ($test_result['cancel_date'])?date('Y-m-d', strtotime($test_result['cancel_date'])):null;
		        					$save_test_result->cancel_time = ($test_result['cancel_time'])?date('H:i:s', strtotime($test_result['cancel_time'])):null;
		        					$save_test_result->cancel_comments = $test_result['cancel_comments'];
		        					$save_test_result->cancelling_user_id = $test_result['cancelling_user_id'];
		        					$save_test_result->lab_notes = $test_result['lab_notes'];
		        					$save_test_result->medtech_user_id = $test_result['medtech_user_id'];
		        					$save_test_result->other_medtech_user_id = $test_result['other_medtech_user_id'];
		        					$save_test_result->pathologist_user_id = $test_result['pathologist_user_id'];
		        					$save_test_result->pdf_result = 1;
		        					$save_test_result->pdf_filename = $test_result['pdf_filename'];
		        					$save_test_result->entry_datetime = date('Y-m-d H:i:s');
		        					$save_test_result->user_id = $user_id;
		        					$save_test_result->save();

		        					$test_result_id = $save_test_result->id;


		        					if (isset($test_result['TestResultSpecimen']) && isset($test_result['TestResultSpecimen']['id']) && !empty($test_result['TestResultSpecimen']['id'])) {

		        						$save_test_result_specimen = new LaboratoryTestResultSpecimen;
		        						$save_test_result_specimen->test_result_id = $test_result_id;
		        						$save_test_result_specimen->status = $test_result['TestResultSpecimen']['status'];
		        						$save_test_result_specimen->extract_date = ($test_result['TestResultSpecimen']['extract_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['extract_date'])):null;
		        						$save_test_result_specimen->extract_time = ($test_result['TestResultSpecimen']['extract_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['extract_time'])):null;
		        						$save_test_result_specimen->extracting_user_id = $test_result['TestResultSpecimen']['extracting_user_id'];
		        						$save_test_result_specimen->checkin_date = ($test_result['TestResultSpecimen']['checkin_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['checkin_date'])):null;
		        						$save_test_result_specimen->checkin_time = ($test_result['TestResultSpecimen']['checkin_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['checkin_time'])):null;
		        						$save_test_result_specimen->checkin_user_id = $test_result['TestResultSpecimen']['checkin_user_id'];
		        						$save_test_result_specimen->accepted_date = ($test_result['TestResultSpecimen']['accepted_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['accepted_date'])):null;
		        						$save_test_result_specimen->accepted_time = ($test_result['TestResultSpecimen']['accepted_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['accepted_time'])):null;
			        					$save_test_result_specimen->accepting_user_id = $test_result['TestResultSpecimen']['accepting_user_id'];
		        						$save_test_result_specimen->reading_date = ($test_result['TestResultSpecimen']['reading_date'])?date('Y-m-d', strtotime($test_result['TestResultSpecimen']['reading_date'])):null;
		        						$save_test_result_specimen->reading_time = ($test_result['TestResultSpecimen']['reading_time'])?date('H:i:s', strtotime($test_result['TestResultSpecimen']['reading_time'])):null;
			        					$save_test_result_specimen->reading_user_id = $test_result['TestResultSpecimen']['reading_user_id'];
		        						$save_test_result_specimen->remarks = $test_result['TestResultSpecimen']['remarks'];
		        						$save_test_result_specimen->entry_datetime = date('Y-m-d H:i:s');
		        						$save_test_result_specimen->user_id = $user_id;
		        						$save_test_result_specimen->save();
		        					}


		        					foreach ($test_result['TestOrderDetail'] as $test_order_detail) {

		        						$save_test_order_detail = new LaboratoryTestOrderDetail;
			        					$save_test_order_detail->test_result_id = $test_result_id;
			        					$save_test_order_detail->patient_order_id = $patient_order_id;
			        					$save_test_order_detail->order_status = $test_order_detail['order_status'];
			        					$save_test_order_detail->test_id = $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id'];
			        					$save_test_order_detail->panel_test_group_id = $posted_test_groups[$test_order_detail['panel_test_group_id']]['internal_id'];
			        					$save_test_order_detail->instrument_id = $test_order_detail['instrument_id'];
			        					$save_test_order_detail->start_test_datetime = $test_order_detail['start_test_datetime'];
			        					$save_test_order_detail->end_test_datetime = $test_order_detail['end_test_datetime'];
			        					$save_test_order_detail->result_type = $test_order_detail['result_type'];
			        					$save_test_order_detail->result_status = $test_order_detail['result_status'];
			        					$save_test_order_detail->result_count = $test_order_detail['result_count'];
			        					$save_test_order_detail->cancel_date = ($test_order_detail['cancel_date'])?date('Y-m-d', strtotime($test_order_detail['cancel_date'])):null;
		        						$save_test_order_detail->cancel_time = ($test_order_detail['cancel_time'])?date('H:i:s', strtotime($test_order_detail['cancel_time'])):null;
			        					$save_test_order_detail->cancel_comments = $test_order_detail['cancel_comments'];
			        					$save_test_order_detail->cancelling_user_id = $test_order_detail['cancelling_user_id'];
			        					$save_test_order_detail->action_status = $test_order_detail['action_status'];
			        					$save_test_order_detail->action_datetime = ($test_order_detail['action_datetime'])?date('Y-m-d H:i:s', strtotime($test_order_detail['action_datetime'])):null;
			        					$save_test_order_detail->action_user_id = $test_order_detail['action_user_id'];
			        					$save_test_order_detail->repeated_test = $test_order_detail['repeated_test'];
			        					$save_test_order_detail->preceeding_test_id = ($posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']])?$posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['preceeding_test_id']]['internal_id']:null;
			        					$save_test_order_detail->status = $test_order_detail['status'];
			        					$save_test_order_detail->printed = $test_order_detail['printed'];
			        					$save_test_order_detail->secondary_specimen_id = $test_order_detail['secondary_specimen_id'];
			        					$save_test_order_detail->patient_package_detail_id = null;
			        					$save_test_order_detail->save();

			        					$test_order_detail_id = $save_test_order_detail->id;


			        					$save_test_order_result = new LaboratoryTestOrderResult;
			        					$save_test_order_result->test_order_detail_id = $test_order_detail_id;
			        					$save_test_order_result->test_id = $posted_test_groups[$test_result['test_group_id']]['TestCode'][$test_order_detail['test_id']]['internal_id'];
			        					$save_test_order_result->test_set_id = null;
			        					$save_test_order_result->value = $test_order_detail['TestOrderResult']['value'];
			        					$save_test_order_result->unit = $test_order_detail['TestOrderResult']['unit'];
			        					$save_test_order_result->si_value = $test_order_detail['TestOrderResult']['value'];
			        					$save_test_order_result->si_unit = $test_order_detail['TestOrderResult']['unit'];
			        					$save_test_order_result->si_reference_range = $test_order_detail['TestOrderResult']['reference_range'];
			        					$save_test_order_result->conventional_value = $test_order_detail['TestOrderResult']['conventional_value'];
			        					$save_test_order_result->conventional_unit = $test_order_detail['TestOrderResult']['conventional_unit'];
			        					$save_test_order_result->conventional_reference_range = $test_order_detail['TestOrderResult']['conventional_reference_range'];
			        					$save_test_order_result->result_flag = $test_order_detail['TestOrderResult']['result_flag'];
			        					$save_test_order_result->status = $test_order_detail['TestOrderResult']['status'];
			        					$save_test_order_result->remarks = $test_order_detail['TestOrderResult']['remarks'];
			        					$save_test_order_result->web_patient_viewable = null;
			        					$save_test_order_result->web_physician_viewable = null;
			        					$save_test_order_result->entry_datetime = date('Y-m-d H:i:s');
		        						$save_test_order_result->user_id = $user_id;
		        						$save_test_order_result->posted = 1;
		        						$save_test_order_result->posted_datetime = date('Y-m-d H:i:s');
		        						$save_test_order_result->save();
		        					}
	        					}
	        				}
        				}
        			}
        		}

				// throw new \Exception("Error Processing Request", 1);
				
				DB::commit();

				// if ($new_user_id) { User::findOrFail($new_user_id)->delete(); }
				// if ($new_physician_user_id) { User::findOrFail($new_physician_user_id)->delete(); }
				// DB::rollBack();
			} catch (\Exception $e) {

				// deleting created user for patient and physician because users table is MyISAM
				if ($new_user_id) { User::findOrFail($new_user_id)->delete(); }
				if ($new_physician_user_id) { User::findOrFail($new_physician_user_id)->delete(); }
				
				DB::rollBack();

				$returndata['success'] = false;
				$returndata['message'] = 'MRO WebLIS post failed! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
			}
		} else {
			$returndata['success'] = false;
			$returndata['message'] = 'MRO WebLIS post failed! Stacktrace: Invalid token scope!';
		}

		return Response::json($returndata);
	}
}
