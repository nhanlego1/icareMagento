<?php

namespace Icare\Custom\Plugin\Controller\Adminhtml\Bookmark;

/**
 * Plugin for save action of bookmark controller to add redirect if <strong>redirect</strong> parammeter 
 * is specified.
 * 
 * @author Nam Pham
 *
 */
class SavePlugin
{
    /**
     * 
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;
    
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        $this->_redirect = $context->getRedirect();
    }
    
    public function aroundExecute(
        \Magento\Ui\Controller\Adminhtml\Bookmark\Save $subject,
        \Closure $proceed
    )
    {
        $return = $proceed();
        
        $redirect = $subject->getRequest()->getParam('redirect');
        if (empty($redirect) || empty($redirect['path'])) {
            return $return;
        }
        else {
            $this->_redirect->redirect($subject->getResponse(), $redirect['path'], isset($redirect['params'])?$redirect['params']:[]);
        }
    }
}