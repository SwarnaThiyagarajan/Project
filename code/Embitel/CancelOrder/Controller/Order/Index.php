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
        \Magento\Sales\Model\Order $order
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_order = $order;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $orderId = $this->getRequest()->getParam('orderid');
        $order = $this->_order->load($orderId);
        if ($order->canCancel()) {
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
