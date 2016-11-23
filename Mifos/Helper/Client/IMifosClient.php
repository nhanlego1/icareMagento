<?php
namespace Icare\Mifos\Helper\Client;

use Icare\Mifos\Helper\Entity\MifosAuth;

interface IMifosClient
{
    /**
     * Login Mifos
     * @param $storeId
     * @return mixed
     */
    public function login(MifosAuth $mifosAuth);

    /**
     * Check client is exist on Mifos
     * @param $external_id
     * @return $Client //IcareMember 
     */
    public function searchIcareMember($icareMemberId, MifosAuth $mifosAuth);

    /**
     * Create client on Mifos and auto activated
     * @param Client $client
     * @return $Client
     */
    public function createICareMember($icareMember, $savingProductId, MifosAuth $mifosAuth);

    /**
     * Get product from Mifox
     * @param $product_id
     * @return $product object
     */
    public function getLoanProduct($product_id, MifosAuth $mifosAuth);

    /**
     * Create a loan
     * @param Loan $loan
     * @return loanId
     */
    public function applyLoanForIcareMember($loanProduct, $icareMemberId, $orderTotal, $loanTemplate, MifosAuth $mifosAuth);

    /**
     * Approve a loan
     * @param $loan
     * @return mixed
     */
    public function approveLoan($loanId, MifosAuth $mifosAuth);

    /**
     * Disburse a loan
     * @param $loan
     * @param $orderId
     * @return mixed
     */
    public function disburseLoan($loanId, $orderId, MifosAuth $mifosAuth);

    /**
     * Get loan template
     * @param $icareMemberId
     * @param $loanProductId
     * @return mixed
     */
    public function getTemplateLoan($icareMemberId, $loanProductId, MifosAuth $mifosAuth);

    /**
     * Create new saving account
     * @param $clientId
     * @param $savingProductId
     * @param MifosAuth $mifosAuth
     * @return mixed
     */
    public function createSavingAccount($clientId, $savingProductId, MifosAuth $mifosAuth);

    /**
     * Create a loan
     * @param Loan $loan
     * @return loanId
     */
    public function applyLoanForIcareMemberV2($loanProduct, $icareMemberId, $orderTotal, $loanTemplate, MifosAuth $mifosAuth, $orderIncrementId = '', $numberOfRepayments = 0);
}
