<?php
/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\SalesReport\Controller\Adminhtml\Dealerrank;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Infosys\SalesReport\Model\DealerSalesRank;
use Infosys\SalesReport\Model\SalesReportStoreConfig;


/**
 * Class to export grid into csv
 */
class ExportToCsv extends Action
{
    protected FileFactory $fileFactory;

    protected Csv $csvProcessor;

    protected DirectoryList $directoryList;

    protected DealerSalesRank $dealerSalesRank;

    protected SalesReportStoreConfig $configData;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param DealerSalesRank $dealerSalesRank
     * @param SalesReportStoreConfig $configData
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        DealerSalesRank $dealerSalesRank,
        SalesReportStoreConfig $configData
    )
    {
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->dealerSalesRank = $dealerSalesRank;
        $this->configData = $configData;
        parent::__construct($context);
    }
 
    /**
     * Export dealer rank report grid data into CSV format
     *
     * @return void
     */
    public function execute()
    {
        $fileName = 'DealerSalesRank.csv';
        $filePath = $this->directoryList->getPath(DirectoryList::VAR_DIR). "/" . $fileName;
     
        $data = $this->getDealerRankData();
        $this->csvProcessor
                ->setDelimiter(',')
                ->setEnclosure('"')
                ->saveData(
                $filePath,
                $data
            );
    
        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
    }

    /**
     * Get dealer ranking data as per applied filter
     *
     * @return array
     */
    protected function getDealerRankData() : array
    {
        $result = [];
        $rankData = $this->dealerSalesRank->calculateDealerRank();
        $result[] = [
            'Metric',
            'Your Performance',
            'Other Performance - Low',
            'Other Performance - Medium',
            'Other Performance - High',
            'Rank'
        ];
        if(isset($rankData['data']))
        {
            $config = $this->configData->getDealerRankMetrics();
            foreach ($rankData['data'] as $data) {
               if($config[$data['metric']][1] == 1)
               {
                    $data['your_performance'] = strip_tags($data['your_performance']);
                    $data['low_dealer'] = strip_tags($data['low_dealer']);
                    $data['medium_dealer'] = strip_tags($data['medium_dealer']);
                    $data['high_dealer'] = strip_tags($data['high_dealer']);
               }
                $result[] = [
                    $data['metric_label'],
                    $data['your_performance'],
                    $data['low_dealer'],
                    $data['medium_dealer'],
                    $data['high_dealer'],
                    ' '.$data['dealer_rank'],
                ];
            }
        }
        return $result;
    }
}
