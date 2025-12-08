<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UnitUnderTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'reference_no',
        'status',
        'assigned_approver_id',
        'assigned_personnel_id',
        'batch_no',

        'accreditation',
        'service_type',
        'capability',
        'description',

        'name',
        'method_procedure',
        'manufacturer',
        'model',
        'serial_number',
        'identification',
        'sub_contracted',

        'type',
        'receiving_method',
        'tracking_no',
        'qty',
        'unit',
        'lead_time',
        'rush_fee',
        'discount',

        'received_date',
        'estimated_completion_date',
        'onsite_date',
        'service_interval',
        'service_interval_type',
        'accessories',
        'receiving_condition',
        'require_conformance',
        'require_opinion',
        'deviate_methods',
        'request_new_method',
        'request_details',
        'delivery_method',
        'delivery_contact',
        'special_instructions',
        'test_points',
        'approver_remarks',

        'uut_status',
        'reporting_status',
        'date_performed',
        'certificate_path',
        'datasheet_path',
        'tsrf_path',
        'report_payload',
        'report_custom_html',
    ];

    protected $casts = [
        'sub_contracted'            => 'boolean',
        'received_date'             => 'date',
        'estimated_completion_date' => 'date',
        'onsite_date'               => 'date',
        'require_conformance'       => 'boolean',
        'require_opinion'           => 'boolean',
        'deviate_methods'           => 'boolean',
        'request_new_method'        => 'boolean',
        'date_performed'            => 'date',
        'certificate_path'          => 'array',
        'datasheet_path'            => 'array',
        'tsrf_path'                 => 'array',
        'report_payload'            => 'array',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'assigned_approver_id');
    }

    public function personnel()
    {
        return $this->belongsTo(User::class, 'assigned_personnel_id');
    }
}
