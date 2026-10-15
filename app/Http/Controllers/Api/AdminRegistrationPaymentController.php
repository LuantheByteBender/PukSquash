<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\RegistrationPaymentRepository;
use Illuminate\Http\JsonResponse;

class AdminRegistrationPaymentController extends Controller
{
    public function __construct(
        private RegistrationPaymentRepository $payments,
    ) {}

    public function index(): JsonResponse
    {
        $records = $this->payments->listAll();

        $mapped = array_map(function (object $row): array {
            return [
                'id'                      => $row->id,
                'pending_registration_id' => $row->pending_registration_id,
                'merchant_payment_id'     => $row->merchant_payment_id,
                'payfast_payment_id'      => $row->payfast_payment_id,
                'provider'               => $row->provider,
                'amount_cents'           => $row->amount_cents,
                'currency'               => $row->currency,
                'status'                 => $row->status,
                'paid_at'                => $row->paid_at,
                'created_at'             => $row->created_at,
                'full_name'              => $row->full_name,
                'email'                  => $row->email,
                'student_number'         => $row->student_number,
                'phone_number'           => $row->phone_number,
            ];
        }, $records);

        return response()->json($mapped);
    }
}
