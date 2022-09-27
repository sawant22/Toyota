<?php

/**
 * @package   Infosys/EPCconnect
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\EPCconnect\Controller\Adminhtml\EPCsync;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Magento\Framework\Controller\Result\JsonFactory;

//SFTP connection
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Infosys\EPCconnect\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constuctor function
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Infosys\EPCconnect\Helper\Data $helperData
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Infosys\EPCconnect\Helper\Data $helperData,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->_dir = $dir;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Test connection
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $hostname = $this->helperData->getConfig('epcconnect/general/hostname');
        $username = $this->helperData->getConfig('epcconnect/general/username');
        $key_path = $this->helperData->getConfig('epcconnect/general/key_path');

        $sshkey = $key_path;
        $sftp = new SFTP($hostname);
        $Key = new RSA();
        $Key->loadKey($sshkey);

        if ($sftp->login($username, $Key)) {
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
        return $this->_authorization->isAllowed('Infosys_EPCconnect::epc');
    }
}
