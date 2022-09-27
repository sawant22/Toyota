<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Api;

interface SftpInterface
{
    /**
     * Establish connection
     *
     * @return bool
     */
    public function connection();

    /**
     * Get file from SFTP server
     *
     * @param mixed $sftp
     * @return array
     */
    public function getFile($sftp);
}
