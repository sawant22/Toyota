<?php
/**
 * @package     Infosys/UpdateProductCount
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\UpdateProductCount\Plugin\Model\Adapter\Index;

class BuilderChange
{
    public function afterBuild(
        \Magento\Elasticsearch\Model\Adapter\Index\Builder $subject,
        $result
    ) {
            $result['max_result_window'] = 500000;
            return $result ;
    }
}
