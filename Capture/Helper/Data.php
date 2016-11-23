<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/3/2016
 * Time: 2:23 PM
 */
namespace Icare\Capture\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /** @var RoleCollectionFactory */
    protected $_roleCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected $_departmentCollectionFactory;

    protected $_statusCollectionFactory;

    protected $_priorityCollectionFactory;

    protected $_quickresponseCollectionFactory;

    protected $_ticketCollectionFactory;

    protected $_messageCollectionFactory;

    protected $_departmentFactory;

    protected $_statusFactory;

    protected $_priorityFactory;

    protected $_quickresponseFactory;

    protected $_ticketFactory;

    protected $_attachmentFactory;

    protected $_attachmentCollectionFactory;

    protected $_customerFactory;

    protected $_userFactory;

    protected $_jsonEncoder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Icare\Capture\Model\ResourceModel\Capture\CollectionFactory $ticketCollectionFactory,
        \Icare\Capture\Model\ResourceModel\CaptureFactory $ticketFactory
    )
    {
        $this->_resource = $resource;
        $this->_coreRegistry = $coreRegistry;
        $this->_resourceHelper = $resourceHelper;
        $this->_localeDate = $localeDate;
        $this->_captureCollectionFactory = $ticketCollectionFactory;
        $this->_captureFactory = $ticketFactory;

        $this->_jsonEncoder = $jsonEncoder;

        parent::__construct($context);
    }


}