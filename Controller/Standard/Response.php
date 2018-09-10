<?php

namespace Payswitch\Theteller\Controller\Standard;

class Response extends \Payswitch\Theteller\Controller\Checkout
{   

    public function execute()
    {
        // Initialize return url
        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');
        $params = $this->getRequest()->getParams();
        //var_dump($params['status']);
        //exit();
//die($this->getCheckoutHelper()->getUrl(''));
     
		
        $status = $this->getRequest()->getParam('status');
        $transactionid = $this->getRequest()->getParam('transaction_id');
        


        try {
           // die($payment->getPayment());
            $paymentMethod = $this->getPaymentMethod();
            
            // Get payment method code
            $code = $paymentMethod->getCode();
            //die($code);
            // Get quote from session
            $quoteId = $this->getQuote()->getId();
            //die($code);
            $quote = $this->_quote->load($quoteId);
            $quote->getCustomerEmail();
		            /**
             * Success
             * The transaction is valid. Therefore you can update this transaction.
             */
            if ($status == '000')
            {
				
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');

                if ($this->getCustomerSession()->isLoggedIn()) {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
                }

                else {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
                }

                // $quote->setCustomerEmail($params['email']);
                $quote->setPaymentMethod($code);
                $quote->getPayment()->importData(['method' => $code]);
                $quote->save();
				//var_dump($t);
                //exit();
				
                $this->initCheckout();
                try {
                    $this->cartManagement->placeOrder($this->_checkoutSession->getQuote()->getId(), $this->_quote->getPayment());
                    $order = $this->getOrder();

					//var_dump($order);
		            //exit(); 
                    $payment = $order->getPayment();
                    $paymentMethod->postProcessing($order, $payment, $params);

                    if ($order) 
					{
                        $this->getCheckoutSession()->setLastOrderId($order->getId())
                            ->setLastRealOrderId($order->getIncrementId())
                            ->setLastOrderStatus($order->getStatus());
                    }

                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
                }

                return $this->getResponse()->setRedirect($returnUrl);
            }
            //failed
            else
            {
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
                return $this->getResponse()->setRedirect($returnUrl);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }

       // $this->getResponse()->setRedirect($returnUrl);

    }

}
