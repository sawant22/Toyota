<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Infosys\Vehicle\Logger\VehicleLogger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Glob;
use Magento\Catalog\Model\Product;
use Magento\Framework\File\Csv;

//Validate Imported Products
class ValidateProduct extends Command
{
    const DIRECTORY = 'folder';
    const FILES = 'files';
    const FILES_PATH = '/import/epc_schedule_import/';

    /**
     * @var VehicleLogger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var \Magento\Framework\Filesystem\Glob
     */
    protected $glob;

    /**
     * @var \Magento\Catalog\Model\Product $productCollection
     */
    protected $productCollection;

    /**
     * @var \Magento\Framework\File\Csv $csvParser
     */
    protected $csvParser;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param File $file
     * @param \Magento\Framework\Filesystem\Glob $glob
     * @param \Magento\Catalog\Model\Product $productCollection
     * @param \Magento\Framework\File\Csv $csvParser
     * @param VehicleLogger $logger
     *
     * @return void
     */
    public function __construct(
        DirectoryList $directoryList,
        File $file,
        \Magento\Framework\Filesystem\Glob $glob,
        \Magento\Catalog\Model\Product $productCollection,
        \Magento\Framework\File\Csv $csvParser,
        VehicleLogger $logger
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->glob = $glob;
        $this->productCollection = $productCollection;
        $this->csvParser = $csvParser;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Configure command options
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::FILES,
                null,
                InputOption::VALUE_OPTIONAL,
                'files'
            ),
            new InputOption(
                self::DIRECTORY,
                null,
                InputOption::VALUE_OPTIONAL,
                'folder'
            )
        ];

        $this->setName('products:validate')
            ->setDescription('Products validation command line')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //load all skus available in db
        $skus = $this->productCollection->getCollection()->getColumnValues('sku');
        
        //if folder path pass as input option
        if ($dir = $input->getOption(self::DIRECTORY)) {
            $importRootDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . $dir . "/";
            foreach ($this->glob->glob($importRootDir."*") as $file) {
                if ($this->file->isExists($file)) {
                    $this->csvParse($file, $skus, $output);
                }
            }
        }

        //if multiple files pass as input option
        if ($files = $input->getOption(self::FILES)) {
            $files_arr = explode(',', $files);
            if ($files_arr) {
                $importRootDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . self::FILES_PATH;
                foreach ($files_arr as $file) {
                    $file_path = $importRootDir . $file;
                    if ($this->file->isExists($file_path)) {
                        $this->csvParse($file_path, $skus, $output);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Parse CSV data to find missing SKUs
     *
     * @param string $file
     * @param array $skus
     * @param OutputInterface $output
     * @return void
     */
    protected function csvParse($file, $skus, $output)
    {
        $output->writeln("Validating ".$file);
        $missingSkus = [];
        $csvData = [];

        $this->csvParser->setDelimiter(',');
        $csvData = $this->csvParser->getData($file);

        //check if csv not empty
        if ($csvData) {
            foreach ($csvData as $lines => $line) {
                $sku = $line[0];
                if ($sku=='sku') {
                    continue;
                }

                if (!in_array($sku, $skus)) {
                    $missingSkus[] = $sku;
                }
            }
        }

        //log missing skus
        if ($missingSkus) {
            $missingSkus = json_encode($missingSkus);
            $output->writeln($missingSkus);
            $this->logger->info("Missing SKUs in " . $file . " " . $missingSkus);
        } else {
            $output->writeln("No missing SKUs");
        }
    }
}
