<?php

/**
 * @package     Infosys/DealerChanges
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerChanges\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Backend\Model\Auth\Session;

/**
 * Class to get check national website or dealer website
 */
class Data extends AbstractHelper
{
    /**
     * Constructor function
     *
     * @param Session $authSession
     */
    public function __construct(
        Session $authSession
    ) {
        $this->authSession = $authSession;
    }

    /**
     * Method to check national website or dealer website
     */
    public function isDealerLogin(): bool
    {
        $loginUserAccess = $this->authSession->getUser()->getData('all_website');
        if (!empty($loginUserAccess) && $loginUserAccess == 1) {
            return false;
        } else {
            return true;
        }
    }
}
