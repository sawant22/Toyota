<?php
/**
 * @package     Infosys/CreateCategory
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\CreateCategory\Setup\Patch\Data;
  
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class to Create Categories
 */
class CreateCategory implements DataPatchInterface
{
     /**
     * Delimiter in category path.
     */
    const DELIMITER_CATEGORY = '/';
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryColFactory;

    /**
     * Categories text-path to ID hash.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * Categories id to object cache.
     *
     * @var array
     */
    protected $categoriesCache = [];

    /**
     * Instance of catalog category factory.
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Failed categories during creation
     *
     * @var array
     * @since 100.1.0
     */
    protected $failedCategories = [];

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * Constructor function
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->categoryColFactory = $categoryColFactory;
        $this->categoryFactory = $categoryFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->initCategories();
    }
    
    /**
     * Patch to create categories
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $category = [
            'TOYOTA/Parts/Body & Interior',
            'TOYOTA/Parts/Engine',
            'TOYOTA/Parts/Brakes & Suspension',
            'TOYOTA/Parts/Electrical',
            'TOYOTA/Parts/Exhaust',
            'TOYOTA/Parts/Fuel System',
            'TOYOTA/Parts/Heating & Air Conditioning',
            'TOYOTA/Parts/Lighting',
            'TOYOTA/Parts/Maintenance',
            'TOYOTA/Parts/Tools',
            'TOYOTA/Parts/Transmission & Driveline',
            'TOYOTA/Parts/Wheel Components',
            'TOYOTA/Accessories/Performance/Engine Accessories/Air Filter',
            'TOYOTA/Accessories/Performance/Engine Accessories/Engine Cover',
            'TOYOTA/Accessories/Performance/Engine Accessories/Fuel Cap',
            'TOYOTA/Accessories/Performance/Engine Accessories/Head Gasket',
            'TOYOTA/Accessories/Performance/Engine Accessories/Oil Cap',
            'TOYOTA/Accessories/Performance/Engine Accessories/Oil Filter',
            'TOYOTA/Accessories/Performance/Engine Accessories/Plug Wires',
            'TOYOTA/Accessories/Performance/Engine Accessories/Radiator Cap',
            'TOYOTA/Accessories/Performance/Performance Drivetrain/Clutch',
            'TOYOTA/Accessories/Performance/Performance Drivetrain/Limited Slip Differential',
            'TOYOTA/Accessories/Performance/Performance Drivetrain/Quickshifter',
            'TOYOTA/Accessories/Performance/Performance Drivetrain/Transmission Cooler',
            'TOYOTA/Accessories/Performance/Performance Engine/Air Intake System',
            'TOYOTA/Accessories/Performance/Performance Engine/Exhaust',
            'TOYOTA/Accessories/Performance/Performance Engine/Gauge',
            'TOYOTA/Accessories/Performance/Performance Engine/Shift Light',
            'TOYOTA/Accessories/Performance/Performance Engine/Supercharger',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Brake Pads',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Brakes',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Chassis Brace',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Coilovers',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Jounce Bumper',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Performance Springs',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Shocks and Struts',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Stabilizer Bar',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Strut Tie Brace',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Suspension Kit',
            'TOYOTA/Accessories/Performance/Performance Suspension-Chassis/Sway Bar',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Body Side Moldings',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Brush Guard',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Car Cover',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Door Cup Film',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Door Edge Film',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Door Edge Guard',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Front Bumper Applique',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Front End Mask',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Front Skid Plate',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Hood Protector',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Mudguards',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Paint Protection Film',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Rear Bumper Applique',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Rear Skid Plate',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Skid Plate',
            'TOYOTA/Accessories/Exterior/Body and Paint Protection/Touch Up Paint',
            'TOYOTA/Accessories/Exterior/Exterior Products/Accent Lighting',
            'TOYOTA/Accessories/Exterior/Exterior Products/Activity Mount',
            'TOYOTA/Accessories/Exterior/Exterior Products/Bike Rack',
            'TOYOTA/Accessories/Exterior/Exterior Products/Bumper',
            'TOYOTA/Accessories/Exterior/Exterior Products/Cable Lock',
            'TOYOTA/Accessories/Exterior/Exterior Products/Camera Mount',
            'TOYOTA/Accessories/Exterior/Exterior Products/Cargo Bag',
            'TOYOTA/Accessories/Exterior/Exterior Products/Cat-shield',
            'TOYOTA/Accessories/Exterior/Exterior Products/Cross Bars',
            'TOYOTA/Accessories/Exterior/Exterior Products/Door Steps',
            'TOYOTA/Accessories/Exterior/Exterior Products/Duffel Bag',
            'TOYOTA/Accessories/Exterior/Exterior Products/Fog Lights',
            'TOYOTA/Accessories/Exterior/Exterior Products/Garnish',
            'TOYOTA/Accessories/Exterior/Exterior Products/Headlight Assembly',
            'TOYOTA/Accessories/Exterior/Exterior Products/Hitch Utility',
            'TOYOTA/Accessories/Exterior/Exterior Products/Ladder',
            'TOYOTA/Accessories/Exterior/Exterior Products/License Plate Bracket',
            'TOYOTA/Accessories/Exterior/Exterior Products/License Plate Frame',
            'TOYOTA/Accessories/Exterior/Exterior Products/Light Bar',
            'TOYOTA/Accessories/Exterior/Exterior Products/Off Road Lights',
            'TOYOTA/Accessories/Exterior/Exterior Products/Ratchet Straps',
            'TOYOTA/Accessories/Exterior/Exterior Products/Rear Puddle Lamp',
            'TOYOTA/Accessories/Exterior/Exterior Products/Rock Rails',
            'TOYOTA/Accessories/Exterior/Exterior Products/Roof Cargo Basket',
            'TOYOTA/Accessories/Exterior/Exterior Products/Roof Pad',
            'TOYOTA/Accessories/Exterior/Exterior Products/Roof Rack',
            'TOYOTA/Accessories/Exterior/Exterior Products/Roof Rails',
            'TOYOTA/Accessories/Exterior/Exterior Products/Roof Utility',
            'TOYOTA/Accessories/Exterior/Exterior Products/Running Boards',
            'TOYOTA/Accessories/Exterior/Exterior Products/Side Puddle Lamp',
            'TOYOTA/Accessories/Exterior/Exterior Products/Ski Rack',
            'TOYOTA/Accessories/Exterior/Exterior Products/Tailgate Seat',
            'TOYOTA/Accessories/Exterior/Exterior Products/Top Carrier',
            'TOYOTA/Accessories/Exterior/Exterior Products/Tube Steps',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Body Kit',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Exhaust Tip',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Exterior Applique',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Exterior Emblem',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Fender Flares',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Fender Vent Insert',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Front Grille',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Graphics',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Lower Rocker Panel',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Mirror Caps',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Rear Garnish',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Rear Spoiler',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Rear Taillight Lens',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Rear Wind Deflector',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Rear Window Spoiler',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Spare Tire Cover',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Splitter',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Sport Bumper Trim',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Sunroof Wind Deflector',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Taillight Garnish',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Trunk Garnish',
            'TOYOTA/Accessories/Exterior/Exterior Styling/Window Deflector',
            'TOYOTA/Accessories/Exterior/Towing/Ball Mount',
            'TOYOTA/Accessories/Exterior/Towing/Tow Hitch',
            'TOYOTA/Accessories/Exterior/Towing/Tow Hook',
            'TOYOTA/Accessories/Exterior/Towing/Towing Wire Harnesses And Adapters',
            'TOYOTA/Accessories/Exterior/Towing/Trailer Ball',
            'TOYOTA/Accessories/Exterior/Towing/Trailer Ball Lock',
            'TOYOTA/Accessories/Exterior/Towing/Trailer Brake Controller',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Air Mattress',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Cargo Divider',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Cleats',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Extender',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Lighting',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Liner',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Mat',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Net',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Net with Tarp',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Rack',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Rail',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Rugs',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Step',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Storage Box-Swing out',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed Tent',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bed-Mount Tire Carrier',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Bike Rack',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Camper Shell',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Cargo Storage Box and Cooler Box',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/D-Rings',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Deck Rail Kit',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Mini-Tie Down',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Rear Step Bumper',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Side Storage Box',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Storage Box',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Tailgate Lock',
            'TOYOTA/Accessories/Exterior/Truck Bed Products/Tonneau Cover',
            'TOYOTA/Accessories/Exterior/Wheels/Center Cap',
            'TOYOTA/Accessories/Exterior/Wheels/Lug Nuts',
            'TOYOTA/Accessories/Exterior/Wheels/Spare Tire Lock',
            'TOYOTA/Accessories/Exterior/Wheels/Tire',
            'TOYOTA/Accessories/Exterior/Wheels/Valve',
            'TOYOTA/Accessories/Exterior/Wheels/Wheel Covers',
            'TOYOTA/Accessories/Exterior/Wheels/Wheel Inserts',
            'TOYOTA/Accessories/Exterior/Wheels/Wheel Locks',
            'TOYOTA/Accessories/Exterior/Wheels/Wheels',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Audio-Multimedia Cable',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Base Audio Headunit',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Extension Box',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Hands Free System',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Historical Audio',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Interface kit for iPod',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Navigation Headunit',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Navigation Upgrade Kit',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Portable Navigation System',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Premium Audio Headunit',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Rear Seat Entertainment',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Satellite Radio',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Subwoofer',
            'TOYOTA/Accessories/Interior/Audio Entertainment & Navigation/Wireless Headphones',
            'TOYOTA/Accessories/Interior/Vehicle Security/Security Box',
            'TOYOTA/Accessories/Interior/Vehicle Security/Security System',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Bar',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Cover',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Divider',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Hooks',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Net',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Organizer',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Storage Box',
            'TOYOTA/Accessories/Interior/Cargo Management/Cargo Tote',
            'TOYOTA/Accessories/Interior/Cargo Management/Hard Organizer',
            'TOYOTA/Accessories/Interior/Cargo Management/Seat Back Storage',
            'TOYOTA/Accessories/Interior/Cargo Management/Soft Organizer',
            'TOYOTA/Accessories/Interior/Driver Convenience/Armrest',
            'TOYOTA/Accessories/Interior/Driver Convenience/Auto-Dimming Mirror',
            'TOYOTA/Accessories/Interior/Driver Convenience/Back-up Camera',
            'TOYOTA/Accessories/Interior/Driver Convenience/Center Console Box',
            'TOYOTA/Accessories/Interior/Driver Convenience/Center Console Tray',
            'TOYOTA/Accessories/Interior/Driver Convenience/Charge Cable',
            'TOYOTA/Accessories/Interior/Driver Convenience/Coin Holder',
            'TOYOTA/Accessories/Interior/Driver Convenience/Coin Holder-Ashtray Cup',
            'TOYOTA/Accessories/Interior/Driver Convenience/Cruise Control',
            'TOYOTA/Accessories/Interior/Driver Convenience/Digital Clock',
            'TOYOTA/Accessories/Interior/Driver Convenience/EV Charger',
            'TOYOTA/Accessories/Interior/Driver Convenience/Emergency Assistance Kit',
            'TOYOTA/Accessories/Interior/Driver Convenience/Fire Extinguisher',
            'TOYOTA/Accessories/Interior/Driver Convenience/First Aid Kit',
            'TOYOTA/Accessories/Interior/Driver Convenience/Home-Link',
            'TOYOTA/Accessories/Interior/Driver Convenience/Key Finder',
            'TOYOTA/Accessories/Interior/Driver Convenience/Key Glove',
            'TOYOTA/Accessories/Interior/Driver Convenience/Kick Sensor',
            'TOYOTA/Accessories/Interior/Driver Convenience/Leather Care Kit',
            'TOYOTA/Accessories/Interior/Driver Convenience/Onboard Tire Inflator',
            'TOYOTA/Accessories/Interior/Driver Convenience/Overhead Console',
            'TOYOTA/Accessories/Interior/Driver Convenience/Owner Portfolio',
            'TOYOTA/Accessories/Interior/Driver Convenience/Power Port',
            'TOYOTA/Accessories/Interior/Driver Convenience/Remote Engine Starter',
            'TOYOTA/Accessories/Interior/Driver Convenience/Road-side Assistance Kit',
            'TOYOTA/Accessories/Interior/Driver Convenience/Universal Mount',
            'TOYOTA/Accessories/Interior/Driver Convenience/Wind Screen',
            'TOYOTA/Accessories/Interior/Driver Convenience/Wireless Charger',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/All Weather Cargo Mat',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/All Weather Floor Liners',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/All Weather Floor Mats',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/All Weather Trunk Mat',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/Cargo Liner',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/Cargo Tray',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/Carpet Cargo Mat',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/Carpet Cargo Mat With Storage',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/Carpet Floor Mats',
            'TOYOTA/Accessories/Interior/Floor Mats & Interior Protection/Carpet Trunk Mat',
            'TOYOTA/Accessories/Interior/Interior Styling/Door Sill Protectors',
            'TOYOTA/Accessories/Interior/Interior Styling/Illuminated Cargo Sills',
            'TOYOTA/Accessories/Interior/Interior Styling/Illuminated Cup Holders',
            'TOYOTA/Accessories/Interior/Interior Styling/Illuminated Door Sills',
            'TOYOTA/Accessories/Interior/Interior Styling/Illuminated Trunk Sill',
            'TOYOTA/Accessories/Interior/Interior Styling/Interior Applique',
            'TOYOTA/Accessories/Interior/Interior Styling/Interior Emblem',
            'TOYOTA/Accessories/Interior/Interior Styling/Interior Light Kit',
            'TOYOTA/Accessories/Interior/Interior Styling/Interior Ornamentation',
            'TOYOTA/Accessories/Interior/Interior Styling/Seat Cover',
            'TOYOTA/Accessories/Interior/Interior Styling/Shift Knob',
            'TOYOTA/Accessories/Interior/Interior Styling/Sport Pedals',
            'TOYOTA/Accessories/Interior/Interior Styling/Steering Wheel',
            'TOYOTA/Accessories/Interior/Video/Dashcam',
            'TOYOTA/Accessories/Interior/Interior Products/Cargo Management Cooler',
            'TOYOTA/Accessories/Interior/Interior Products/Console Safe',
            'TOYOTA/Accessories/Interior/Interior Products/Divider',
            'TOYOTA/Accessories/Interior/Interior Products/Interior Bike Rack',
            'TOYOTA/Accessories/Interior/Interior Products/LED Bulb',
            'TOYOTA/Accessories/Interior/Interior Products/Push Start',
            'TOYOTA/Accessories/Interior/Interior Products/Screen Protector',
            'TOYOTA/Accessories/Interior/Interior Products/Seatback',
            'TOYOTA/Accessories/Interior/Interior Products/Universal Clothes Hanger',
            'TOYOTA/Accessories/Interior/Interior Products/Vacuum'
        ];
        foreach ($category as $categoryPath) {
            $this->upsertCategory($categoryPath);
        }
        $this->moduleDataSetup->endSetup();
    }
     /**
      * Initialize categories
      *
      * @return $this
      */
    protected function initCategories()
    {
        if (empty($this->categories)) {
            $collection = $this->categoryColFactory->create();
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            foreach ($collection as $category) {
                $structure = explode(self::DELIMITER_CATEGORY, $category->getPath());
                $pathSize = count($structure);

                $this->categoriesCache[$category->getId()] = $category;
                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $name = $collection->getItemById((int)$structure[$i])->getName();
                        $path[] = $this->quoteDelimiter($name);
                    }
                    /** @var string $index */
                    $index = $this->standardizeString(
                        implode(self::DELIMITER_CATEGORY, $path)
                    );
                    $this->categories[$index] = $category->getId();
                }
            }
        }
        return $this;
    }

    /**
     * Creates a category.
     *
     * @param string $name
     * @param int $parentId
     * @return int
     */
    protected function createCategory($name, $parentId)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        try {
            $category = $this->categoryFactory->create();
            if (!($parentCategory = $this->getCategoryById($parentId))) {
                $parentCategory = $this->categoryFactory->create()->load($parentId);
            }
            $category->setPath($parentCategory->getPath());
            $category->setParentId($parentId);
            $category->setName($this->unquoteDelimiter($name));
            $category->setIsActive(true);
            $category->setIncludeInMenu(true);
            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            $category->save();
            $this->categoriesCache[$category->getId()] = $category;
            return $category->getId();
        } catch (\Exception $e) {
            
        }
    }

    /**
     * Returns ID of category by string path creating nonexistent ones.
     *
     * @param string $categoryPath
     * @return int
     */
    protected function upsertCategory($categoryPath)
    {
        /** @var string $index */
        $index = $this->standardizeString($categoryPath);

        if (!isset($this->categories[$index])) {
            $pathParts = preg_split('~(?<!\\\)' . preg_quote(self::DELIMITER_CATEGORY, '~') . '~', $categoryPath);
            $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $path = '';

            foreach ($pathParts as $pathPart) {
                $path .= $this->standardizeString($pathPart);
                if (!isset($this->categories[$path])) {
                    $this->categories[$path] = $this->createCategory($pathPart, $parentId);
                }
                $parentId = $this->categories[$path];
                $path .= self::DELIMITER_CATEGORY;
            }
        }
        return $this->categories[$index];
    }
    /**
     * Get category by Id
     *
     * @param int $categoryId
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCategoryById($categoryId)
    {
        return $this->categoriesCache[$categoryId] ?? null;
    }

    /**
     * Standardize a string.
     * For now it performs only a lowercase action, this method is here to include more complex checks in the future
     * if needed.
     *
     * @param string $string
     * @return string
     */
    private function standardizeString($string)
    {
        return mb_strtolower($string);
    }

    /**
     * Quoting delimiter character in string.
     *
     * @param string $string
     * @return string
     */
    private function quoteDelimiter($string)
    {
        return str_replace(self::DELIMITER_CATEGORY, '\\' . self::DELIMITER_CATEGORY, $string);
    }

    /**
     * Remove quoting delimiter in string.
     *
     * @param string $string
     * @return string
     */
    private function unquoteDelimiter($string)
    {
        return str_replace('\\' . self::DELIMITER_CATEGORY, self::DELIMITER_CATEGORY, $string);
    }

    /**
     * Get Aliases
     *
     * @return void
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return void
     */
    public static function getDependencies()
    {
        return [];
    }
}
