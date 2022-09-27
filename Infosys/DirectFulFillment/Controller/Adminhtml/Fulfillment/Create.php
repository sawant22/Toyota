<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright � 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Controller\Adminhtml\Fulfillment;

use Magento\Sales\Api\OrderRepositoryInterface;
use Xtento\OrderExport\Model\ProfileFactory;
use Xtento\OrderExport\Model\ExportFactory;
use Infosys\DirectFulFillment\Helper\DirectFulFillment;

class Create extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Infosys_DirectFulFillment::create';

    const PAGE_TITLE = 'Direct FulFillment';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var ProfileFactory
     */
    protected $profileFactory;
    /**
     * @var ExportFactory
     */
    protected $exportFactory;
    /**
     * @var DirectFulFillment
     */
    protected $directFulFillmentHelper;
    /**
     * @var timezone
     */
    protected $timezone;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;
    /**
     * Constructor function
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param ProfileFactory $profileFactory
     * @param ExportFactory $exportFactory
     * @param DirectFulFillment $directFulFillmentHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $timezone
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        ProfileFactory $profileFactory,
        ExportFactory $exportFactory,
        DirectFulFillment $directFulFillmentHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $timezone,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->_pageFactory = $pageFactory;
        $this->orderRepository = $orderRepository;
        $this->profileFactory = $profileFactory;
        $this->exportFactory = $exportFactory;
        $this->directFulFillmentHelper = $directFulFillmentHelper;
        $this->timezone = $timezone;
        $this->backendSession = $backendSession;
        return parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu(static::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__(static::PAGE_TITLE), __(static::PAGE_TITLE));
        $resultPage->getConfig()->getTitle()->prepend(__(static::PAGE_TITLE));

        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $directFulfillmentStatus = $this->getRequest()->getParam('direct_fulfillment_status');
        $itemFulFilmentStatus = [];
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getDirectFulfillmentEligibility()) {
                foreach ($directFulfillmentStatus as $itemDFStatus) {
                    foreach ($itemDFStatus as $itemId => $status) {
                        if ($item->getId() == $itemId) {
                            if ($status) {
                                $item->setDealerDirectFulfillmentStatus(1);
                                $item->setDirectFulfillmentStatus('Sent to Direct Fulfillment');
                                $item->save();
                            } else {
                                $item->setDealerDirectFulfillmentStatus(0);
                                $item->save();
                            }
                        }
                    }
                }
            }
            $itemFulFilmentStatus[] =  $item->getDealerDirectFulfillmentStatus();
        }
        if (count(array_unique($itemFulFilmentStatus)) > 1) {
            $order->setDirectFulfillmentSplit(1);
        }
        $order->setDirectFulfillmentOrderAcceptedAt($this->timezone->gmtDate());
        $this->orderRepository->save($order);
        $profileId =  $this->directFulFillmentHelper->getExportProfileId($order->getStoreId());
        $profile = $this->profileFactory->create()->load($profileId);
        $this->exportFactory->create()->setProfile($profile)->gridExport([$orderId]);
        if($this->backendSession->getDDOAError()) {
            foreach ($order->getAllVisibleItems() as $item) { 
                $item->setDealerDirectFulfillmentStatus(0);
                $item->setDirectFulfillmentStatus(NULL);
                $item->save();
            }
            $this->messageManager->addError("Something went wrong, Please check execution log for more details");
            $this->backendSession->unsDDOAError();
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        }
        $this->messageManager->addSuccessMessage(
            __('Order Item(s) have been sent to Direct Fulfillment')
        );
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Is the user allowed to view the page.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
