<?php

namespace App\Repositories;

use App\Interfaces\PharmacistRepositoryInterface;
use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Notifications\SendPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PharmacyRepository extends CrudRepository implements PharmacyRepositoryInterface
{
    
    protected Model $model;
    protected $employee_repo ;
    public $dashboard_type = "pharmacy";
    public $table = "pharmacies";
    public $employee_table = "pharmacists";
    public function __construct(Pharmacy $model ,PharmacistRepositoryInterface $employee_repo)
    {
        $this->model = $model;
        $this->employee_repo = $employee_repo;

    }


    public function createPharmacywithUser( array $data )
    {
        return DB::transaction(function () use ($data) {
            unset($data["email"]);
            $pharmacy = $this->create($data);
            $employee_data = $this->handleData($data);
            $employee_data["pharmacy_id"] = $pharmacy->id;
            $password = $employee_data["un_hash"];
            unset($employee_data["un_hash"]);
            $employee = $this->employee_repo->create($employee_data);
            $employee->notify(new SendPassword($password ,$this->dashboard_type));

            return $employee ;
        });

    }

    public function updatePharmacywithUser( array $data  ,$pharmacy_id )
    {
        return DB::transaction(function () use ($data,$pharmacy_id) {
            unset($data["email"]);
            $pharmacy = $this->update($data , $pharmacy_id) ;
            $employee_data["pharmacy_id"] = $pharmacy->id;
            $password = $employee_data["un_hash"];
            unset($employee_data["un_hash"]);
            $employee = $this->employee_repo->model::where('is_owner',1)->first();
            $employee->notify(new SendPassword($password ,$this->dashboard_type));
            return $employee ;
        });

    }



    public function deletePharmacywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->employee_repo->model::whereIn('pharmacy_id', $ids)->delete();
            $this->model::whereIn('id', $ids)->delete();

        });

    }


    public function restorePharmacywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->model::withTrashed()
            ->whereIn('id', $ids)
            ->restore();

            $this->employee_repo->model::withTrashed()
                ->whereIn('pharmacy_id', $ids)
                ->restore();
        });

    }


 
    protected function handleData(array $data)
    {

        $code = rand(1000,9999);

        $password = rand(1000,9999)."Admin".rand(1000,9999);

        $employee["name"] = $data["name"]."_admin".$code ;

        $employee["active"] = 1;

        $employee["email"] = $data["email"];

        $employee["password"] = Hash::make($password) ;
        $employee["un_hash"] = $password; 
        $employee["role_id"] = 1;
        $employee["is_owner"] = 1;
        return $employee;
      
    }
}
