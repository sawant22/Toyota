<?php

namespace Infosys\CreateWebsite\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Infosys\CreateWebsite\Model\TRDRepository;

class Delete extends Action
{

    /**
     * @var TRDRepository
     */
    private $trdRepository;


    /**
     * Delete constructor.
     *
     * @param Context  $context
     * @param TRDRepository  $trdRepository
     */
    public function __construct(
        Context $context,
        TRDRepository $trdRepository
    ) {
        $this->trdRepository = $trdRepository;
        parent::__construct($context);
    }

    /**
     * Execute method.
     *
     * @return null
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $trdModel = $this->trdRepository->getById($id);
                $this->trdRepository->delete($trdModel);

                $this->messageManager->addSuccessMessage(
                    __('The region has been deleted.')
                );
                $this->_redirect('trd/*/');

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while deleting the region. Please review the log and try again.')
                );
                $this->_redirect(
                    '*/*/edit',
                    ['id' => $id]
                );

                return;
            }
        }
        $this->messageManager->addErrorMessage(
            __('Unable to find a region to delete.')
        );
        $this->_redirect('trd/*/');
    }
}
