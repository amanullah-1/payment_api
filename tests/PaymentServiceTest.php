<?php
// tests/Service/PaymentServiceTest.php
namespace App\Tests\Service;

use App\Service\PaymentService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PaymentServiceTest extends TestCase
{
    private $client;
    private $paymentService;

    protected function setUp(): void
    {
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->paymentService = new PaymentService($this->client);
    }

    public function testProcessShift4Payment()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([
            'id' => 'txn_12345',
            'amount' => 100,
            'currency' => 'EUR',
            'card' => ['number' => '4242424242424242'],
        ]);

        $this->client->method('request')->willReturn($response);

        $result = $this->paymentService->processPayment(
            'shift4', 100, 'EUR', '4242424242424242', '2024', '10', '123', 'VISA', 'DB'
        );

        $result = json_decode($result, true);

        $this->assertEquals(100, $result['amount']);
        $this->assertEquals('EUR', $result['currency']);
    }
}
