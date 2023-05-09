<?php
namespace Embitel\CancelOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Razorpay\Magento\Model\PaymentMethod;

class Refund implements ObserverInterface
{
    /**
     * Store key
     */
    const STORE = 'store';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AfterPlaceOrderRepayEmailProcessor
     */
    private $emailProcessor;

    /**
     * StatusAssignObserver constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param AfterPlaceOrderRepayEmailProcessor $emailProcessor
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Razorpay\Magento\Model\Config $config,
    ) {
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
    }

    public function execute($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethod() == 'razorpay') {
            $orderId = $order->getIncrementId();
            $amountToRefund = $order->getTotalDue(); 
                $key_id = 'rzp_test_Qiz19dKbMAYUjz'; 
                $secret = 'llxmo88yqpYvosRg1h15kI1e'; 
                // $api = new Api($key_id, $secret);
                $razorpayId = $order->getPayment()->getAdditionalInformation('rzp_order_id');
                echo $razorpayId;
                die("yes");
                $url = "https://api.razorpay.com/v1/payments/$razorpayId";
                $this->_curl->post($url, []);
                $refund = $razorpayClient->refund->create([
                    'payment_id' => $order->getPayment()->getAdditionalInformation('razorpay_payment_id'),
                    'amount' => $amountToRefund * 100,
                ]);
                if ($refund->id) {
                    $order->setState(Mage_Sales_Model_Order::STATE_CLOSED, true)->setStatus('Refunded');
                    $order->addStatusHistoryComment('Order refunded successfully.')->setIsCustomerNotified(true);
                    $order->save();
                } else {
                    echo 'Not a valid Razorpay ID';
                }
            }
        }
    }
