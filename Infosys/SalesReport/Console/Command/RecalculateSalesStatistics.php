<?php
/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Infosys\SalesReport\Model\DealerSalesStatisticsQueueFactory;
use Infosys\SalesReport\Logger\SalesReportLogger;

class RecalculateSalesStatistics extends Command
{

    const SALES_STATISTICS_QUEUE_TABLE = 'toyota_dealer_sales_statistics_queue';

    private const STORE_ID = 'store_id';

    private const START_DATE = 'start_date';

    private const END_DATE = 'end_date';

    protected $_dealerSalesStatisticsQueueFactory;

    protected StoreManagerInterface $storeManager;

    protected ResourceConnection $resource;

    protected SalesReportLogger $salesReportLogger;

    /**
     * Constructor function
     *
     * @param DealerSalesStatisticsQueueFactory $dealerSalesStatisticsQueueFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource,
     * @param SalesReportLogger $salesReportLogger
     */
    public function __construct(
        DealerSalesStatisticsQueueFactory $dealerSalesStatisticsQueueFactory,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        SalesReportLogger $salesReportLogger
    ) {
        $this->_dealerSalesStatisticsQueueFactory = $dealerSalesStatisticsQueueFactory;
        $this->storeManager = $storeManager;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->salesReportLogger = $salesReportLogger;
        parent::__construct();
    }

    /**
     * Cofigure method to get the LCI command inputs
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('infosyssales_statistics:recalculate');
        $this->setDescription('This command is used to recalculate the sales statistics for a given date range.');
        $this->addOption(
            self::STORE_ID,
            null,
            InputOption::VALUE_OPTIONAL,
            'Store ID. When Store ID is not mentioned, all Store IDs will be taken by default. For multiple Store IDs use format 1,2,3'
        );
        $this->addOption(
            self::START_DATE,
            null,
            InputOption::VALUE_REQUIRED,
            'Start Date in the format 2022-02-12 (year-month-date)'
        );
        $this->addOption(
            self::END_DATE,
            null,
            InputOption::VALUE_REQUIRED,
            'End Date in the format 2022-02-12 (year-month-date)'
        );

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;

        $store_id = $input->getOption(self::STORE_ID);
        $start_date = $input->getOption(self::START_DATE);
        $end_date = $input->getOption(self::END_DATE);

        //Input validation: start_date and end_date are mandatory
        if (!$start_date || !$end_date) {
            throw new LocalizedException(__('Please provide the start_date and end_date inputs'));
        }

        try {

            //Logging the input details
            $this->salesReportLogger->info("The provided input values are; Store Id : `" . $store_id . "` , Start Date : `" . $start_date . "` , End Date : `" . $end_date . "`");

            //Checking the start_date format
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $start_date)) {
                throw new LocalizedException(__('Wrong start_date input format given. Please provide in the format 2022-02-12 (year-month-date)'));
            }

            //Checking the end_date format
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $end_date)) {
                throw new LocalizedException(__('Wrong end_date input format given. Please provide in the format 2022-02-12 (year-month-date)'));
            }

            //Calculating the date range between the start_date and the end_date
            $dates_range = [];
            $current = strtotime($start_date);
            $last = strtotime($end_date);

            while ($current <= $last) {
                $dates_range[] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
            }

            //Verifying if the start_date is previous date from the end_date
            if (strtotime($start_date) > strtotime($end_date)) {
                throw new LocalizedException(__('The start_date should be a date prior to end_date'));
            }

            $all_store_ids = array_keys($this->storeManager->getStores());

            if ($store_id) {
                $store_id_arr = explode(',', $store_id);

                //Checking for the invalid store ids
                foreach ($store_id_arr as $store_id_val) {
                    $store_id_check = in_array($store_id_val, $all_store_ids);
                    if (!$store_id_check) {
                        throw new LocalizedException(__('The Store ID `' . $store_id_val . '` is invalid. Please provide a valid store ID'));
                    }
                }

                //Removing the duplicate store ids
                $store_id_arr = array_unique($store_id_arr);
            } else {
                $store_id_arr = $all_store_ids;
            }

            //Inserting the input values into the toyota_dealer_sales_statistics_queue table
            $insert_data_list = [];

            foreach ($store_id_arr as $store_id_val) {
                foreach ($dates_range as $dates_range_val) {
                    $insert_data['store_id'] = $store_id_val;
                    $insert_data['report_date'] = $dates_range_val;
                    $insert_data_list[] = $insert_data;
                }
            }

            if (count($insert_data_list) > 0) {
                $inserted_records =  $this->_connection->insertMultiple(self::SALES_STATISTICS_QUEUE_TABLE, $insert_data_list);

                if ($inserted_records) {
                    $output->writeln('<info>The records are inserted successfully for the provided range.');
                }
            }
        } catch (\Exception $e) {
            $this->salesReportLogger->error('Error in inserting data into sales statistics queue table from command' . $e);
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $exitCode = 1;
        }

        return $exitCode;
    }
}
