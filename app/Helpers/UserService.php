<?php

namespace App\Helpers;

use App\Helpers\Interfaces\UserInterface;

use Illuminate\Support\Facades\Hash;

use App\User;
use App\AuditLog;

class UserService implements UserInterface
{

    public function audit($request) {

        $returndata = array('success'=>true,'message'=>null);

        try {
            
            $save_audit = new AuditLog;
            $save_audit->datetime = date('Y-m-d H:i:s');
            $save_audit->user_id = $request['user_id'];
            $save_audit->module = null;
            $save_audit->url = $request['url'];
            $save_audit->action = $request['action'];
            $save_audit->ip_address = $request['ip_address'];
            $save_audit->remarks = $request['remarks'];
            $save_audit->device = $request['device'];
            $save_audit->device_os = $request['device_os'];
            $save_audit->browser = $request['browser'];
            $save_audit->browser_version = $request['browser_version'];
            $save_audit->save();
        } catch (\Exception $e) {
            $returndata['success'] = false;
            $returndata['message'] = $e->getMessage();
        }

        return $returndata;
    }

    public function create($request) {

    	$returndata = array('success'=>true,'message'=>null);

    	try {
    		
    		$password = Hash::make($request['last_name']);

	        User::create([
	            'name' => $request['first_name'].' '.$request['middle_name'].' '.$request['last_name'],
	            'email' => $request['email'],
	            'username' => $request['external_id'],
	            'password' => $password
	        ]);
    	} catch (Exception $e) {
    		$returndata['success'] = false;
			$returndata['message'] = 'Unable to save new user! Stacktrace: '.$e->getMessage();
    	}

        return $returndata;
    }

}


?>