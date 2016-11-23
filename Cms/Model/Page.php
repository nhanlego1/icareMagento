<?php
/**
 * Created by PhpStorm.
 * User: nhan
 * Date: 9/19/16
 * Time: 3:45 PM
 */
namespace Icare\Cms\Model;

use Icare\Cms\Api\PageInterface;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;

class Page implements PageInterface{

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
        \Magento\Framework\App\Helper\Context $context)
    {
        $this->_logger = $context->getLogger();
        $this->_logger->setClass($this);
    }

    /**
     * @param string $category
     * @param string $websiteId
     * @throws IcareWebApiException
     */
    public function pageList($categoryId, $websiteId)
    {
        // TODO: Implement pageList() method.
        $result = [];
        $logger = '';
        if(empty($categoryId) || !is_numeric($categoryId)){
            $result[] = new IcareException(__("Category is required."));
            $logger .= __("Category is required.");
        }
        if(empty($websiteId) || !is_numeric($websiteId)){
            $result[] = new IcareException(__("Website Id is required."));
            $logger .= __("Website Id is required.");
        }
        if($result){
            $this->_logger->error($logger);
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //get customer by website id and telephone
        $bind = ['category' => $categoryId,'is_active' => 1, 'website'=>$websiteId];
        $select = $connection->select()->from(
            'cms_page',
            ['page_id']
        );
        $select->where('category = :category');
        $select->where('website = :website');
        $select->where('is_active = :is_active');
        $select->order('sort_order ASC');
        $pages = $connection->fetchAll($select, $bind);
        $data = [];
        if($pages){
            foreach($pages as $id){

                $page = $om->create('Magento\Cms\Model\Page')->load($id['page_id']);
                $page_item = $page->getData();

                    $item = [
                        'page_id' =>$page_item['page_id'],
                        'title' => $page_item['title'],
                        'content_heading' => $page_item['content_heading'],
                        'content' => $page_item['content'],
                        'category' => $this->mappCategory($page_item['category']),
                        'type' => $page_item['type'],
                        'variables'=>$this->getIcareVariable($page_item['variable'])
                    ];

                    $data[] = $item;

            }
            return $data;
        }else{
            $result[] = new IcareException(__("Content not found"));
            $this->_logger->error(__("Content not found"));
            throw new IcareWebApiException(401,__('Web API internal error'),$result);
        }
    }


    /**
     * mapping category
     */
    public function mappCategory($category){
        $cate = [
            2 => __('Installment'),
            3 => __('Sales Order'),
            4=> __('Credit'),
            5 => __('Security'),
            6 => __('Reset Pincode'),
            7 => __('System Support'),
            8 => __('Customer Experience')
        ];
        return $cate[$category];
    }

    /**
     * Get variable by page_id
     */
    public function getIcareVariable($variable){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $data = [];
        if($variable){
            $vars = explode(',',$variable);
                foreach($vars as $var){
                    $variable_item = $om->create('Magento\Variable\Model\Variable')->load($var);
                    $item = array(
                        'id'=> $variable_item->getId(),
                        'key'=> str_replace('cms_','',$variable_item->getCode()),
                        'value'=>$variable_item->getData('plain_value'),
                    );
                    $data[] = $item;
                }

        }
        return $data;
    }

    /**
     * @param int $pageId
     * @param string $type
     * @param int $number
     * @param int $customerId
     * @param string $ratingType
     * @param int $entityId
     * @param string $data
     * @return mixed
     */
    public function pageVote($pageId, $type, $number, $customerId = null, $ratingType = null, $entityId = null, $data = null) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $page = $om->create('Magento\Cms\Model\Page')->load($pageId);

        if (!$page->getId()) {
            throw new IcareWebApiException(404,__
                ('Invalid page, Please select a valid page'));
        }

        // With Rating Type
        if ($type == 'rating') {
            if ($customerId) {
                $customer = $om->create('Magento\Customer\Model\Customer')->load($customerId);
                if (!$customer->getId()) {
                    throw new IcareWebApiException(404,__
                        ('Invalid customer, Please select a valid customer'));
                }

                if ($ratingType == 'sales_order') {
                    $order = $om->create('Magento\Sales\Model\Order')
                        ->load($entityId);
                    if (!$order->getId()) {
                        throw new IcareWebApiException(404, __
                        ('Invalid order, Please select a valid order'));
                    }
                }

                if ($ratingType == 'ticket') {
                    $ticket = $om->create('Magebuzz\Helpdesk\Model\Ticket')
                        ->load($entityId);
                    if (!$ticket->getId()) {
                        throw new IcareWebApiException(404, __
                        ('Invalid ticket, Please select a valid ticket'));
                    }
                }

                try {
                    $rating = $om->create('Icare\Cms\Model\Page\Rating');
                    $data = [
                        'page_id' => $pageId,
                        'customer_id' => $customerId,
                        'rating' => $number,
                        'type' => $ratingType,
                        'entity_id' => $entityId,
                        'data' => $data
                    ];
                    $rating->setData($data);
                    $rating->save();
                } catch (\Exception $ex) {
                    $this->_logger->error($ex);
                    throw new IcareWebApiException(500,__
                        ('Can not save customer rating'));
                }
            } else {
                throw new IcareWebApiException(404,__
                    ('Invalid customer, Please select a valid customer'));
            }
        }

        try {
            // With Like, Unlike Type
            if ($type=='like') {
                $page->setLike($page->getLike() + $number);
                $page->save();
            }
            if ($type=='unlike') {
                $page->setUnlike($page->getUnlike() + $number);
                $page->save();
            }

            $result[] = ['code' => 200, 'status' => true];
            return $result;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            throw new IcareWebApiException(500,__
                ('Can not save cms page'));
        }
    }
}