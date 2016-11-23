<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 24/10/2016
 * Time: 10:02
 */

namespace Icare\Deposit\Test\Unit\Controller\Adminhtml\Deposit;

/**
 * @covers \Icare\Deposit\Controller\Adminhtml\Deposit\Index
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Icare\Deposit\Controller\Adminhtml\Deposit\Index
     */
    protected $indexController;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    protected function setUp()
    {
        $this->resultPageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexController = $objectManager->getObject(
            'Icare\Deposit\Controller\Adminhtml\Deposit\Index',
            [
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * @covers \Icare\Deposit\Controller\Adminhtml\Deposit\Index::execute
     */
    public function testExecute()
    {
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Icare_Deposit::customers_deposit');
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->once())
            ->method('prepend')
            ->with('Deposit Receivable');
        $this->resultPageMock->expects($this->once())
            ->method('addBreadcrumb')
            ->withConsecutive(
                ['Deposit Receivable', 'Deposit Receivable']
            );

        $this->assertInstanceOf(
            'Magento\Framework\View\Result\Page',
            $this->indexController->execute()
        );
    }
}
