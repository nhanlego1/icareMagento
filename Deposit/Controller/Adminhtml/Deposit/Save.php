<?php
/**
 * Copyright © 2016 by Mobivi.
 * Created by Long Nguyen.
 * User: longnguyen
 * Date: 04/10/2016
 * Time: 14:55
 */

namespace Icare\Deposit\Controller\Adminhtml\Deposit;


use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;

class Save extends Action{
    const S3_BASE_PATH = '/deposit/payment';
    /**
     * @var \Magento\Backend\Model\Auth\Session $authSession
     */
    private $authSession = NULL;

    /**
     * @var Registry
     */
    private $_coreRegistry = null;

    /**
     * @var \Icare\Custom\Helper\S3Helper|null
     */
    private $s3Helper = null;

    private $uploaderFactory = null;
    private $fileSystem = null;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Registry $registry,
        Session $authSession,
        Filesystem $fileSystem,
        \Icare\Custom\Helper\S3Helper $s3Helper,
        UploaderFactory $uploaderFactory
    ) {
        $this->authSession = $authSession;
        $this->_coreRegistry = $registry;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->s3Helper = $s3Helper;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        /**@var \Icare\Deposit\Model\Payment $model**/
        $model = $this->_coreRegistry->registry('icare_deposit_payment');
        if(empty($model)){
            $model = ObjectManager::getInstance()->create('Icare\Deposit\Model\Payment');
        }


        $id = (int)$request->getParam('id');

        if ($id) {
            $model->load($id);
        }

        try {
            $data = $request->getParams();
            $model->setData($data);
            $this->_getSession()->setData('deposit_payment_model',$model);
            $user_id = $data['user_id'];
            /**@var \Icare\Deposit\Block\Adminhtml\View\Tabs\MakePayment $block **/
            $block = ObjectManager::getInstance()->get('Icare\Deposit\Block\Adminhtml\View\Tabs\MakePayment');
            $block->getTotalReceivale($user_id);
            if($data['transaction_amount']>$block->maxTotalTrans){
                $this->messageManager->addError(__('Transaction amount can not greater than total received value : '). number_format($block->maxTotalTrans));
                return $resultRedirect->setPath('*/*/view', ['user_id' => $user_id]);
            }
            if(isset($_FILES['attach_file']) && !empty($_FILES['attach_file']['name'])){
                $destinationPath = $this->getDestinationPath();
                try {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'attach_file'])
                        ->setAllowCreateFolders(true);
                        //->setAllowedExtensions($this->allowedExtensions)
                        //->addValidateCallback('validate', $this, 'validateFile');
                    $filename = md5(microtime() . rand(0,999)) .'.'.$uploader->getFileExtension();
                    if (!$result=$uploader->save($destinationPath,$filename)) {
                        throw new LocalizedException(
                            __('File cannot be saved to path: $1', $destinationPath)
                        );
                    }
                    $uploadedFile = $result['path'] .'/'. $result['file'];
                    if(file_exists($uploadedFile)){
                        $url = $this->s3Helper->uploadFile(self::S3_BASE_PATH.'/'.$filename, fopen($uploadedFile, 'rb'));
                        if($url){
                            unlink($uploadedFile);
                            $model->setData('attach_file',$url);
                        }
                    }
                    
                    // @todo
                    // process the uploaded file
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __($e->getMessage())
                    );
                }
            }
            $model->setData('created_by',$this->authSession->getUser()->getUserId());
            if($model->save()){
                $this->messageManager->addSuccess(__('The payment has been saved.'));
                $this->_getSession()->setFormData(false);
                $this->_getSession()->setData('deposit_payment_model',null);
            }
            $this->_coreRegistry->register('icare_deposit_payment',$model);
        } catch (LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            return $resultRedirect->setPath('*/*/view', ['user_id' => $user_id]);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving this payment.'.$e->getMessage()));
            return $resultRedirect->setPath('*/*/view', ['user_id' => $user_id]);
        }

        return $resultRedirect->setPath('*/*/view', ['user_id' => $user_id]);
    }
    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Icare_Deposit::customer_deposit');
    }

    public function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::TMP)
            ->getAbsolutePath('/');
    }
}