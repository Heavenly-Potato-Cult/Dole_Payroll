<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
// TODO: implement UpdateEmployeeRequest
class UpdateEmployeeRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return []; }
}
