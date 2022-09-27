<?php
/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\AdminRole\Plugin\Model\ResourceModel;

use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollection;

class Collections
{
    /**
     * @var UserCollection
     */
    protected $userCollection;

    /**
     * constructor function
     *
     * @param UserCollection $userCollection
     */
    public function __construct(UserCollection $userCollection)
    {
        $this->userCollection = $userCollection;
    }
    /**
     * Overriding the method to filter user collection based on webiste
     *
     * @param \Magento\AdminGws\Model\ResourceModel\Collections $subject
     * @param  $result
     * @param  $isAll
     * @param  $allowedWebsites
     * @param  $allowedStoreGroups
     * @return array
     */
    public function afterGetUsersOutsideLimitedScope(
        \Magento\AdminGws\Model\ResourceModel\Collections $subject,
        $result,
        $isAll,
        $allowedWebsites,
        $allowedStoreGroups
    ) {
        if (!$isAll) {
            $collection = $this->userCollection->create();
            $collection->addFieldToFilter(
                ['website_ids', 'website_ids'],
                [
                    [['null' => true]],
                    [['nfinset' => $allowedWebsites]]
                ]
            );
            return $collection->getAllIds();
        }
        return $result;
    }
}
