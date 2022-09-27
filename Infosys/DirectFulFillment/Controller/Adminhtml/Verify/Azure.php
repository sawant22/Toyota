<?php
declare(strict_types=1);

/**
 * @package   Infosys/DirectFulFillment
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Controller\Adminhtml\Verify;

use Magento\Framework\Controller\Result\JsonFactory;
use Infosys\DirectFulFillment\Helper\Data as DFHelper;
use Infosys\DirectFulFillment\Logger\DDOALogger;

/**
 * Validate Credentials To create AWS Access Token
 */
class Azure extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected DFHelper $dfHelper;

    protected DDOALogger $logger;


    /**
     * Constuctor function
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        DFHelper $dfHelper,
        DDOALogger $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dfHelper = $dfHelper;
        $this->logger = $logger;
    }

    /**
     * Test connection
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $accessToken = $this->dfHelper->getAccessToken();
        } catch (\Exception $e) {
            $this->logger->error($e);
            return $result->setData(['status' => $e->getMessage()]);
        }

        if ($accessToken) {
            return $result->setData(['status' => true]);
        } else {
            return $result->setData(['status' => false]);
        }
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Infosys_DirectFulFillment::configuration');
    }
}
