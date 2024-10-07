<?php
// src/Command/AppExampleCommand.php
namespace App\Command;

use App\Service\PaymentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppExampleCommand extends Command
{
    protected static $defaultName = 'app:example';

    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Process a payment through Shift4 or ACI')
            ->addArgument('system', InputArgument::REQUIRED, 'The payment system (aci|shift4)')
            ->addArgument('amount', InputArgument::REQUIRED, 'Amount of the payment')
            ->addArgument('currency', InputArgument::REQUIRED, 'Currency of the payment')
            ->addArgument('card_number', InputArgument::REQUIRED, 'Card number')
            ->addArgument('exp_year', InputArgument::REQUIRED, 'Expiration year of the card')
            ->addArgument('exp_month', InputArgument::REQUIRED, 'Expiration month of the card')
            ->addArgument('cvv', InputArgument::REQUIRED, 'Card CVV')
            ->addArgument('payment_brand', InputArgument::OPTIONAL, 'Payment Brand')
            ->addArgument('payment_type', InputArgument::OPTIONAL, 'Payment Type');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $system = $input->getArgument('system');
        $amount = $input->getArgument('amount');
        $currency = $input->getArgument('currency');
        $cardNumber = $input->getArgument('card_number');
        $expYear = $input->getArgument('exp_year');
        $expMonth = $input->getArgument('exp_month');
        $cvv = $input->getArgument('cvv');
        $paymentBrand = $input->getArgument('payment_brand');
        $paymentType = $input->getArgument('payment_type');

        // Process the payment
        $response = $this->paymentService->processPayment(
            $system, $amount, $currency, $cardNumber, $expYear, $expMonth, $cvv, $paymentBrand, $paymentType );

        // Output the response to the console
        $output->writeln(json_encode($response));

        return Command::SUCCESS;
    }
}
