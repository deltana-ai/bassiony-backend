<?php
namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;

interface CompanyRepositoryInterface extends ICrudRepository
{

    public function createCompanywithUser( array $data );

    public function updateCompanywithUser( array $data  ,$company_id );
    
    public function deleteCompanywithUsers(array $ids );
    
    public function restoreCompanywithUsers(array $ids );

    public function getCompanyProducts(int $companyId, array $filters = []);


}
