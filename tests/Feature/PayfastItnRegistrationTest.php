<?php

namespace Tests\Feature;

use App\Http\Middleware\FirebaseAuth;
use App\Services\FirebaseAuthService;
use App\Services\PayfastService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Value\Email;
use Tests\TestCase;

class PayfastItnRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required to run PayFast ITN integration tests.');
        }

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('firebase_uid')->unique();
            $table->string('student_number')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('role')->default('USER');
            $table->string('status')->default('ACTIVE');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('pending_registrations', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('firebase_uid')->unique();
            $table->string('email');
            $table->string('full_name')->nullable();
            $table->string('student_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamp('email_verified_checked_at')->nullable();
            $table->string('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('registration_payments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('pending_registration_id');
            $table->string('merchant_payment_id')->unique();
            $table->string('payfast_payment_id')->nullable();
            $table->string('provider');
            $table->integer('amount_cents');
            $table->string('currency');
            $table->string('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('paid_at')->nullable();
        });

        // Prevent needing Firebase credentials during tests.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldNotReceive('handle');
        });
    }

    public function test_complete_itn_marks_payment_paid_and_creates_user_when_email_verified(): void
    {
        DB::table('pending_registrations')->insert([
            'id' => 'pr-1',
            'firebase_uid' => 'uid-100',
            'email' => 'paid@example.com',
            'full_name' => 'Paid User',
            'student_number' => '100',
            'phone_number' => '081',
            'status' => 'PENDING_PAYMENT',
            'created_at' => now(),
        ]);

        DB::table('registration_payments')->insert([
            'id' => 'pay-1',
            'pending_registration_id' => 'pr-1',
            'merchant_payment_id' => 'mp-1',
            'provider' => 'PAYFAST',
            'amount_cents' => 10000,
            'currency' => 'ZAR',
            'status' => 'PENDING',
            'created_at' => now(),
        ]);

        $this->mock(PayfastService::class, function ($mock) {
            $mock->shouldReceive('isValidSourceIp')->andReturn(true);
            $mock->shouldReceive('signatureMatches')->andReturn(true);
            $mock->shouldReceive('validateItn')->andReturn(true);
            $mock->shouldReceive('amountStringToCents')->andReturn(10000);
        });

        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('getUser')
                ->andReturn(new UserRecord([
                    'uid' => 'uid-100',
                    'email' => new Email('paid@example.com'),
                    'emailVerified' => true,
                ]));
        });

        $resp = $this->post('/api/payfast/itn/registration', [
            'merchant_id' => config('payfast.merchant_id'),
            'm_payment_id' => 'mp-1',
            'pf_payment_id' => 'pf-1',
            'payment_status' => 'COMPLETE',
            'amount_gross' => '100.00',
            'signature' => 'x',
        ]);

        $resp->assertStatus(200);

        $payment = DB::table('registration_payments')->where('merchant_payment_id', 'mp-1')->first();
        $this->assertNotNull($payment);
        $this->assertSame('PAID', $payment->status);

        $pending = DB::table('pending_registrations')->where('id', 'pr-1')->first();
        $this->assertNotNull($pending);
        $this->assertSame('COMPLETED', $pending->status);

        $user = DB::table('users')->where('firebase_uid', 'uid-100')->first();
        $this->assertNotNull($user);
    }

    public function test_complete_itn_preserves_paid_payment_when_firebase_temporarily_fails(): void
    {
        DB::table('pending_registrations')->insert([
            'id' => 'pr-2',
            'firebase_uid' => 'uid-200',
            'email' => 'fail@example.com',
            'full_name' => 'Fail User',
            'status' => 'PENDING_PAYMENT',
            'created_at' => now(),
        ]);

        DB::table('registration_payments')->insert([
            'id' => 'pay-2',
            'pending_registration_id' => 'pr-2',
            'merchant_payment_id' => 'mp-2',
            'provider' => 'PAYFAST',
            'amount_cents' => 10000,
            'currency' => 'ZAR',
            'status' => 'PENDING',
            'created_at' => now(),
        ]);

        $this->mock(PayfastService::class, function ($mock) {
            $mock->shouldReceive('isValidSourceIp')->andReturn(true);
            $mock->shouldReceive('signatureMatches')->andReturn(true);
            $mock->shouldReceive('validateItn')->andReturn(true);
            $mock->shouldReceive('amountStringToCents')->andReturn(10000);
        });

        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('getUser')->andThrow(new \RuntimeException('firebase down'));
        });

        $resp = $this->post('/api/payfast/itn/registration', [
            'merchant_id' => config('payfast.merchant_id'),
            'm_payment_id' => 'mp-2',
            'pf_payment_id' => 'pf-2',
            'payment_status' => 'COMPLETE',
            'amount_gross' => '100.00',
            'signature' => 'x',
        ]);

        $resp->assertStatus(200);

        $payment = DB::table('registration_payments')->where('merchant_payment_id', 'mp-2')->first();
        $this->assertNotNull($payment);
        $this->assertSame('PAID', $payment->status);

        $pending = DB::table('pending_registrations')->where('id', 'pr-2')->first();
        $this->assertNotNull($pending);
        $this->assertSame('PENDING_PAYMENT', $pending->status);

        $user = DB::table('users')->where('firebase_uid', 'uid-200')->first();
        $this->assertNull($user);
    }
}
