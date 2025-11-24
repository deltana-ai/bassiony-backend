<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
     public function authorize(): bool
    {
        return true; // لو فيه صلاحيات تقدر تتحكم هنا
    }


    public function rules(): array
    {
        $rules = [
            'name_ar'            => [ 'required','string','max:255'],
            'name_en'            => [ 'required','string','max:255'],
            'active_ingredients' => ['nullable','string','max:1000'],
            'dosage_form'        => ['nullable','string','max:225'],
            'gtin'               => [ 'nullable','string','max:200','required_without_all:bar_code,qr_code'],
            'bar_code'           => [ 'nullable','string','max:200','required_without_all:gtin,qr_code'],
            'qr_code'            => [ 'nullable','string','max:200','required_without_all:gtin,bar_code'],
            'scientific_name'    => [ 'nullable','string','max:255'],
            'position'           => [ 'nullable','integer'],
            'active'             => [ 'boolean'],
            'show_home'          => [ 'boolean'],
            'description'        => [ 'nullable','string'],
            'category_id'        => [ 'required','exists:categories,id'],
            'brand_id'           => [ 'nullable','exists:brands,id'],
            'rating'             => [ 'nullable','numeric','min:0','max:9.9'],
            'price'              => [ 'nullable','numeric'],
            'tax'              => [ 'nullable','numeric'],
        ];
        if ($this->isMethod('post')) {
          $rules['gtin'][]     = 'unique:products,gtin';
          $rules['qr_code'][]  = 'unique:products,qr_code';
          $rules['bar_code'][] = 'unique:products,bar_code';


      }
      else{
        $product = $this->route('product')?? $this->route('id');
       // dd($product);
        $rules['gtin'][]     = Rule::unique('products','gtin')->ignore($product->id);
        $rules['qr_code'][]  = Rule::unique('products','qr_code')->ignore($product->id);
        $rules['bar_code'][] = Rule::unique('products','bar_code')->ignore($product->id);

      }

      return $rules;
    }
}
