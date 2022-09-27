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
use Infosys\SalesReport\Model\DealerSalesRank;
use Magento\Framework\Filesystem;
use Infosys\SalesReport\Model\SalesReportStoreConfig;

/**
 * Class to export grid into xcel file
 */
class ExportToExcel extends Action
{
    const XML_REPORT_PATH = 'export';

    protected FileFactory $fileFactory;

    protected DealerSalesRank $dealerSalesRank;

    protected Filesystem $filesystem;
    
    protected SalesReportStoreConfig $configData;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param DealerSalesRank $dealerSalesRank
     * @param Filesystem $filesystem
     * @param SalesReportStoreConfig $configData
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        DealerSalesRank $dealerSalesRank,
        Filesystem $filesystem,
        SalesReportStoreConfig $configData
    ) {
        $this->fileFactory = $fileFactory;
        $this->dealerSalesRank = $dealerSalesRank;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->configData = $configData;
        parent::__construct($context);
    }

    /**
     * Export dealer rank report grid data into Excel XML format
     *
     * @return void
     */
    public function execute()
    {
        $file = 'DealerSalesRank.xml';
        $data = $this->getDealerRankData();
        $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));
        $this->directory->create(self::XML_REPORT_PATH);
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $convert->write($stream, $file);
        $stream->unlock();
        $stream->close();

        return $this->fileFactory->create(
            $file,
            [
                'type' => "filename",
                'value' => $file,
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
    protected function getDealerRankData(): array
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
        if (isset($rankData['data'])) {
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
                    $data['dealer_rank'],
                ];
            }
        }
        return $result;
    }
}
