<?php
namespace Modules\Payroll\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
// TODO: implement ApprovePayrollRequest
class ApprovePayrollRequest extends FormRequest {
    public function authorize() { return true; }
    public function rules() { return []; }
}
