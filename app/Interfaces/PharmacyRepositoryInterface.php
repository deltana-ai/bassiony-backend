<?php
namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;

interface PharmacyRepositoryInterface extends ICrudRepository
{


    public function createPharmacywithUser( array $data );

    public function updatePharmacywithUser( array $data  ,$pharmacy_id );
    
    public function deletePharmacywithUsers(array $ids );
    
    public function restorePharmacywithUsers(array $ids );
    public function getPharmacyProducts(int $pharmacyId);
}
