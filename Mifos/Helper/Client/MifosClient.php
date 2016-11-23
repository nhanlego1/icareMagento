<?php
namespace Icare\Mifos\Helper\Client;

use Icare\ClientRequest\Helper;
use Icare\Exception\Model\IcareException;
use Icare\Exception\Model\IcareWebApiException;
use Icare\Mifos\Helper\Entity\ICareMember;
use Icare\Mifos\Helper\Entity\MifosAuth;
use Icare\Mifos\Helper\Mifos;
use Icare\Custom\Helper\Custom;

class MifosClient implements IMifosClient
{
    private $_clientRequest;
    private static $_instance;

    /**
     * Logging instance
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    const MIFOS_ERROR = 'Internal error from Mifos';
    const LOGIN_REQUEST = 'authentication';
    const ICAREMEMBER_RESOURCE = 'clients';
    const LOANPRODUCT_RESOURCE = 'loanproducts';
    const LOAN_RESOURCE = 'loans';
    const SAVING_ACCOUNT = 'savingsaccounts';
    const OFFICES = 'offices';
    const DEPOSIT = 'deposit';
    const TRANSFER = 'accounttransfers';
    const ORDER_PENDING = 'pending';

    /**
     * Create instance of MifosClient
     * @param $url
     * @return MifosClient
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new MifosClient();
        }

        return static::$_instance;
    }

    /**
     * MifosClient constructor.
     * @param $url
     */
    public function __construct()
    {
        $this->_clientRequest = Helper\ClientRequest::getInstance();
        $this->_variables = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Variable\Model\Variable');
        $this->_logger = \Magento\Framework\App\ObjectManager::getInstance()->get('Icare\Custom\Model\ICareLogger');
        $this->_logger->setClass($this);
    }

    /**
     * Login mifos
     * @throws Exception
     * @throws \Exception
     */
    function login(MifosAuth $mifosAuth)
    {
        try {
            $request = $mifosAuth->getRequestURL() . self::LOGIN_REQUEST . '?username=' . $mifosAuth->getUserName() . '&password=' . $mifosAuth->getPassword();

            $response = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($response, ['mifos login result']);
            if (isset($response->base64EncodedAuthenticationKey)) {
                $this->_logger->debug(sprintf('Mifos Login ok[mifos_url:%s,username:%s]', $mifosAuth->getRequestURL(), $mifosAuth->getUserName()));
                return $response->base64EncodedAuthenticationKey;
            }
            if (isset($response->defaultUserMessage)) {
                $messages[] = new IcareException(__($response->defaultUserMessage));
            }
            if (isset($response->error)) {
                $messages[] = new IcareException(__($response->error));
            }

        } catch (\Exception $ex) {

            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Mifos login Failed"), $result);
        }
    }

    /**
     * Check client is exist on Mifos
     * @param $external_id
     * @return mixed
     */
    public function searchIcareMember($icareMemberId, MifosAuth $mifosAuth)
    {
        $clientId = 0;
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            //check clientid from customer first

            if (Custom::create()->getClientIdCustomer($icareMemberId)) {
                $clientId = Custom::create()->getClientIdCustomer($icareMemberId);
            }


            if ($clientId == 0) {
                $request = $mifosAuth->getRequestURL() . self::ICAREMEMBER_RESOURCE . '?limit=1&&offset=0&externalId=' . $icareMemberId;
                $icareMembers = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders());
                $this->_logger->track($icareMembers, ['mifos search icare member result']);
                if ($icareMembers->pageItems) {
                    $icareMembers = reset($icareMembers->pageItems);
                    if ($icareMembers->id > 0) {
                        $clientId = $icareMembers->id;
                        //save client_id to customer
                        Custom::create()->saveClientIdCustomer($icareMembers->id, $icareMemberId);
                    }
                }
                if ($clientId == 0) {

                    $this->_logger->debug(sprintf('Can not find Mifos Client[iCareMember:%s]', $icareMemberId));
                    //add icare member
                    $customer = $om->create('Magento\Customer\Model\Customer')->load($icareMemberId);
                    $savingProductId = $this->_variables->setStoreId($customer->getStoreId())->loadByCode(Mifos::MIFOS_SAVING_ACCOUNT)->getPlainValue();
                    $client = $this->createICareMember($customer, $savingProductId, $mifosAuth);
                    //save client_id to customer
                    Custom::create()->saveClientIdCustomer($client->clientId, $icareMemberId);
                    $clientId = $client->clientId;
                }

            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Search Icare Member Failed"), $result);
        }
        return $clientId;
    }

    /**
     * Create client on Mifos
     * @param Client $client
     * @return mixed
     */
    public function createICareMember($icareMember, $savingProductId, MifosAuth $mifosAuth)
    {
        try {
            $data = array(
                'officeId' => 1,//$this->checkOfficeId($icareMember, $mifosAuth),
                'firstname' => $icareMember->getFirstname(),
                'middlename' => $icareMember->getMiddlename(),
                'lastname' => $icareMember->getLastname(),
                'externalId' => $icareMember->getId(),
                'dateFormat' => 'dd MMMM yyyy',
                'locale' => 'en',
                'active' => true,
                'activationDate' => gmdate('d M Y'),
                'savingsProductId' => $savingProductId,
                'organizationId' => Custom::create()->getCustomerOrgId($icareMember->getEntityId())
            );

            $request = $mifosAuth->getRequestURL() . self::ICAREMEMBER_RESOURCE;
            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($response, ['mifos create icare member result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not create Mifos Client[data:%s, response:%s]', print_r($data, true), print_r($response, true)));
                throw new IcareException(__($response->defaultUserMessage));
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Create Icare Member Failed"), $result);
        }

    }

    /**
     * Get product from Mifos
     * @param $product_id
     * @return $product object
     */
    public function getLoanProduct($loanProductId, MifosAuth $mifosAuth)
    {
        try {
            $request = $mifosAuth->getRequestURL() . self::LOANPRODUCT_RESOURCE . '/' . $loanProductId;
            $response = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders());
            $this->_logger->track($response, ['mifos get loan result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not get Mifos Loan Product[loanProductId:%s, response:%s]', $loanProductId, print_r($response, true)));
                $messages[] = new IcareException(__($response->defaultUserMessage));
                throw new IcareWebApiException($response->httpStatusCode, $response->defaultUserMessage, $messages);
            }
            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Get Loan Product Failed"), $result);
        }

    }

    /**
     * Create a loan
     * @param Loan $loan
     * @return mixed
     */
    public function applyLoanForIcareMember($loanProduct, $icareMemberId, $orderTotal, $loanTemplate, MifosAuth $mifosAuth, $orderIncrementId = '')
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $orderObject = $om->create('Magento\Sales\Model\Order');
        $productObject = $om->create('Magento\Catalog\Model\Product');
        try {
            $order = $orderObject->loadByIncrementId($orderIncrementId);
            $product = [];
            foreach ($order->getItemsCollection() as $orderItem) {
                $item = $productObject->load($orderItem->getProductId());
                if ($item->getTypeId() == 'virtual') {
                    $product[] = $item;
                }
            }
            $product = reset($product);
            if ($product) {
                $numberOfRepayments = $product->getSku();
            } else {
                $numberOfRepayments = $loanProduct->numberOfRepayments;
            }
            //search loan before save
            $loanRequest = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '?externalId=' . $orderIncrementId;
            $loanResponse = $this->_clientRequest->execute(null, $loanRequest, $mifosAuth->getHeaders(), 'GET');
            $this->_logger->track($loanResponse, ['mifos search loan result']);
            if (count($loanResponse->pageItems) >= 1) {
                $loadObj = reset($loanResponse->pageItems);
                $loan = new \stdClass();
                $loan->officeId = $loadObj->clientOfficeId;
                $loan->clientId = $loadObj->clientId;
                $loan->loanId = $loadObj->id;
                $loan->resourceId = $loadObj->id;
                return $loan;
            }
            //save loan
            $request = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE;
            $data = array(
                'dateFormat' => 'dd MMMM yyyy',
                'locale' => 'en_GB',
                'clientId' => $icareMemberId,
                'productId' => $loanProduct->id,
                'principal' => $orderTotal,
                'loanTermFrequency' => $numberOfRepayments != null ? $numberOfRepayments : $loanTemplate['termfrequency'],
                'loanTermFrequencyType' => $loanTemplate['termPeriodFrequencyType'],
                'loanType' => 'individual',
                'numberOfRepayments' => $numberOfRepayments,
                'repaymentEvery' => $loanProduct->repaymentEvery,
                'repaymentFrequencyType' => $loanProduct->repaymentFrequencyType->id,
                'interestRatePerPeriod' => $loanProduct->interestRatePerPeriod,
                'amortizationType' => $loanProduct->amortizationType->id,
                'interestType' => $loanProduct->interestType->id,
                'interestCalculationPeriodType' => $loanProduct->interestCalculationPeriodType->id,
                'transactionProcessingStrategyId' => $loanProduct->transactionProcessingStrategyId,
                'expectedDisbursementDate' => gmdate('d M Y'),
                'submittedOnDate' => gmdate('d M Y'),
                'externalId' => $orderIncrementId,
                // TODO: must be load from mifos api fund
                'fundId' => 1
            );

            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($response, ['mifos apply loan result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not apply Mifos Loan[data:%s, response:%s]', print_r($data, true), print_r($response, true)));
                $messages[] = new IcareException(__($response->defaultUserMessage));
                throw new IcareWebApiException($response->httpStatusCode, $response->defaultUserMessage, $messages);
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Apply Loan For Icare Member Failed"), $result);
        }

    }

    /**
     * Create a loan
     * @param Loan $loan
     * @return mixed
     */
    public function applyLoanForIcareMemberV2($loanProduct, $icareMemberId, $orderTotal, $loanTemplate, MifosAuth $mifosAuth, $orderIncrementId = '', $numberOfRepayments = 0, $saving = 0, $savingAmount = 0)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            $order = $om->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);
            //check saving aount is using
            $amount = $orderTotal;
            $isLoan = true;
            if($savingAmount == 0){
                $savingAmount = $order->getSavingAccountAmount();
            }

//             if ($savingAmount == 0) {
//                 $savingAmount = Custom::create()->getTempSaving($order->getCustomerId());
//             }

            if ($savingAmount > 0) {
                if ($savingAmount >= $orderTotal) {
                    $isLoan = false;
                } else {

                    $amount = floatval($orderTotal) - floatval($savingAmount);
                }
            }

            if ($isLoan && $amount > 0) {
                if (!$numberOfRepayments) {
                    $numberOfRepayments = $loanProduct->numberOfRepayments;
                }
                //check saving account
                $savingId = '';
                if ($saving == 1) {
                    $requestIns = $mifosAuth->getRequestURL() . 'icare/' . self::ICAREMEMBER_RESOURCE . '/' . $icareMemberId;
                    $responseIns = $this->_clientRequest->execute(null, $requestIns, $mifosAuth->getHeaders(), 'GET');
                    if (isset($responseIns->errors) || isset($responseIns->error)) {
                        throw new IcareException(__($responseIns->defaultUserMessage));
                    }
                    $savingId = $responseIns->savingsAccount->id;
                }
                //search loan before save
                $loanRequest = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '?externalId=' . $orderIncrementId;
                $loanResponse = $this->_clientRequest->execute(null, $loanRequest, $mifosAuth->getHeaders(), 'GET');
                $this->_logger->track($loanResponse, ['mifos search loan result']);
                if (count($loanResponse->pageItems) >= 1) {
                    $loadObj = reset($loanResponse->pageItems);
                    $loan = new \stdClass();
                    $loan->officeId = $loadObj->clientOfficeId;
                    $loan->clientId = $loadObj->clientId;
                    $loan->loanId = $loadObj->id;
                    $loan->resourceId = $loadObj->id;
                    return $loan;
                }
                
                if ($order->getCreatedAt()) {
                    $orderCreatedAt = gmdate('d M Y', strtotime($order->getCreatedAt()));
                } else {
                    $orderCreatedAt = gmdate('d M Y');
                }
                
                
                //save loan
                $request = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE;
                $data = array(
                    'dateFormat' => 'dd MMMM yyyy',
                    'locale' => 'en_GB',
                    'clientId' => $icareMemberId,
                    'productId' => $loanProduct->id,
                    'principal' => floatval($amount),
                    'loanTermFrequency' => $numberOfRepayments != null ? $numberOfRepayments : $loanTemplate['termfrequency'],
                    'loanTermFrequencyType' => $loanTemplate['termPeriodFrequencyType'],
                    'loanType' => 'individual',
                    'numberOfRepayments' => $numberOfRepayments,
                    'repaymentEvery' => $loanProduct->repaymentEvery,
                    'repaymentFrequencyType' => $loanProduct->repaymentFrequencyType->id,
                    'interestRatePerPeriod' => $loanProduct->interestRatePerPeriod,
                    'amortizationType' => $loanProduct->amortizationType->id,
                    'interestType' => $loanProduct->interestType->id,
                    'interestCalculationPeriodType' => $loanProduct->interestCalculationPeriodType->id,
                    'transactionProcessingStrategyId' => $loanProduct->transactionProcessingStrategyId,
                    'expectedDisbursementDate' => $orderCreatedAt,
                    'submittedOnDate' => $orderCreatedAt,
                    'externalId' => $orderIncrementId,
                    // TODO: must be load from mifos api fund
                    'fundId' => 1,
                    "linkAccountId" => $savingId,
                    "repaymentsStartingFromDate" => $orderCreatedAt
                );

                $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
                $this->_logger->track($response, ['mifos apply loan result']);
                if (isset($response->errors) || isset($response->error)) {
                    $this->_logger->debug(sprintf('Can not apply Mifos Loan[data:%s, response:%s]', print_r($data, true), print_r($response, true)));
                    $messages[] = new IcareException(__($response->defaultUserMessage));
                    throw new IcareWebApiException($response->httpStatusCode, $response->defaultUserMessage, $messages);
                }

                return $response;
            } else {
                return 'saving';
            }


        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Apply Loan For Icare Member Failed"), $result);
        }

    }

    public function approveLoan($loanId, MifosAuth $mifosAuth)
    {
        try {
            //get Loan to check status
            $requestLoan = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId;
            $responseLoan = $this->_clientRequest->execute(null, $requestLoan, $mifosAuth->getHeaders(), 'GET');
            if ($responseLoan->status->code != 'loanStatusType.approved') {
                $request = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?command=approve';
                $data = array(
                    'locale' => 'en',
                    'dateFormat' => 'dd MMMM yyyy',
                    'approvedOnDate' => gmdate('d M Y'),
                );

                $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
                $this->_logger->track($response, ['mifos approve loan result']);
                if (isset($response->errors) || isset($response->error)) {
                    $this->_logger->debug(sprintf('Can not approve Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($response, true)));
                    throw new IcareException(__($response->defaultUserMessage));
                }

                return $response;
            }

        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Failed to approve loan " . $loanId), $result);
        }

    }

    /**
     * @param $loanId
     * @param MifosAuth $mifosAuth
     * @return mixed
     * @throws IcareWebApiException
     */
    public function disburseLoan($loanId, $orderId, MifosAuth $mifosAuth)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        try {
            $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
            if ($order->getCreatedAt()) {
                $orderCreatedAt = gmdate('d M Y', strtotime($order->getCreatedAt()));
            } else {
                $orderCreatedAt = gmdate('d M Y');
            }
            $request = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?command=disburse';
            $data = array(
                'dateFormat' => 'dd MMMM yyyy',
                'locale' => 'en',
                'actualDisbursementDate' => $orderCreatedAt,
            );

            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($response, ['mifos disburse loan result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not disburse Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($response), true));
                throw new IcareException(__($response->defaultUserMessage));
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Disburse For Icare Member Failed"), $result);
        }
    }

    /**
     * @param $loanId
     * @param MifosAuth $mifosAuth
     * @return mixed
     * @throws IcareWebApiException
     */
    public function cancelLoan($loanId, $orderId, MifosAuth $mifosAuth)
    {
        try {
            //check status order first
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $om->get('Magento\Sales\Model\Order')->load($orderId);
            $this->_logger->info(sprintf("Cancel Loan[orderId=%s,loanId=%s,status=%s]", $order->getId(), $loanId, $order->getStatus()));
            if ($order) {
                //check if order not confirm
                if ($order->getStatus() == \Icare\EventOrder\Plugin\OrderPlugin::ORDER_PENDING) {
                    // check status loan before undoApproval
                    $loanInfo = $this->getLoan($loanId, $mifosAuth);
                    if ($loanInfo && isset($loanInfo->status)) {
                        /**
                         * INVALID(0, "loanStatusType.invalid"), //
                        SUBMITTED_AND_PENDING_APPROVAL(100, "loanStatusType.submitted.and.pending.approval"), //
                        APPROVED(200, "loanStatusType.approved"), //
                        ACTIVE(300, "loanStatusType.active"), //
                        TRANSFER_IN_PROGRESS(303, "loanStatusType.transfer.in.progress"), //
                        TRANSFER_ON_HOLD(304, "loanStatusType.transfer.on.hold"), //
                        WITHDRAWN_BY_CLIENT(400, "loanStatusType.withdrawn.by.client"), //
                        REJECTED(500, "loanStatusType.rejected"), //
                        CLOSED_OBLIGATIONS_MET(600, "loanStatusType.closed.obligations.met"), //
                        CLOSED_WRITTEN_OFF(601, "loanStatusType.closed.written.off"), //
                        CLOSED_RESCHEDULE_OUTSTANDING_AMOUNT(602, "loanStatusType.closed.reschedule.outstanding.amount"), //
                        OVERPAID(700, "loanStatusType.overpaid");
                         */
                        if ($loanInfo->status->id >= 200 ) {
                            //undo approval
                            $requestAp = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?command=undoApproval';
                            $data = array(
                                'note' => 'Undo Approval Loan ' . $loanId . ' ',
                            );

                            $responseAp = $this->_clientRequest->execute($data, $requestAp, $mifosAuth->getHeaders(), 'POST');
                            $this->_logger->track($responseAp, ['mifos cancel loan result']);
                            if (isset($responseAp->errors) || isset($responseAp->error)) {
                                $this->_logger->debug(sprintf('Can not cancel Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($responseAp, true)));
                            }
                            return $responseAp;
                        }
                        //delete Loan
                        // TODO: must discuss how to delete LOAN
//                        $requestDelete = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId;
//                        $responseDelete = $this->_clientRequest->execute(NULL, $requestAp, $mifosAuth->getHeaders(), 'DELETE');
//                        $this->_logger->track($responseAp, ['mifos delete loan result']);
//                        if (isset($responseAp->errors) || isset($responseAp->error)) {
//                            $this->_logger->debug(sprintf('Can not delete Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($responseDelete, true)));
//                        }
//                        return $requestDelete;
                        return [];
                    }

                } else {
                    //@Todo : waiting for approval from PM
                    //cancel order 
                    //check if order is ready disburb Loan
                    //undo disbusrs first
//                    $requestDis = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?command=undoDisbursal';
//                    $data = array(
//                        'note' => 'Undo disburs Loan ' . $loanId . ' ',
//                    );
//
//                    $responseDis = $this->_clientRequest->execute($data, $requestDis, $mifosAuth->getHeaders(), 'POST');
//                    $this->_logger->track($responseDis, ['mifos cancel loan result']);
//                    if (isset($responseDis->errors) || isset($responseDis->error)) {
//                        $this->_logger->debug(sprintf('Can not cancel Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($responseDis, true)));
//                        throw new IcareException(__($responseDis->defaultUserMessage));
//                    }
//
//                    //undo approval
//                    $requestAp = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?command=undoApproval';
//                    $data = array(
//                        'note' => 'Undo Approval Loan ' . $loanId . ' ',
//                    );
//
//                    $responseAp = $this->_clientRequest->execute($data, $requestAp, $mifosAuth->getHeaders(), 'POST');
//                    $this->_logger->track($responseAp, ['mifos cancel loan result']);
//                    if (isset($responseAp->errors) || isset($responseAp->error)) {
//                        $this->_logger->debug(sprintf('Can not cancel Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($responseAp, true)));
//                    }
//
//                    //reject the loan
//
//                    $requestRe = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?command=reject';
//                    $data = array(
//                        'locale' => 'en',
//                        "dateFormat"=> "dd MMMM yyyy",
//                        'rejectedOnDate' => gmdate('d M Y'),
//                        'note' => 'Reject Loan ' . $loanId . ' ',
//                    );
//
//                    $responseRe = $this->_clientRequest->execute($data, $requestRe, $mifosAuth->getHeaders(), 'POST');
//                    $this->_logger->track($responseRe, ['mifos cancel loan result']);
//                    if (isset($responseRe->errors) || isset($responseRe->error)) {
//                        $this->_logger->debug(sprintf('Can not cancel Mifos Loan[loanId:%s, data:%s, response:%s]', $loanId, print_r($data, true), print_r($responseRe, true)));
//                        //      throw new IcareException(__($responseRe->defaultUserMessage));
//                    }
//                    return $responseRe;

                }

            }

        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Cancel Loan For Icare Member Failed"), $result);
        }
    }

    /**
     * @param $loanId
     * @param MifosAuth $mifosAuth
     * @return mixed
     * @throws IcareWebApiException
     */
    public function getLoan($loanId, MifosAuth $mifosAuth)
    {
        try {
            $request = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/' . $loanId . '?associations=all';

            $response = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders(), 'GET');
            $this->_logger->track($response, ['mifos get loan result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not get Mifos Loan[loanId:%s, response:%s]', $loanId, print_r($response, true)));
                throw new IcareException(__($response->defaultUserMessage));
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Get Loan For Icare Member Failed"), $result);
        }

    }

    /**
     * @param $loanId
     * @param MifosAuth $mifosAuth
     * @return mixed
     * @throws IcareWebApiException
     */
    public function getInstallment($customerId, MifosAuth $mifosAuth)
    {

        try {

            $clientId = $this->searchIcareMember($customerId, $mifosAuth);

            $request = $mifosAuth->getRequestURL() . 'icare/' . self::ICAREMEMBER_RESOURCE . '/' . $clientId;
            $response = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders(), 'GET');

            $this->_logger->track($response, ['mifos get installment result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not get Mifos Installment[customerId:%s, response:%s]', $customerId, print_r($response, true)));
                throw new IcareException(__($response->defaultUserMessage));
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Get Installment For Icare Member Failed"), $result);
        }
    }

    /**
     * @param $icareMemberId
     * @param $loanProductId
     * @param MifosAuth $mifosAuth
     * @return array
     * @throws IcareWebApiException
     */

    public function getTemplateLoan($icareMemberId, $loanProductId, MifosAuth $mifosAuth)
    {
        try {
            $request = $mifosAuth->getRequestURL() . self::LOAN_RESOURCE . '/template?clientId=' . $icareMemberId . '&productId=' . $loanProductId;

            $request .= '&tenantIdentifier=' . $mifosAuth->getTernant() . '&templateType=individual';
            $response = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders(), 'GET');
            $this->_logger->track($response, ['mifos get template loan result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not get Mifos template loan[icaremember:%s, loanProductId:%s, response:%s]', $icareMemberId, $loanProductId, print_r($response, true)));
                throw new IcareException(__($response->defaultUserMessage));
            }

            $result = array(
                'termfrequency' => $response->termFrequency,
                'termPeriodFrequencyType' => $response->termPeriodFrequencyType->id
            );

            return $result;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Get Template Loan For Icare Member Failed"), $result);
        }
    }

    public function createSavingAccount($clientId, $savingProductId, MifosAuth $mifosAuth)
    {
        try {
            $request = $mifosAuth->getRequestURL() . self::SAVING_ACCOUNT;
            $data = array(
                'dateFormat' => 'dd MMMM yyyy',
                'locale' => 'en',
                'clientId' => $clientId,
                'productId' => $savingProductId,
                'submittedOnDate' => gmdate('d M Y'),
            );

            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($response, ['mifos create saving result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not create Mifos saving account[clientId:%s, data:%s, response:%s]', $clientId, print_r($data, true), print_r($response, true)));
                $messages[] = new IcareException(__($response->defaultUserMessage));
                throw new IcareWebApiException($response->httpStatusCode, $response->defaultUserMessage, $messages);
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Create Saving Account For Icare Member Failed"), $result);
        }

    }

    /**
     * Check office id before create new office
     */
    public function checkOfficeId($icareMember, $mifosAuth)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $store = $om->get('Magento\Store\Model\Store')->load($icareMember->getStoreId());
            $request = $mifosAuth->getRequestURL() . self::OFFICES . '?fields=externalId,id,name';
            $responses = $this->_clientRequest->execute(null, $request, $mifosAuth->getHeaders(), 'GET');
            $this->_logger->track($responses, ['mifos check office result']);
            $externalId = array();
            if (isset($responses->errors) || isset($responses->error)) {
                $this->_logger->debug(sprintf('Office not found [response:%s]', print_r($responses, true)));
                throw new IcareException(__($responses->defaultUserMessage));
            } else {

                foreach ($responses as $office) {
                    if ($office->externalId == $icareMember->getStoreId() || $office->name == $store->getName()) {
                        $externalId[] = $office->id;
                        break;
                    }
                }
                if ($externalId) {
                    return reset($externalId);
                } else {
                    return $this->createOfficeMifos($icareMember->getStoreId(), $mifosAuth);
                }

            }
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Check Office Id For Icare Member Failed"), $result);
        }

    }

    /**
     * Create office id by store
     */
    public function createOfficeMifos($storeId, $mifosAuth)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $store = $om->get('Magento\Store\Model\Store')->load($storeId);
            $request = $mifosAuth->getRequestURL() . self::OFFICES;
            $data = array(
                'name' => $store->getName(),
                'dateFormat' => 'dd MMMM yyyy',
                'locale' => 'en',
                'openingDate' => date('d M Y'),
                'parentId' => 1,
                'externalId' => $store->getId()
            );
            $responses = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($responses, ['mifos create office result']);
            if (isset($responses->errors) || isset($responses->error)) {
                $this->_logger->debug(sprintf('Can not create Mifos Office[storeId:%s, response:%s]', $storeId, print_r($responses, true)));
                throw new IcareException(__($responses->defaultUserMessage));
            } else {
                return $responses->officeId;
            }

        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Create Office Id For Icare Member Failed"), $result);
        }


    }

    /**
     * add deposit for customer
     */
    public function addDeposit($customerId, $amount, $mifosAuth, $orderId = null)
    {
        try {

            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $om->get('Magento\Customer\Model\Customer')->load($customerId);

            $clientId = $this->searchIcareMember($customerId, $mifosAuth);

            //get installment to get saving account number
            $requestIns = $mifosAuth->getRequestURL() . 'icare/' . self::ICAREMEMBER_RESOURCE . '/' . $clientId;

            $responseIns = $this->_clientRequest->execute(null, $requestIns, $mifosAuth->getHeaders(), 'GET');

            if (isset($responseIns->errors) || isset($responseIns->error)) {
                $this->_logger->debug(sprintf('Can not get saving acount of client [customerId:%s, response:%s]', $customerId, print_r($responseIns, true)));
                throw new IcareException(__($responseIns->defaultUserMessage));
            }

            $savingAccount = $responseIns->savingsAccount->id;

            ///add deposit amount to saving account
            $request = $mifosAuth->getRequestURL() . self::SAVING_ACCOUNT . '/' . $savingAccount . '/transactions?command=' . self::DEPOSIT;

            //check order external Id
            if ($orderId) {
                $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
                $reason = 'ORDER_DEPOSIT';
                $receiptNumber = $order->getIncrementId();
            } else {
                $reason = 'CUSTOMER_DEPOSIT';
                $receiptNumber = '';
            }

            $data = array(
                "locale" => "en",
                "dateFormat" => "dd MMMM yyyy",
                "transactionDate" => date('d M Y'),
                "transactionAmount" => floatval($amount),
                "paymentTypeId" => $this->_variables->setStoreId($customer->getStoreId())->loadByCode('paymentTypeId')->getPlainValue() ? (int) $this->_variables->setStoreId($customer->getStoreId())->loadByCode('paymentTypeId')->getPlainValue() : 1,
                "accountNumber" => $savingAccount,
                "checkNumber" => '',
                "routingCode" => $reason,
                "receiptNumber" => $receiptNumber,
                "bankNumber" => ''
            );
            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            $this->_logger->track($response, ['mifos add deposit result']);
            if (isset($response->errors) || isset($response->error)) {
                $this->_logger->debug(sprintf('Can not add deposit amount [customerId:%s, response:%s]', $customerId, print_r($response, true)));
                throw new IcareException(__($response->defaultUserMessage));
            }

            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("add deposit For Icare Member Failed"), $result);
        }
    }

    /**
     * transfer deposit
     */
    public function transferDeposit($customerId, $orderId, $amount = 0, $mifosAuth)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
            $clientId = $this->searchIcareMember($customerId, $mifosAuth);
            //get installment to get saving account number
            $requestIns = $mifosAuth->getRequestURL() . 'icare/' . self::ICAREMEMBER_RESOURCE . '/' . $clientId;
            $responseIns = $this->_clientRequest->execute(null, $requestIns, $mifosAuth->getHeaders(), 'GET');
            if (isset($responseIns->errors) || isset($responseIns->error)) {
                $this->_logger->debug(sprintf('Can not get saving acount of client [customerId:%s, response:%s]', $customerId, print_r($responseIns, true)));
                throw new IcareException(__($responseIns->defaultUserMessage));
            }
            $savingAccount = $responseIns->savingsAccount->id;
            if ($amount == 0) {
                $total = $order->getBaseGrandTotal();
            } else {
                if ($amount > $order->getBaseGrandTotal()) {
                    $total = $order->getBaseGrandTotal();
                } else {
                    $total = $amount;
                }
            }

            //get office id from cient id
            $requestClient = $mifosAuth->getRequestURL() . self::ICAREMEMBER_RESOURCE . '/' . $clientId;
            $responseClient = $this->_clientRequest->execute(null, $requestClient, $mifosAuth->getHeaders(), 'GET');
            if (isset($responseClient->errors) || isset($responseClient->error)) {
                $this->_logger->debug(sprintf('Can not get client [customerId:%s, response:%s]', $customerId, print_r($responseIns, true)));
                throw new IcareException(__($responseIns->defaultUserMessage));
            }
            //get office
            $officeId = $responseClient->officeId;

            //transfer deposit

            $data = array(
                "fromOfficeId" => $officeId,
                "fromClientId" => $clientId,
                "fromAccountType" => 2,
                "fromAccountId" => $savingAccount,
                "toOfficeId" => $officeId,
                "toClientId" => $clientId,
                "toAccountType" => 1,
                "toAccountId" => $order->getLoanId(),
                "dateFormat" => "dd MMMM yyyy",
                "locale" => "en",
                "transferDate" => date('d M Y'),
                "transferAmount" => $total,
                "transferDescription" => __("Transfer anount for client " . $clientId . " with amount " . $amount),
            );

            $request = $mifosAuth->getRequestURL() . self::TRANSFER;
            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Transfer deposit For Icare Member Failed"), $result);
        }

    }

    /*
     * withdrw amount from saving account
     */
    public function withdrawAmount($customerId, $orderId, $amount, $mifosAuth)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $om->create('Magento\Sales\Model\Order')->load($orderId);
            $customer = $om->get('Magento\Customer\Model\Customer')->load($customerId);
            $clientId = $this->searchIcareMember($customerId, $mifosAuth);
            //get installment to get saving account number
            $requestIns = $mifosAuth->getRequestURL() . 'icare/' . self::ICAREMEMBER_RESOURCE . '/' . $clientId;
            $responseIns = $this->_clientRequest->execute(null, $requestIns, $mifosAuth->getHeaders(), 'GET');
            if (isset($responseIns->errors) || isset($responseIns->error)) {
                $this->_logger->debug(sprintf('Can not get saving acount of client [customerId:%s, response:%s]', $customerId, print_r($responseIns, true)));
                throw new IcareException(__($responseIns->defaultUserMessage));
            }
            $savingAccount = $responseIns->savingsAccount->id;


            $data = array(
                "locale" => "en",
                "dateFormat" => "dd MMMM yyyy",
                "transactionDate" => date('d M Y'),
                "transactionAmount" => floatval($amount),
                "paymentTypeId" => "1",//$this->_variables->setStoreId($customer->getStoreId())->loadByCode('paymentTypeId')->getPlainValue() ? $this->_variables->setStoreId($customer->getStoreId())->loadByCode('paymentTypeId')->getPlainValue() : 1,
                "accountNumber" => "",
                "checkNumber" => "",
                "routingCode" => "",
                "receiptNumber" => $order->getIncrementId(),
                "bankNumber" => "",
            );

            $request = $mifosAuth->getRequestURL() . 'savingsaccounts/' . $savingAccount . '/transactions?command=withdrawal';
            $response = $this->_clientRequest->execute($data, $request, $mifosAuth->getHeaders(), 'POST');
            return $response;
        } catch (\Exception $ex) {
            $this->_logger->error($ex);
            $result[] = new IcareException(__($ex->getMessage()));
            throw new IcareWebApiException(401, __("Withdraw amount from saving account Failed"), $result);
        }

    }

}