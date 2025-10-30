<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $owner_permissions = [
            
            //////////////////////////////////////////
            'brand-list','brand-create' ,'brand-edit' ,'brand-delete',
            ////////////////////////////////////////
            'category-list' ,'category-create','category-edit' ,'category-delete' ,
           
            ////////////////////////////////////////
            'product-list','product-create','product-edit','product-delete',

            ////////////////////////////////////////
            'pharmacy-create' , 'pharmacy-edit' , 'pharmacy-list' , 'pharmacy-delete' ,
            ////////////////////////////////////////
            'pharmacist-list' ,'pharmacist-edit' ,'pharmacist-delete' ,
            ////////////////////////////////////////
            'company-list' ,'company-create','company-edit','company-delete',
            ////////////////////////////////////////
          
            'admin-list' , 'admin-create' , 'admin-edit' , 'admin-delete',
            ////////////////////////////////////////
            'role-list' ,'role-create' ,'role-edit' ,'role-delete' ,
            ////////////////////////////////////////
            'contact-list' ,'contact-delete' ,
            ////////////////////////////////////////////////////////
            'slider-list' , 'slider-create' , 'slider-edit' , 'slider-delete',
            /////////////////////////////////////////////////////////////////////////////
            'employee-list' ,  'employee-edit' , 'employee-delete',

           
         ];

         $company_permissions = [
            'employee-list' , 'employee-create' , 'employee-edit' , 'employee-delete',
            ////////////////////////////////////////
            'role-list' ,'role-create' ,'role-edit' ,'role-delete' ,
            ///////////////////////////////////////////////////////////////
            'warehouse-list' , 'warehouse-create' , 'warehouse-edit' , 'warehouse-delete',
            ////////////////////////////////////////////////////////////////////////////////////////
            'warehouse-product-list' , 'warehouse-product-create' , 'warehouse-product-edit' , 'warehouse-product-delete',
            //////////////////////////////////////////////////////////////////////////
            'company-list','pharmacy-list',
            /////////////////////////////////////////////////////////////////////////
            'offer-list' , 'offer-create' , 'offer-edit' , 'offer-delete',
            //////////////////////////////////////////////////////////////////
            'response_offer-list' , 'response_offer-edit' , 'response_offer-delete',
            //////////////////////////////////////////////////////////////////
            'product-list' ,
            /////////////////////////////////////////////////////////////
            'order-list' , 'order-create' , 'order-edit' , 'order-delete',
         
         
         ];

          $pharmacist_permissions = [
            'pharmacist-list' , 'pharmacist-create' , 'pharmacist-edit' , 'pharmacist-delete',
            ////////////////////////////////////////
            'role-list' ,'role-create' ,'role-edit' ,'role-delete' ,
            ///////////////////////////////////////////////////////////////
            'branch-list' , 'branch-create' , 'branch-edit' , 'branch-delete',
            ////////////////////////////////////////////////////////////////////////////////////////
            'branch-product-list' , 'branch-product-create' , 'branch-product-edit' , 'branch-product-delete',
            //////////////////////////////////////////////////////////////////////////
            'company-list','pharmacy-list',
            /////////////////////////////////////////////////////////////////////////
            'offer-list' , 'offer-create' , 'offer-edit' , 'offer-delete',
            //////////////////////////////////////////////////////////////////
            'response_offer-list' , 'response_offer-create' ,
            //////////////////////////////////////////////////////////////////
            'company-offer-list' ,
            //////////////////////////////////////////////////////////////////
            'product-list' ,
            /////////////////////////////////////////////////////////////
            'order-list' , 'order-edit' , 'order-delete',
           /////////////////////////////////////////////////////////////
            'company-order-list' , 'company-order-cancel' ,
         
         ];
          
         foreach ($owner_permissions as $permission) {
           Permission::create(['name' => $permission,'guard_name'=>'admins']);
         }

         foreach ($company_permissions as $permission) {
           Permission::create(['name' => $permission,'guard_name'=>'employees']);
         }
         foreach ($pharmacist_permissions as $permission) {
           Permission::create(['name' => $permission,'guard_name'=>'pharmacists']);
         }


    }
}
