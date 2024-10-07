<?php 
// src/Controller/PaymentController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PaymentService;

class PaymentController
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @Route("/app/example/{system}", name="process_payment", methods={"POST"})
     */
    public function processPayment(Request $request, $system): Response
    {
        // Get input parameters from the request
        $amount = $request->get('amount');
        $currency = $request->get('currency');
        $cardNumber = $request->get('card_number');
        $expYear = $request->get('exp_year');
        $expMonth = $request->get('exp_month');
        $cvv = $request->get('cvv');
        $paymentBrand = $request->get('payment_brand');
        $paymentType = $request->get('payment_type');

        // Process the payment based on the system (Shift4 or ACI)
        $response = $this->paymentService->processPayment(
            $system, $amount, $currency, $cardNumber, $expYear, $expMonth, $cvv, $paymentBrand, $paymentType
        );

        $response = new Response($response);

        return new Response($response, 200, ['Content-Type' => 'application/json']);
    }
}
