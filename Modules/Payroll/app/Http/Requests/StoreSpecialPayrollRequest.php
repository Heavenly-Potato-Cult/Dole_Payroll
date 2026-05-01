<?php
namespace Modules\Payroll\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
// TODO: implement StoreSpecialPayrollRequest
class StoreSpecialPayrollRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return []; }
}
