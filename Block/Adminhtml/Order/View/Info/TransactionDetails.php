<?php

namespace Payswitch\Theteller\Block\Adminhtml\Order\View\Info;

class TransactionDetails extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_template = 'Payswitch_Theteller::order/view/info/transaction_details.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return (
            $this->getPayment()->getMethod() === \Payswitch\Theteller\Model\Checkout::CODE ||
            $this->getPayment()->getMethod() === \Payswitch\Theteller\Model\Api::CODE
        ) ? parent::_toHtml() : '';
    }
}
