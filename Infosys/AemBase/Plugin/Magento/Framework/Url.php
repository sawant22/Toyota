<?php declare(strict_types=1);

namespace Infosys\AemBase\Plugin\Magento\Framework;

use Infosys\AemBase\Model\AemBaseConfigProvider;
use Magento\Framework\App\RequestInterface;

class Url
{
    /** @var AemBaseConfigProvider */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Url constructor.
     * @param AemBaseConfigProvider $configProvider
     * @param RequestInterface $request
     */
    public function __construct(
        AemBaseConfigProvider $configProvider,
        RequestInterface $request
    ) {
        $this->configProvider = $configProvider;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Url $subject
     * @param $result
     * @return mixed
     */
    public function afterGetCurrentUrl(
        \Magento\Framework\Url $subject,
        $result
    ) {
        $host = $this->request->getHttpHost()."/";
        $magentoPath = $host.$this->configProvider->getMagentoPath();
        if (!strstr($result, $magentoPath)) {
            $result = str_replace($host, $magentoPath, $result);
        }
        return $result;
    }
}
