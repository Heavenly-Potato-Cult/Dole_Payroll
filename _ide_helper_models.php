<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $code e.g. PAGIBIG1, GSIS_MPL, WHT
 * @property string $name
 * @property string|null $short_name
 * @property string $category mandatory, loan, voluntary, tax, union
 * @property int $is_fixed_amount true = fixed peso, false = percentage or variable
 * @property numeric|null $default_amount
 * @property int $display_order Order on payslip per DOLE RO9 standard
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeDeductionEnrollment> $enrollments
 * @property-read int|null $enrollments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType computed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType manual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereDefaultAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereIsFixedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeductionType whereUpdatedAt($value)
 */
	class DeductionType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereUpdatedAt($value)
 */
	class Division extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $plantilla_item_no
 * @property string|null $employee_no
 * @property string $last_name
 * @property string $first_name
 * @property string|null $middle_name
 * @property string|null $suffix
 * @property string|null $date_of_birth
 * @property string|null $gender
 * @property string|null $civil_status
 * @property string $position_title
 * @property int $salary_grade
 * @property int $step
 * @property int $sit_year
 * @property numeric $basic_salary
 * @property numeric $pera Personnel Economic Relief Allowance
 * @property int $division_id
 * @property string $employment_status permanent, casual, coterminous
 * @property string|null $original_appointment_date
 * @property string|null $last_promotion_date
 * @property \Illuminate\Support\Carbon $hire_date
 * @property string|null $gsis_bp_no
 * @property string|null $pagibig_no
 * @property string|null $philhealth_no
 * @property string|null $tin
 * @property string|null $sss_no
 * @property numeric $vacation_leave_balance
 * @property numeric $sick_leave_balance
 * @property string $status active, on_leave, separated, retired
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeDeductionEnrollment> $deductions
 * @property-read int|null $deductions_count
 * @property-read \App\Models\Division $division
 * @property-read float $daily_rate
 * @property-read string $display_name
 * @property-read string $full_name
 * @property-read float $hourly_rate
 * @property-read float $minute_rate
 * @property-read float $semi_monthly_gross
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeePromotionHistory> $promotionHistory
 * @property-read int|null $promotion_history_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee byDivision(int $divisionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCivilStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmployeeNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmploymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereGsisBpNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastPromotionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereOriginalAppointmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePagibigNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePera($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhilhealthNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePlantillaItemNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePositionTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSalaryGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSickLeaveBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSitYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSssNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereVacationLeaveBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee withoutTrashed()
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\DeductionType|null $deductionType
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDeductionEnrollment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDeductionEnrollment activeOn(string $date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDeductionEnrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDeductionEnrollment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDeductionEnrollment query()
 */
	class EmployeeDeductionEnrollment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read float $differential
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeePromotionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeePromotionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeePromotionHistory query()
 */
	class EmployeePromotionHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $office_order_no
 * @property int $employee_id
 * @property string $purpose
 * @property string $destination
 * @property string $travel_type local, regional, national
 * @property string $travel_date_start
 * @property string $travel_date_end
 * @property string $status draft, approved, cancelled
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereOfficeOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereTravelDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereTravelDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereTravelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficeOrder withoutTrashed()
 */
	class OfficeOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollAuditLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollAuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollAuditLog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollAuditLog withoutTrashed()
 */
	class PayrollAuditLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $year
 * @property int $month
 * @property int $cutoff
 * @property string $period_start
 * @property string $period_end
 * @property string|null $release_date
 * @property string $status
 * @property int|null $prepared_by
 * @property string|null $prepared_at
 * @property int|null $reviewed_by
 * @property string|null $reviewed_at
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property int|null $released_by
 * @property string|null $released_at
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereCutoff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch wherePreparedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch wherePreparedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereReleaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereReleasedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollBatch withoutTrashed()
 */
	class PayrollBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $payroll_entry_id
 * @property int $deduction_type_id
 * @property numeric $amount
 * @property int $is_overridden
 * @property string|null $override_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereDeductionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereIsOverridden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereOverrideReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction wherePayrollEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollDeduction withoutTrashed()
 */
	class PayrollDeduction extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntry onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntry withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntry withoutTrashed()
 */
	class PayrollEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $travel_type local, regional, national
 * @property string|null $destination_category e.g. Metro Manila, Regional Center, Others
 * @property int $year
 * @property numeric $daily_rate Full day per diem per COA Circular
 * @property numeric|null $half_day_rate
 * @property string|null $coa_circular_ref e.g. COA Circular 2021-001
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereCoaCircularRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereDailyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereDestinationCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereHalfDayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereTravelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerDiemRate withoutTrashed()
 */
	class PerDiemRate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $salary_grade
 * @property int $step
 * @property int $year
 * @property numeric $monthly_salary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereMonthlySalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereSalaryGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryIndexTable whereYear($value)
 */
	class SalaryIndexTable extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $type newly_hired, salary_differential, nosi, nosa, step_increment
 * @property string $title
 * @property int $year
 * @property int $month
 * @property string $effectivity_date
 * @property string|null $period_start
 * @property string|null $period_end
 * @property int $employee_id
 * @property numeric|null $old_basic_salary
 * @property numeric|null $new_basic_salary
 * @property numeric|null $differential_amount
 * @property numeric|null $pro_rated_days Days worked out of 22-day denominator
 * @property numeric $gross_amount
 * @property numeric $deductions_amount
 * @property numeric $net_amount
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereDeductionsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereDifferentialAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereEffectivityDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereGrossAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereNewBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereOldBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereProRatedDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialPayrollBatch withoutTrashed()
 */
	class SpecialPayrollBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tev_request_id
 * @property int $user_id
 * @property string $step submitted, hr_approved, accountant_certified, rd_approved, cashier_released
 * @property string $action approved, rejected, returned
 * @property string|null $remarks
 * @property string|null $ip_address
 * @property string $performed_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog wherePerformedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereTevRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevApprovalLog withoutTrashed()
 */
	class TevApprovalLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tev_request_id
 * @property string|null $date_returned
 * @property string|null $place_reported_back
 * @property int $travel_completed
 * @property numeric $annex_a_amount
 * @property string|null $annex_a_particulars
 * @property string|null $agency_visited
 * @property string|null $appearance_date
 * @property string|null $contact_person
 * @property int|null $certified_by
 * @property string|null $certified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereAgencyVisited($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereAnnexAAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereAnnexAParticulars($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereAppearanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereCertifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereCertifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereDateReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification wherePlaceReportedBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereTevRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereTravelCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevCertification withoutTrashed()
 */
	class TevCertification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tev_request_id
 * @property string $travel_date
 * @property string $origin
 * @property string $destination
 * @property string|null $mode_of_transport bus, jeepney, boat, plane, vehicle
 * @property numeric $transportation_cost
 * @property numeric $per_diem_amount From per_diem_rates lookup
 * @property int $is_half_day
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereIsHalfDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereModeOfTransport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine wherePerDiemAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereTevRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereTransportationCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereTravelDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevItineraryLine withoutTrashed()
 */
	class TevItineraryLine extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tev_no
 * @property int $office_order_id
 * @property int $employee_id
 * @property string $track cash_advance, reimbursement
 * @property string $purpose
 * @property string $destination
 * @property string $travel_type
 * @property string $travel_date_start
 * @property string $travel_date_end
 * @property int $total_days
 * @property numeric $total_per_diem
 * @property numeric $total_transportation
 * @property numeric $total_other_expenses
 * @property numeric $grand_total
 * @property numeric $cash_advance_amount Amount released if CA track
 * @property numeric $balance_due Grand total minus CA amount
 * @property string $status draft, submitted, hr_approved, accountant_certified, rd_approved, cashier_released, completed
 * @property int|null $submitted_by
 * @property string|null $submitted_at
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereBalanceDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereCashAdvanceAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereOfficeOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereSubmittedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTevNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTotalOtherExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTotalPerDiem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTotalTransportation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTravelDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTravelDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereTravelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TevRequest withoutTrashed()
 */
	class TevRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

