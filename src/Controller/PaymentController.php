<?php 
// src/Controller/PaymentController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function processPayment(Request $request, $system): JsonResponse
    {
        // Get input parameters from the request
        $amount = $request->get('amount');
        $currency = $request->get('currency');
        $cardNumber = $request->get('card_number');
        $expYear = $request->get('exp_year');
        $expMonth = $request->get('exp_month');
        $cvv = $request->get('cvv');
        $paymentBrand = $request->get('payment_brand');  // e.g., VISA, MasterCard
        $paymentType = $request->get('payment_type');    // e.g., DB (debit), CD (credit)

        // Validate required fields
        if (!$amount || !$currency || !$cardNumber || !$expYear || !$expMonth || !$cvv) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing required parameters.',
            ], 400);
        }

        try {
            // Process the payment based on the system (Shift4 or ACI)
            $response = $this->paymentService->processPayment(
                $system, 
                $amount, 
                $currency, 
                $cardNumber, 
                $expYear, 
                $expMonth, 
                $cvv, 
                $paymentBrand, 
                $paymentType
            );

            // Return the unified response in JSON format
            return new JsonResponse($response, 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error message
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
