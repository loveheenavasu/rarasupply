<?php
namespace Ced\CsStripePayment\Controller\Index;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Stripe extends \Magento\Framework\App\Action\Action
{

	public function execute()
	{
		$data = $this->getRequest()->getParams();

			try {
			
			
			\Stripe\Stripe::setApiKey("sk_test_eFpo0m8VGG8VGDblbrxF85sY");
			
			$Tokenparams = array (
					"card" => array (
							"name" => "test test",
							"number" => 4242424242424242,
							"cvc" => 444,
							"exp_month" => 12,
							"exp_year" => 2020
					)
			);
			$createtoken1 = \Stripe\Token::create ( $Tokenparams );
			
			$customer = \Stripe\Customer::retrieve("cus_B0Nt7Syd5GQ1BF");
			$customer->sources->create(array("source" => $createtoken1->id));
			$charge1 = \Stripe\Charge::create ( array (
					"amount" => 100 * 100, // amount in cents
					"currency" => 'USD',
					"source" => $createtoken1->id,
					"description" => "Create Payment"
			));
			print_r($charge1); 
		} catch ( \Stripe\Error\Card $e ) {
			throw new \Magento\Framework\Exception\LocalizedException ( __ ( $e->getMessage () ) );
		}
	}
}

		