<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\Vehicle\Api\SftpInterface;

class SftpConnection implements SftpInterface
{
    /**
     * @var \Infosys\Vehicle\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /**
     * @var VehicleLogger
     */
    protected $logger;

    /**
     * Constructor function
     *
     * @param \Infosys\Vehicle\Helper\Data $helperData
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param VehicleLogger $logger
     */
    public function __construct(
        \Infosys\Vehicle\Helper\Data $helperData,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        VehicleLogger $logger
    ) {
        $this->helperData = $helperData;
        $this->_dir = $dir;
        $this->logger = $logger;
    }

    /**
     * Establish connection
     *
     * @return bool
     */
    public function connection()
    {
        $hostname = $this->helperData->getConfig('epc_config/general/hostname');
        $username = $this->helperData->getConfig('epc_config/general/username');
        $key_path = $this->helperData->getConfig('epc_config/general/key_path');

        $sshkey = $key_path;
        $sftp = new SFTP($hostname);
        $Key = new RSA();
        $Key->loadKey($sshkey);

        if (!$sftp->login($username, $Key)) {
            $this->logger->error('SFTP connection failed.');
            return false;
        }
        return $sftp;
    }

    /**
     * Get file from SFTP server
     *
     * @param mixed $sftp
     * @return array
     */
    public function getFile($sftp)
    {
        try {
            
            $sftp->chdir('outbound');
            $files = $sftp->rawlist();

        } catch (\Exception $e) {
            $this->logger->error('SFTP connection not working' . $e->getMessage());
        }
        return $files;
    }
}
