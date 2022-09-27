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
use Infosys\Vehicle\Model\Scheduler;

//Vehicle schedule tasks
class Vehicle extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Scheduler
     */
    protected $Scheduler;

    /**
     * Constuctor function
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Scheduler $Scheduler
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        Scheduler $Scheduler
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->Scheduler = $Scheduler;
    }

    /**
     * Run vehicle scheduled tasks manually
     */
    public function execute()
    {
        $this->Scheduler->syncScheduleFiles();
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Infosys_Vehicle::schedule_vehicle');
    }
}
