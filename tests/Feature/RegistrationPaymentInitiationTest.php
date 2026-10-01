<?php

namespace Tests\Feature;

use App\Http\Middleware\FirebaseAuth;
use App\Services\PayfastService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrationPaymentInitiationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required to run registration payment initiation tests.');
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
    }

    public function test_initiate_payment_requires_email_verified(): void
    {
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set('firebase_uid', 'uid-1');
                    $request->attributes->set('firebase_email', 'test@example.com');
                    $request->attributes->set('firebase_email_verified', false);

                    return $next($request);
                });
        });

        $resp = $this->postJson('/api/registration/payment/initiate', [
            'full_name' => 'Test User',
            'student_number' => '123',
            'phone_number' => '082',
        ]);

        $resp->assertStatus(403);
        $resp->assertJson([
            'code' => 'EMAIL_NOT_VERIFIED',
        ]);
    }

    public function test_initiate_payment_returns_payfast_fields(): void
    {
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set('firebase_uid', 'uid-2');
                    $request->attributes->set('firebase_email', 'user2@example.com');
                    $request->attributes->set('firebase_email_verified', true);

                    return $next($request);
                });
        });

        $this->mock(PayfastService::class, function ($mock) {
            $mock->shouldReceive('processUrl')->andReturn('https://sandbox.payfast.co.za/eng/process');
            $mock->shouldReceive('amountStringToCents')->andReturn(10000);
            $mock->shouldReceive('signature')->andReturn('sig');
        });

        $resp = $this->postJson('/api/registration/payment/initiate', [
            'full_name' => 'User Two',
            'student_number' => '456',
            'phone_number' => '083',
        ]);

        $resp->assertStatus(200);
        $resp->assertJsonPath('payfast.process_url', 'https://sandbox.payfast.co.za/eng/process');
        $resp->assertJsonPath('payfast.fields.signature', 'sig');
        $resp->assertJsonPath('payfast.fields.item_name', 'PukSquash Registration');
    }
}
