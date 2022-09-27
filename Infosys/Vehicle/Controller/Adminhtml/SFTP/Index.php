<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Controller\Adminhtml\SFTP;

use Magento\Framework\Controller\Result\JsonFactory;
use Infosys\Vehicle\Model\SftpConnection;
use Infosys\Vehicle\Model\Scheduler;

//SFTP connection
class Index extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SftpConnection
     */
    protected $SftpConnection;

    /**
     * @var Scheduler
     */
    protected $Scheduler;

    /**
     * Constuctor function
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SftpConnection $SftpConnection
     * @param Scheduler $Scheduler
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        SftpConnection $SftpConnection,
        Scheduler $Scheduler
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->SftpConnection = $SftpConnection;
        $this->Scheduler = $Scheduler;
    }

    /**
     * Test connection
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        //check connection
        $connection = $this->SftpConnection->connection();
    
        if ($connection==true) {
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
        return $this->_authorization->isAllowed('Infosys_Vehicle::vehicle');
    }
}
