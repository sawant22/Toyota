<?php
/**
 * @package     Infosys/CreateWebsite
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\CreateWebsite\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Infosys\CreateWebsite\Api\TRDRepositoryInterface;
use Infosys\CreateWebsite\Model\TRDFactory;

/**
 *
 */
class Save extends Action implements HttpPostActionInterface
{

    /**
     * @var TRDRepositoryInterface
     */
    protected $trdRepository;

    /**
     * @var TRDFactory
     */
    protected $trdFactory;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        TRDRepositoryInterface $trdRepository,
        TRDFactory $trdFactory
    ) {
        $this->trdRepository = $trdRepository;
        $this->trdFactory = $trdFactory;
        parent::__construct($context);
    }

    /**
     * Region save action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {

                if (isset($data['id'])) {
                    $model = $this->trdRepository->getById($data['id']);
                    $model->setRegionCode($data['region_code']);
                    $model->setRegionLabel($data['region_label']);
                    $this->trdRepository->save($model);
                }else {
                    $newTRD = $this->trdFactory->create();
                    $newTRD->setRegionCode($data['region_code']);
                    $newTRD->setRegionLabel($data['region_label']);
                    $this->trdRepository->save($newTRD);
                }

                $this->messageManager->addSuccessMessage(__('You saved the region.'));

                $this->_redirect('trd/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('trd/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('trd/*/create');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the region data. Please review the error log.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                if (isset($data['id'])) {
                    $this->_redirect('trd/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }else{
                    $this->_redirect('trd/*/create');
                }
                return;
            }
        }
        $this->_redirect('trd/*/');
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Infosys_CreateWebsite::trd_save');
    }
}
