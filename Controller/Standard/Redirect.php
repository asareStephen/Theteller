<?php


namespace Payswitch\Theteller\Controller\Standard;

class Redirect extends \Payswitch\Theteller\Controller\Checkout
{


    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_cancelPayment();
            $this->_checkoutSession->restoreQuote();
            $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
            );
        }
        
        $quote = $this->getQuote();
		
        $email = $this->getRequest()->getParam('email');
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getCheckoutSession()->loadCustomerQuote();
            $quote->updateCustomerData($this->getQuote()->getCustomer());
        }
        else
        {
            $quote->setCustomerEmail($email);
        }
        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);


        $params = [];
        $params = $this->getPaymentMethod()->buildCheckoutRequest($quote);
        $status = $params['status'];
        $token = $params['token'];
        $checkout_url = $params['checkout_url'];

        //var_dump($token);

        if($status == "success")
        {   
            
            $params['url'] = $this->getPaymentMethod()->redirect_checkout($token);
            $request_body =  $this->resultJsonFactory->create()->setData($params);
             return $request_body;
             

            
        }

    }

   

}
