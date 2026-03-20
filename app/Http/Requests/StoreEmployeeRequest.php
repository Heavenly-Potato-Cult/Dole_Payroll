<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
// TODO: implement StoreEmployeeRequest
class StoreEmployeeRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return []; }
}
