<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 26/10/2016
 * Time: 11:28
 */

namespace Icare\Cms\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magento\Cms\Controller\Adminhtml\Page\Save
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param StoreManagerInterface $storeManager,
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $dataProcessor);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Cms::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('page_id');
            $originIdentifier = $data['identifier'];

            if (!$id) {
                $stores = $data['stores'];
                foreach ($stores as $storeId) {
                    $store = $this->_storeManager->getStore($storeId);
                    $store_website[$store->getWebsiteId()][] = $storeId;
                }
            } else {
                $model = $this->_objectManager->create('Magento\Cms\Model\Page');
                $model->load($id);
                $store_website[$model->getWebsite()] = $model->getStoreId();
            }

            foreach ($store_website as $website => $stores) {
                $data = $this->dataProcessor->filter($data);

                if (!$id) {
                    $model = $this->_objectManager->create('Magento\Cms\Model\Page');
                    $title = str_replace(' ', '-', strtolower($data['title']));
                    $data['identifier'] = !empty($data['identifier']) ? $data['identifier'].'-'.$website : $title.'-'.$website;
                }
                $data['stores'] = $stores;
                $model->setData($data);

                $this->_eventManager->dispatch(
                    'cms_page_prepare_save',
                    ['page' => $model, 'request' => $this->getRequest()]
                );

                if (!$this->dataProcessor->validate($data)) {
                    return $resultRedirect->setPath('*/*/edit', ['page_id' => $model->getId(), '_current' => true]);
                }

                if ($model->getVariable() && is_array($model->getVariable())) {
                    $variable = implode(',', $model->getVariable());
                    $model->setVariable($variable);
                }
                $model->setWebsite($website);

                try {
                    $model->save();
                    $data['identifier'] = $originIdentifier;
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the page.'));
                }
            }

            $this->messageManager->addSuccess(__('You saved this page.'));
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['page_id' => $model->getId(), '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }
}