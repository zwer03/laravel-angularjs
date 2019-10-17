<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Hmo;
use App\HmoConsultantTypePf;
use Illuminate\Support\Facades\DB;
class HmoController extends Controller
{

	public function index(Request $request) {

		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		try {
			// $returndata['data'] = DB::table('hmo')->orderBy('external_id')->paginate(15);
			$hmos = Hmo::select(
				'hmo.id',
				'hmo.external_id',
				'hmo.name',
				'hmo.created_at'
			)
			->orderBy('hmo.external_id', 'asc')
			->paginate(20);
			
			$formatted_hmos = array();
			$hmo_items = $hmos->items();
			foreach($hmo_items as $key=>$hmo){
				$formatted_hmos[$hmo->id]['Hmo']['id'] = $hmo->id;
				$formatted_hmos[$hmo->id]['Hmo']['name'] = $hmo->name;
				$hmo_consultant_type_pfs = HmoConsultantTypePf::select(
					'consultant_types.name as consultant_type_name',
					'hmo_consultant_type_pfs.default_pf_amount'
				)
				->leftJoin('consultant_types','consultant_types.id','=','hmo_consultant_type_pfs.consultant_type_id')
				->where('hmo_consultant_type_pfs.hmo_id','=',$hmo->id)
				->get();
				foreach($hmo_consultant_type_pfs as $hctp_key=>$hctp_value){
					$formatted_hmos[$hmo->id]['HmoConsultantTypePfs'][$hctp_key]['consultant_type_name'] = $hctp_value->consultant_type_name;
					$formatted_hmos[$hmo->id]['HmoConsultantTypePfs'][$hctp_key]['default_pf_amount'] = $hctp_value->default_pf_amount;
				}
			}
			foreach ($hmos->items() as &$hmo) {
				if (isset($formatted_hmos[$hmo->id])) {
					$hmo['Hmo'] = $formatted_hmos[$hmo->id]['Hmo'];
					$hmo['HmoConsultantTypePfs'] = $formatted_hmos[$hmo->id]['HmoConsultantTypePfs'];
				}
			}
			// Log::info($hmos);
			$returndata['data'] = $hmos;
		} catch(\Exception $e) {
			$returndata['success'] = false;
			$returndata['message'] = 'Get hmo error! Stacktrace: (Message: '.$e->getMessage().'; Line: '.$e->getLine().')';
		}
		return $returndata;
	}

	public function add(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		DB::beginTransaction();
		try {
			Log::info('Start adding hmo');
			if (request()->isMethod('post')) {
				Log::info($request);
				$existing_hmo = Hmo::where('external_id','=',$request['Hmo']['external_id'])->first();
				if(!$existing_hmo){
					$save_hmo = new Hmo;
					$save_hmo->external_id = $request['Hmo']['external_id'];
					$save_hmo->name = $request['Hmo']['name'];
					if($save_hmo->save()){
						if(isset($request['HmoConsultantPf']) && !empty($request['HmoConsultantPf'])){
							foreach($request['HmoConsultantPf'] as $hmo_consultant_type_pf){
								$save_hmo_consultant_type_pf = new HmoConsultantTypePf;
								$save_hmo_consultant_type_pf->hmo_id = $save_hmo->id;
								$save_hmo_consultant_type_pf->consultant_type_id = $hmo_consultant_type_pf['consultant_type_id'];
								$save_hmo_consultant_type_pf->default_pf_amount = $hmo_consultant_type_pf['amount'];
								$save_hmo_consultant_type_pf->save();
							}
						}
						$returndata['message'] = 'Hmo has been saved.';
					}
				}else
					$returndata = array('success'=>false, 'message'=>'Hmo already exists.', 'data'=>null);
			}
			DB::commit();
			Log::info('End adding hmo');
		} catch (\Exception $e) {
			DB::rollBack();
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
	}
	
	public function edit(Request $request)
    {
		$returndata = array('success'=>true, 'message'=>null, 'data'=>null);
		//Log::info($request->has('User.password'));
		DB::beginTransaction();
		try {
			Log::info('Start editing hmo');
			if (request()->isMethod('post')) {
				Log::info($request);
				$save_hmo = Hmo::findOrFail($request['Hmo']['id']);
				if($save_hmo->external_id != $request['Hmo']['external_id'])
					if(Hmo::where('external_id','=',$request['Hmo']['external_id'])->first())
						throw new \Exception("Duplicate Id!", 1);
				$save_hmo->external_id = $request['Hmo']['external_id'];
				$save_hmo->name = $request['Hmo']['name'];
				if ($save_hmo->save()){
					if(isset($request['HmoConsultantPf']) && !empty($request['HmoConsultantPf'])){
						
						foreach($request['HmoConsultantPf'] as $hmo_consultant_type_pf){
							if(!isset($hmo_consultant_type_pf['id'])){
								$save_hmo_consultant_type_pf = new HmoConsultantTypePf;
								$save_hmo_consultant_type_pf->hmo_id = $save_hmo->id;
								$save_hmo_consultant_type_pf->consultant_type_id = $hmo_consultant_type_pf['consultant_type_id'];
								$save_hmo_consultant_type_pf->default_pf_amount = $hmo_consultant_type_pf['amount'];
								$save_hmo_consultant_type_pf->save();
							}
							else{
								$save_hmo_consultant_type_pf = HmoConsultantTypePf::findOrFail($hmo_consultant_type_pf['id']);
								$save_hmo_consultant_type_pf->consultant_type_id = $hmo_consultant_type_pf['consultant_type_id'];
								$save_hmo_consultant_type_pf->default_pf_amount = $hmo_consultant_type_pf['amount'];
								$save_hmo_consultant_type_pf->save();
							}
						}
					}
					$returndata['message'] = 'Hmo has been saved.';
				}
			}else{
				// $hmo = DB::table('hmo')->where('id','=',$request['id'])->first();
				$hmo = Hmo::select(
					'hmo.id',
					'hmo.external_id',
					'hmo.name',
					'hmo.created_at'
				)
				->where('id','=',$request['id'])
				->first();
				
				$hmo_consultant_type_pfs = HmoConsultantTypePf::select(
					'consultant_types.name',
					'hmo_consultant_type_pfs.id',
					'hmo_consultant_type_pfs.consultant_type_id',
					'hmo_consultant_type_pfs.default_pf_amount'
				)
				->leftJoin('consultant_types','consultant_types.id','=','hmo_consultant_type_pfs.consultant_type_id')
				->where('hmo_consultant_type_pfs.hmo_id','=',$hmo->id)
				->get();

				$consultant_types = DB::table('consultant_types')->pluck('id', 'name');
				$formatted_hmo = array();
				$formatted_hmo['Hmo'] = $hmo;
				$formatted_hmo['HmoConsultantTypePfs'] = $hmo_consultant_type_pfs;
				$formatted_hmo['ConsultantTypes'] = $consultant_types;
				
				if (!$hmo)
					throw new \Exception("Invalid hmo", 1);
				$returndata['data'] = $formatted_hmo;
			}
			DB::commit();
			Log::info('End editing hmo');
		} catch (\Exception $e) {
			DB::rollBack();
			$returndata['success'] = false;
			$returndata['message'] = $e->getMessage();
		}
		return $returndata;
    }
}