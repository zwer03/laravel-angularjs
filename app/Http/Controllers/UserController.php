<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Helpers\Interfaces\UserInterface;

use App\User;
use App\People;
use DB;
class UserController extends Controller
{

	public function audit(UserInterface $iUser, Request $request) {
		$audit_data = array(
			"user_id" => ($request->user_id)?$request->user_id:null,
			"url" => $request->url,
			"action" => $request->action,
			"remarks" => $request->remarks,
			"device" => $request->device,
			"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			"device_os" => $request->device_os,
			"browser" => $request->browser,
			"browser_version" => $request->browser_version
		);

		return $iUser->audit($audit_data);
	}


	public function passwordReset(UserInterface $iUser, Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);

		try {
			
			
			$user = User::where('username','=',$request->username)->first();

			if ($user) {

				if (!Hash::check($request->old_password, $user->password)) {
					throw new \Exception("Invalid password", 1);
				}
			} else {
				throw new \Exception("Invalid username", 1);
			}


			$password = Hash::make($request->password);
			$user->password = $password;
			$user->save();


			// $audit_data = array(
			// 	"user_id" => $request->user()->id,
			// 	"url" => "user/password_reset",
			// 	"action" => "user.password_reset",
			// 	"remarks" => "Password reset successful",
			// 	"device" => null,
			// 	"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			// 	"device_os" => null,
			// 	"browser" => null,
			// 	"browser_version" => null
			// );
			// $iUser->audit($audit_data);
		} catch (\Exception $e) {

			// $audit_data = array(
			// 	"user_id" => $request->user()->id,
			// 	"url" => "user/password_reset",
			// 	"action" => "user.password_reset",
			// 	"remarks" => "Password reset failed",
			// 	"device" => null,
			// 	"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			// 	"device_os" => null,
			// 	"browser" => null,
			// 	"browser_version" => null
			// );
			// $iUser->audit($audit_data);

			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		

		return $returndata;
	}
    
    public function logout(UserInterface $iUser, Request $request) {

    	$returndata = array('success'=>true, 'message'=>null);

    	if (!$request->user()->token()->revoke()) {

    		$returndata['success'] = false;
    		$returndata['message'] = 'Error logging out user!';


   //  		$audit_data = array(
			// 	"user_id" => $request->user()->id,
			// 	"url" => "user/logout",
			// 	"action" => "user.logout",
			// 	"remarks" => "Logout failed",
			// 	"device" => null,
			// 	"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			// 	"device_os" => null,
			// 	"browser" => null,
			// 	"browser_version" => null
			// );
			// $iUser->audit($audit_data);
    	} else {

   //  		$audit_data = array(
			// 	"user_id" => $request->user()->id,
			// 	"url" => "user/logout",
			// 	"action" => "user.logout",
			// 	"remarks" => "Logout successful",
			// 	"device" => null,
			// 	"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			// 	"device_os" => null,
			// 	"browser" => null,
			// 	"browser_version" => null
			// );
			// $iUser->audit($audit_data);
    	}

    	return $returndata;
	}
	
	// CRUD

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			$username = null;
			$name = null;
			$role = null;

			$filter_string = "";

			if ($request->filled('username')) {
				$username = $request->input('username');
				$filter_string .= "username=".$username.", ";
			}
			if ($request->filled('name')) {
				$name = $request->input('name');
				$filter_string .= "name*=".$name.", ";
			}
			if ($request->filled('role')) {
				$role = $request->input('role');
				$filter_string .= "role=".$role.", ";
			}
			$returndata['data'] = User::where(function($whereClause) use ($username,$name,$role) {
				$whereClause->where('users.name','<>','Administrator');
				if ($username) {
					$whereClause->where('users.username','=',$username);
				}
				if ($name) {
					$whereClause->where('users.name','like',"%".$name."%");
				}
				if ($role) {
					$whereClause->where('users.role','=',$role);
				}
			})->paginate(15);
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'User get_users error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		DB::beginTransaction();
		try {
			
			
			$user = User::where('username','=',$request['User']['username'])->first();
			if (!$user)
				throw new \Exception("Invalid username", 1);
			
			if (isset($request['User']['password']) && !empty($request['User']['password'])) {
				$password = Hash::make($request['User']['password']);
				$user->password = $password;
			}
			$user->name = $request['Person']['firstname'].' '.$request['Person']['middlename'].' '.$request['Person']['lastname'];
			$user->save();

			$save_people = People::findOrFail($request['Person']['id']);
			// $save_people->myresultonline_id = $mro_id;
			$save_people->title_id = null;
			$save_people->firstname = $request['Person']['firstname'];
			$save_people->middlename = $request['Person']['middlename'];
			$save_people->lastname = $request['Person']['lastname'];
			// $save_people->birthdate = date('Y-m-d', strtotime($post_patient['birthdate']));
			// $save_people->sex = $post_patient['sex'];
			// $save_people->marital_status = $post_patient['marital_status'];
			$save_people->mobile = $request['Person']['mobile'];
			$save_people->save();
			$returndata['message'] = 'User information has been updated';

			// $audit_data = array(
			// 	"user_id" => $request->user()->id,
			// 	"url" => "user/password_reset",
			// 	"action" => "user.password_reset",
			// 	"remarks" => "Password reset successful",
			// 	"device" => null,
			// 	"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			// 	"device_os" => null,
			// 	"browser" => null,
			// 	"browser_version" => null
			// );
			// $iUser->audit($audit_data);
			DB::commit();
		} catch (\Exception $e) {

			// $audit_data = array(
			// 	"user_id" => $request->user()->id,
			// 	"url" => "user/password_reset",
			// 	"action" => "user.password_reset",
			// 	"remarks" => "Password reset failed",
			// 	"device" => null,
			// 	"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
			// 	"device_os" => null,
			// 	"browser" => null,
			// 	"browser_version" => null
			// );
			// $iUser->audit($audit_data);
			DB::rollBack();
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		

		return $returndata;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
				'practitioners.external_id as practitioner_external_id'
			)
			->leftJoin('people','people.myresultonline_id','=','users.username')
			->leftJoin('practitioners','practitioners.person_id','=','people.id')
			->where('users.username','=',$username)
			->first();

		if($user)
			$returndata['data'] = $user;
		else
			$returndata = array('success'=>false,'message'=>'No record found.','data'=>null);
		

		return $returndata;
	}
}