<?php

namespace Database\Seeders;

use App\Models\PatientQueue;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class DefaultPaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentGateways = [
            [
                'payment_gateway_id' => PatientQueue::MANUALLY,
                'payment_gateway' => PatientQueue::PAYMENT_METHOD[1],
            ],
            // [
            //     'payment_gateway_id' => PatientQueue::STRIPE,
            //     'payment_gateway' => PatientQueue::PAYMENT_METHOD[2],
            // ],
            // [
            //     'payment_gateway_id' => PatientQueue::PAYPAL,
            //     'payment_gateway' => PatientQueue::PAYMENT_METHOD[4],
            // ],
            // [
            //     'payment_gateway_id' => PatientQueue::AUTHORIZE,
            //     'payment_gateway' => PatientQueue::PAYMENT_METHOD[6],
            // ],
            // [
            //     'payment_gateway_id' => PatientQueue::PAYTM,
            //     'payment_gateway' => PatientQueue::PAYMENT_METHOD[7],
            // ],

        ];

        foreach ($paymentGateways as $paymentGateway) {
            PaymentGateway::create($paymentGateway);
        }
    }
}
