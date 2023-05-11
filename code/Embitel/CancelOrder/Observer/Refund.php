<?php
namespace Embitel\CancelOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Razorpay\Magento\Model\PaymentMethod;
use Razorpay\Magento\Model\Config;
use Razorpay\Api\Api;

class Refund implements ObserverInterface
{
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
        if ($order->getPayment()->getMethod() === 'razorpay') {
            $orderId = $order->getIncrementId();
            $amountToRefund = $order->getTotalDue();
            $key_id = $this->config->getConfigData(Config::KEY_PUBLIC_KEY);
            $secret = $this->config->getConfigData(Config::KEY_PRIVATE_KEY);
            $api = new Api($key_id, $secret);
            //code for fetching the Razorpay order ID
            $razorpayId = $order->getData('rzp_order_id');
            //code for fetching the Razorpay payment ID using Razorpay Order ID
            $orderDetail = $api->order->fetch($razorpayId);
            $paymentId = $orderDetail->payments()->items[0]->id;
            //Code for performing refund 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/payments/" . $paymentId . "/refund");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'amount' => $amountToRefund * 100
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode("$key_id:$secret")
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
        }
    }
}
