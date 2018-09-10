<?php


namespace Payswitch\Theteller\Model;

use Magento\Quote\Model\Quote\Payment;

class Checkout extends \Magento\Payment\Model\Method\AbstractMethod
{

    const CODE = 'theteller_payment';
    protected $_code = self::CODE;
    protected $_isGateway = false;
    protected $_isOffline = false;
    protected $_canRefund = true;
    protected $_isInitializeNeeded = false;
    protected $helper;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array(
        'KES', 'USD','GHS'
    );

    protected $_formBlockType = 'Payswitch\Theteller\Block\Form\Checkout';
    protected $_infoBlockType = 'Payswitch\Theteller\Block\Info\Checkout';

    protected $httpClientFactory;
    protected $orderSender;

    protected $_curl;
    public $ttid;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Payswitch\Theteller\Helper\Checkout $helper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
    }



    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount
                || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function buildCheckoutRequest($quote)
    {



        $billing_address = $quote->getBillingAddress();

        //Convert amount to minor float..
        $amount = round($quote->getGrandTotal(),2);

        if(is_float($amount) || is_double($amount)) {
            $number = $amount * 100;
            $zeros = 12 - strlen($number);
            $padding = '';
            //Log::info('The number of zeros to use is '.$zeros);
            for($i=0; $i<$zeros; $i++) {
                $padding .= '0';
            }
            //Log::info('Padding is '.$padding);
            $minor = $padding.$number;
        }elseif (strlen($amount)==12) {
            //Received an actual minor unit
            $minor = $amount;
        }

        $rand = substr(rand(), 0, 3);



        $data = array(
            "merchant_id" => $this->getConfigData('vendor_id'),
            "transaction_id" => $rand.$quote->getReservedOrderId(),
            "desc" => "Payment to  Merchant",
            "amount" => $minor,
            "email"=> $quote->getCustomerEmail(),
            "redirect_url" => $this->getConfigData('checkout_redirect')
            
        );

      
        $json_data = json_encode($data);

;
        $url = "https://test.theteller.net/checkout/initiate";
// Initialization of the request
        $curl = curl_init();

// Definition of request headers
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "json",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic dGVzdHVzZXI6yyyTVRrMU5qUXlNVFE0TjNSbGMzUjFjMlZ5VkdoMUxVWmxZaUF4TmkweU1ERTU=",
                "cache-control: no-cache",
                "content-type: application/json; charset=UTF-8",

            ),
            CURLOPT_POSTFIELDS => $json_data,
        ));

// Send request and show response
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "API Error #:" . $err;
        } else {

            $response = json_decode($response, true);

            //print_r($response) ;
            return $response;
        }

    }



    public function postProcessing(\Magento\Sales\Model\Order $order, \Magento\Framework\DataObject $payment, $response) {

        $transaction_id = $response['transaction_id'];
        $order_id = substr($transaction_id, 3);

        // Update payment details
        $payment->setTransactionId($order_id);
        $payment->setIsTransactionClosed(0);
        $payment->setTransactionAdditionalInfo('ipay_order_number', $order_id);
        $payment->setAdditionalInformation('ipay_order_number', $order_id);
        $payment->setAdditionalInformation('ipay_order_status', 'approved');
        $payment->place();



        // Update order status
        $order->setStatus($this->getOrderStatus());
        $order->setExtOrderId($order_id);
        $order->save();

        // Send email confirmation
        $this->orderSender->send($order);
    }



    public function redirect_checkout($token)
    {

        $checkout_url = "http://23.239.22.186/ecom.php?token=".$token;
        return $checkout_url;
    }

    public function getRedirectUrl()
    {
        $url = $this->helper->getUrl($this->getConfigData('redirect_url'));
        return $url;
    }

    public function getReturnUrl()
    {
        $url = $this->helper->getUrl($this->getConfigData('return_url'));
        return $url;
    }

    public function getCancelUrl()
    {
        $url = $this->helper->getUrl($this->getConfigData('cancel_url'));
        return $url;
    }

    public function getOrderStatus()
    {
        $value = $this->getConfigData('order_status');
        return $value;
    }

    public function getVendorID()
    {
        return $this->getConfigData('vendor_id');
    }

}
