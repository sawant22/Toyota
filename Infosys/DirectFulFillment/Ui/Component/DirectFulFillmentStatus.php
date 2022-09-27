<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\DirectFulFillment\Ui\Component;

use Magento\Ui\Component\Listing\Columns\Column;

class DirectFulFillmentStatus extends Column
{
    /**
     * Method to customize the columns data
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        parent::prepareDataSource($dataSource);

        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            $status = $item[$this->getData('name')];
            if ($status == 1) {
                $item[$this->getData('name')] = 'YES';
            } else {
                $item[$this->getData('name')] = 'NO';
            }
        }

        return $dataSource;
    }
}
