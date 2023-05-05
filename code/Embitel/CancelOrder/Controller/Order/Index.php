<?php

namespace Embitel\CancelOrder\Controller\Order;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $_order;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        array $data = [])
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->_order = $order;
        return parent::__construct($context,$data);
    }

    public function execute()
    {
        /**
        *Changes by Swarna
        */
        // $orderItem = $this->orderItemRepository->get($orderId);
        // print_r ($orderItem);
        // die();
        //$razorpayId = $this->order->load(
        //$this->_curl->get('https://api.razorpay.com/v1/refunds/$razorpayId');
        //  $api = new Api($key_id, $secret);
        //	$api->refund->fetch($refundId);
        /**
        *End of changes
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $orderId = $this->getRequest()->getParam('orderid');
        $order = $this->_order->load($orderId);
        if($order->canCancel()){
            $order->cancel();
            $order->save();
        
            $this->messageManager->addSuccess(__('Order has been canceled successfully.'));
        } else {
            $this->messageManager->addError(__('Order cannot be canceled.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
