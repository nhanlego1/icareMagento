<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 24/10/2016
 * Time: 10:02
 */

namespace Icare\Deposit\Test\Unit\Controller\Adminhtml\Deposit;

/**
 * @covers \Icare\Deposit\Controller\Adminhtml\Deposit\View
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Icare\Deposit\Controller\Adminhtml\Deposit\View
     */
    protected $viewController;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Backend\Model\Auth|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $auth;

    /**
     * @var \Magento\User\Model\UserFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userFactoryMock;

    /**
     * @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userMock;

    /**
     * @var \Icare\User\Helper\Role|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $roleHelperMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_model;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMock();
//        $this->auth = $this->getMockBuilder('Magento\Backend\Model\Auth')
//            ->disableOriginalConstructor()
//            ->getMock();
        $this->userFactoryMock = $this->getMockBuilder('Magento\User\Model\UserFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getName', 'getStoreId'])
            ->getMock();
        $this->roleHelperMock = $this->getMockBuilder('Icare\User\Helper\Role')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->auth = $objectManager->getObject(
            'Magento\Backend\Model\Auth'
        );
        $this->_model = $objectManager->getObject(
            'Magento\Backend\Model\Auth\Session'
        );
        $this->auth->setAuthStorage($this->_model);
        $this->context = $objectManager->getObject(
            'Magento\Backend\App\Action\Context',
            [
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
            ]
        );
        $this->viewController = $objectManager->getObject(
            'Icare\Deposit\Controller\Adminhtml\Deposit\View',
            [
                'context' => $this->context,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'userFactory' => $this->userFactoryMock,
                '_roleHelper' => $this->roleHelperMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                '_auth' => $this->auth
            ]
        );
    }

    /**
     * @covers \Icare\Deposit\Controller\Adminhtml\Deposit\View::execute
     */
    public function testExecute()
    {
        $userIdParam = 1;
        $name = 'admin admin';
        $storeId = 13;
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('user_id')
            ->willReturn($userIdParam);
        $this->userFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('load')
            ->with($userIdParam)
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('getName')
            ->willReturn($name);
        $this->userMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Icare_Deposit::customers_deposit');
        $this->resultPageMock->expects($this->once())
            ->method('addBreadcrumb')
            ->withConsecutive(
                ['Deposit Receivable', 'Deposit Receivable']
            );
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->once())
            ->method('prepend')
            ->with($name);

        $this->assertInstanceOf(
            'Magento\Framework\View\Result\Page',
            $this->viewController->execute()
        );
    }

    protected function prepareRedirect()
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * @param string $path
     * @param array $params
     */
    protected function setPath($path, $params = [])
    {
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($path, $params);
    }
}