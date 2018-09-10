<?php

namespace Payswitch\Theteller\Controller\Standard;

class Cancel extends \Payswitch\Theteller\Controller\Checkout
{

    public function execute()
    {
        $this->_cancelPayment();
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->getCheckoutHelper()->getUrl('checkout')
        );
    }

}
