<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Helpers\Interfaces\UserInterface;

use App\User;
use App\People;
use DB;
use App\Traits\Authorizable;
//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class UserController extends Controller
{
	use Authorizable;
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


	public function password_change(UserInterface $iUser, Request $request) {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);

		try {
			$user = User::where('username','=',$request->username)->first();

			if ($user) {
				if (!Hash::check($request->old_password, $user->password)) {
					throw new \Exception("Invalid password", 1);
				}elseif($request->old_password == $request->password)
					throw new \Exception("Password cannot be the same as old", 1);
			} else {
				throw new \Exception("Invalid username", 1);
			}
			$user->last_change_password = date('Y-m-d H:i:s');
			// $password = Hash::make($request->password);
			$user->password = $request->password;
			$user->default_pf_type = $request->default_pf_type;
			$user->save();
			$audit_data = array(
				"user_id" => $request->user()->id,
				"url" => "user/password_reset",
				"action" => "user.password_reset",
				"remarks" => "Password reset successful",
				"device" => null,
				"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
				"device_os" => null,
				"browser" => null,
				"browser_version" => null
			);
			$iUser->audit($audit_data);
		} catch (\Exception $e) {
			$audit_data = array(
				"user_id" => $request->user()->id,
				"url" => "user/password_reset",
				"action" => "user.password_reset",
				"remarks" => "Password reset failed",
				"device" => null,
				"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
				"device_os" => null,
				"browser" => null,
				"browser_version" => null
			);
			$iUser->audit($audit_data);
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		

		return $returndata;
	}

	public function default_pf_type(UserInterface $iUser, Request $request) {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);

		try {
			Log::info($request);
			$user = User::where('id','=',$request->user()->id)->first();
			$user->default_pf_type = $request->default_pf_type;
			$user->save();
			$audit_data = array(
				"user_id" => $request->user()->id,
				"url" => "user/default_pf_type",
				"action" => "user.default_pf_type",
				"remarks" => "Default PF type has been set to ".$request->default_pf_type,
				"device" => null,
				"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
				"device_os" => null,
				"browser" => null,
				"browser_version" => null
			);
			$iUser->audit($audit_data);
			$returndata['message'] = "Default PF type updated";
		} catch (\Exception $e) {
			$audit_data = array(
				"user_id" => $request->user()->id,
				"url" => "user/default_pf_type",
				"action" => "user.default_pf_type",
				"remarks" => "Default PF type update failed",
				"device" => null,
				"ip_address" => ($request->ip_address)?$request->ip_address:$request->ip(),
				"device_os" => null,
				"browser" => null,
				"browser_version" => null
			);
			$iUser->audit($audit_data);
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		
		return redirect('/physician/dashboard')->with('status', $returndata['message']);
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
    public function index()
    {
        $users = User::orderby('created_at', 'asc')->paginate(20); 
        return view('users.index')->with('users', $users);
    }

	public function search(Request $request)
    {
		if($request->search_keyword)
			$users = User::where('username','=',$request['search_keyword'])->orWhere('name','like','%'.$request['search_keyword'].'%')->orderby('created_at', 'asc')->paginate(20);
		else
			$users = User::orderby('created_at', 'asc')->paginate(20); 
        return view('users.index')->with('users', $users);
	}
	
    public function create()
    {
        $roles = Role::get();
        return view('users.create', ['roles'=>$roles]);
    }

    public function store(Request $request)
    {
        // $this->validate($request, [
        //     'name' => 'bail|required|min:2',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required|min:6',
        //     'roles' => 'required|min:1'
        // ]);

        // // hash password
        // $request->merge(['password' => bcrypt($request->get('password'))]);

        // // Create the user
        // if ( $user = User::create($request->except('roles', 'permissions')) ) {
        //     $this->syncPermissions($request, $user);
        //     flash('User has been created.');
        // } else {
        //     flash()->error('Unable to create user.');
        // }

        // return redirect()->route('users.index');
        //Validate name, email and password fields
        $this->validate($request, [
            'name'=>'required|max:120',
            'username'=>'required|unique:users',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create($request->only('username', 'name', 'password')); //Retrieving only the email and password data

        $roles = $request['roles']; //Retrieving the roles field
    //Checking if a role was selected
        if (isset($roles)) {

            foreach ($roles as $role) {
            $role_r = Role::where('id', '=', $role)->firstOrFail();            
            $user->assignRole($role_r); //Assigning role to user
            }
        }        
    //Redirect to the users.index view and display message
        return redirect()->route('users.index')
            ->with('status',
             'User successfully added.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id); //Get user with specified id
        $roles = Role::get(); //Get all roles

        return view('users.edit', compact('user', 'roles')); //pass user and roles data to view
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); //Get role specified by id

    //Validate name, email and password fields    
        $this->validate($request, [
            'name'=>'required|max:120',
            #'username'=>'required|unique:users,'.$id,
            'password'=>'required|min:6|confirmed'
		]);
		if(auth()->user()->username == 'admin' && $request->username == 'admin')
			$input = $request->only(['name', 'username', 'password']); //Retrieve the name, email and password fields
		elseif($request->username != 'admin')
			$input = $request->only(['name', 'username', 'password']); //Retrieve the name, email and password fields
		else
			$input = $request->only(['name', 'username']); //Retrieve the name, email and password fields

        $roles = $request['roles']; //Retrieve all roles
        $user->fill($input)->save();
		if($roles){
			if (isset($roles)) {        
				$user->roles()->sync($roles);  //If one or more role is selected associate user to roles          
			}        
			else {
				$user->roles()->detach(); //If no role is selected remove exisiting role associated to a user
			}
		}
        return redirect()->route('users.index')
            ->with('status',
             'User successfully edited.');
    }

    public function destroy($id)
    {
        if ( auth()->user()->id == $id ) {
            $message = 'Deletion of currently logged in user is not allowed :(';
            return redirect()->back();
        }

        if( User::findOrFail($id)->delete() ) {
            $message = 'User has been deleted';
        } else {
            $message = 'User not deleted';
        }

        return redirect()->back()->with('status',
		$message);
    }

    private function syncPermissions(Request $request, $user)
    {
        // Get the submitted roles
        $roles = $request->get('roles', []);
        $permissions = $request->get('permissions', []);

        // Get the roles
        $roles = Role::find($roles);

        // check for current role changes
        if( ! $user->hasAllRoles( $roles ) ) {
            // reset all direct permissions for user
            $user->permissions()->sync([]);
        } else {
            // handle permissions
            $user->syncPermissions($permissions);
        }

        $user->syncRoles($roles);
        return $user;
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
				'users.last_change_password',
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