<?php

namespace App\Repositories;

use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use Hash;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Notifications\SendPassword;
use Illuminate\Support\Facades\DB;

class CompanyRepository extends CrudRepository implements CompanyRepositoryInterface
{
    protected Model $model;
    protected $employee_repo ;
    public $dashboard_type = "company";
    public $table = "companies";
    public $employee_table = "employees";
    public function __construct(Company $model ,EmployeeRepositoryInterface $employee_repo)
    {
        $this->model = $model;
        $this->employee_repo = $employee_repo;

    }


    public function createCompanywithUser( array $data )
    {
        return DB::transaction(function () use ($data) {
            unset($data["email"]);
            $company = $this->create($data);
            $employee_data = $this->handleData($data);
            $employee_data["company_id"] = $company->id;
            $password = $employee_data["un_hash"];
            unset($employee_data["un_hash"]);
            $employee = $this->employee_repo->create($employee_data);
            $employee->notify(new SendPassword($password ,$this->dashboard_type));

            return $employee ;
        });

    }

    public function updateCompanywithUser( array $data  ,$company_id )
    {
        return DB::transaction(function () use ($data,$company_id) {
            unset($data["email"]);
            $company = $this->update($data , $company_id) ;
            $employee_data["company_id"] = $company->id;
            $password = $employee_data["un_hash"];
            unset($employee_data["un_hash"]);
            $employee = $this->employee_repo->model::where('is_owner',1)->first();
            $employee->notify(new SendPassword($password ,$this->dashboard_type));
            return $employee ;
        });

    }



    public function deleteCompanywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            Employee::whereIn('company_id', $ids)->delete();
            $this->model::whereIn('id', $ids)->delete();

        });

    }


    public function restoreCompanywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->model::withTrashed()
            ->whereIn('id', $ids)
            ->restore();

            $this->employee_repo->model::withTrashed()
                ->whereIn('company_id', $ids)
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
