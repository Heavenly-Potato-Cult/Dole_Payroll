<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
// TODO: implement StoreOfficeOrderRequest
class StoreOfficeOrderRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return []; }
}
