<?php
/* vim: set tabstop=4 shiftwidth=4*/
/**
 * MAccount
 *
 * PHP version 5.1 or greater
 *
 * @category  Account
 * @package   Models
 * @author    Scott Hancock
 * @copyright REDACTED
 * @license   Not licensed for external use
 * @link      REDACTED
 */

class MAccount extends LModel
{
    /*
     * constants used in AIMorPIA property
     */
    const PIA = 0x0;
    const AIM = 0x1;
    /**#@+
     * Account Status Constants
     */
    const STATUS_ACTIVE     = 'A';
    const STATUS_CANCELLED  = 'C';
    const STATUS_SUSPENDED  = 'S';
    const STATUS_PENDING    = 'P';
    const STATUS_HOTLINED   = 'H';
    const STATUS_UNHOTLINED = 'U';

    /*
     * constants for bucket names
     */
    const PIA_CARE = 'PIA_CARE_CREDIT';
    const DVI      = 'FLEX_DVI';

    /**
     * List of account statuses
     * @var array
     */
    public static $accountStatuses = array(
        self::STATUS_ACTIVE     =>'Active',
        self::STATUS_CANCELLED  =>'Cancelled',
        self::STATUS_SUSPENDED  =>'Suspended',
        self::STATUS_HOTLINED   =>'Hotlined',
        self::STATUS_UNHOTLINED =>'Unhotlined',
        self::STATUS_PENDING    =>'Pending',
    );

    /**#@+
     * Raw Customer Data Services (CDS) field
     */
    /**
     * @var string Last name
     */
    public $lastName;

    /**
     * @var string First name
     */
    public $firstName;


    /**
     * @var string Middle initial
     */
    public $middleInitial;

    /**
     * @var string companyName company name
     */
    public $companyName;

    /**
     * Customers home phone number
     * @var string
     */
    public $homePhone;

    /**
     * Customer's work phone
     * @var string
     */
    public $workPhone;
    
    /**
     * Customer's email address
     * @var string
     */
    public $accountEmail;
    
    /*
     * Account registration email
     * @var string
     */
    public $email;

    /**
     * @var string Billing address (line 1)
     */
    public $address1;

    /**
     * @var string Billing address (line 2)
     */
    public $address2;

    /**
     * @var string City (billing address)
     */
    public $city;

    /**
     * @var string State (billing address)
     */
    public $state;

    /**
     * @var string Zip code (billing address)
     */
    public $zip5;

    /**
     * @var string Extended Zip4 code (billing address)
     */
    public $zip4;

    /**
     * @var string Social security number
     */
    public $SSN;

    /**
     * @var string Drivers license ID / Number
     */
    public $driversLicense;

    /**
     * @var float Last payment amount
     */
    public $lastPaymentAmount;

    /**
     * @var string Account number
     */
    public $accountNumber;
    /**#@-*/

    /**#@+
     * Primarily used for calculation not display
     */
    /**
     * @var string Status of account (from CDS)
     */
    public $status;

    /**
     * @var string Customer type (Jump, REDACTED, etc.)
     */
    public $customerType;
    
    /**
     * Friendly name of customer type
     * 
     * @var string
     */
    public $customerTypeName;

    /**
     * @var float Balance due
     */
    public $balanceDue;

    /**
     * @var float MTD (month to date?) amount
     */
    public $mtdAmount;

    /**
     * @var float Current account balance
     */
    public $currentBalance;
    /**#@-*/

    /**#@+
     * Type converted or imported field
     */
    /**
     * @var int Timestamp of last HO update
     */
    public $lastHOUpdate;

    /**
     * @var int Customer's birthdate
     */
    public $birthDate;

    /**
     * @var bool Issue a bill? (opposite of paperless billing)
     */
    public $issueBill;

    /**
     * @var string billCycle
     */
    public $billCycle;

    /**
     * @var bool Is the account hotlined?
     */
    public $hotlined;

    /**
     * @var int Date of the last payment
     */
    public $lastPaymentDate;

    /**
     * @var int Date of the last bill
     */
    public $billedDate;

    /**
     * @var bool Automatic bill pay?
     */
    public $ABP;

    /**
     * @var bool Paperless billing?
     */
    public $PLB;
    /**#@-*/

    /**#@+
     * Calculated data member
     */
    /**
     * @var string Market code
     */
    public $marketCode;

    /**
     * @var string Formatted customer name (first, middle, last)
     */
    public $displayName;

    /**
     * @var string Formatted account number (hyphens added)
     */
    public $formattedAccountNumber;

    /**
     * @var string Formatted social security number (hyphens added)
     */
    public $formattedSSN;

    /**
     * @var string Post-processed status string
     */
    public $displayStatus;

    /**
     * @var int Date bill is due
     */
    public $dueDate;

    /**
     * @var int Start of billing period
     */
    public $billingPeriodStart;

    /**
     * @var int End of billing period
     */
    public $billingPeriodEnd;

    /**
     * @var float Amount past due
     */
    public $pastDueAmount;

    /**
     * @var float Current charges
     */
    public $currentCharges;
    /**#@-*/

    public $isRealTimeBalances = false;

    /**
     * Indicator if we've loaded live data from HO into the account
     * @var boolean isLiveData
     */
    public $isLiveData;

    /**
     * @var array fraudCodes
     */
    public $fraudCodes;

    /**
     * @var array Phones collection
     */
    public $phones = array();
    
    /*
     * @var boolean SPAM flag
     */
    public $SPAM;
    
    /**
     * Fraud codes
     *
     * @var array validFraudCodes
     */
    public static $validFraudCodes = array(
        'F', // Fraud
        'N', // NSF Checks
        'P', // Payment Fraud
        'S', // Subscription Fraud
        'T'  // Tumbling ENS Fraud
    );

    /**
     * Number of non-family plan voice lines on the account.
     *
     * @var array
     */
    public $wirelessLines = array();
    /**
     * Number of family plan voice lines on an account.
     *
     * @var array
     */
    public $familyLines = array();

    /**
     * Number of data lines on an account.
     *
     * @var array
     */
    public $broadbandLines = array();
    
    /**
     * Account lines that have data usage (BBMM, Smartphone, etc.)
     *
     * @var array
     */
    public $dataUsageLines = array();

    /**
     * Total number of lines of service associated with this account.
     *
     * @var int
     */
    public $totalLinesOfService;

    /**
     * Pending Add A Line work order
     *
     * @var bool
     */
    public $pendingWorkOrder = null;
    
    public $hasPendingLOSChange = false;
    
    public $pendingLOSChangeMessage = '';
    
    /**
     * Last date/time the information was updated
     *
     * @var string
     */
    public $lastUpdateDateTime = null;

    /**
     * Holds the CVG customer Id
     * @var string
     */
    public $customerId;
    
    /**
     * Is this an AIM account or a PIA account
     * @var int isAIMorPIA
     */
    public $isAIMorPIA;
    
    public $isWholesale;
    
    /**
     * Returns an account's MPhone with the earliest startDate
     *
     * @return MPhone Preferred phone
     * @throws MAccountException
     */
    public function getPreferredPhone()
    {
        if (!count($this->phones)) {
            throw new MAccountException('No phones associated with account');
        }

        // when the phones are loaded into the account in CLoadAccount, they
        // are sorted by earliest date at that time. so we can just return
        // the first element of the phones array
        return $this->phones[0];
    }

    /**
     * Retrieves a phone object from a given MDN
     *
     * @param string $mdn Phone number to find
     *
     * @return MPhone
     */
    public function getPhoneFromMDN($mdn)
    {
        // cleanup
        $mdn = MPhone::stripMDN($mdn);

        $data = $this;

        // make sure that the MDN we're trying to lookup belongs to the account
        if (!$this->isValidMdnForAccount($mdn)) {
                throw new MAccountException(
                    'Trying to access MDN ['.$mdn.'] that is not ' .
                    'valid for this account'
            );
        }

        $matchedPhone = null;

        foreach ($data->phones as $phone) {
            if ($phone->MDN == $mdn) {
                $matchedPhone = $phone;
                break; // we found a match
            }
        }

        if (!$matchedPhone) {
            throw new MAccountException(
                'Could not load MDN from account [MDN: '.$mdn.']'
            );
        }

        return $matchedPhone;
    }
    
    /**
     * confirms that the provided mdn is connected
     * to this account
     * 
     * @param string $mdn the mdn to check agains
     * @return bool
     * 
     */
    public function isValidMdnForAccount($mdn)
    {
        if (!in_array($mdn, $this->getArrayOfMDNs())) {
            return false;
        } else { 
            return true;
        }
    }

    /**
     *  Returns an array of mdn numbers for the customer account
     *
     *  @return array MDNs
     */
    public function getArrayOfMDNs()
    {
        $phones = array();
        foreach ($this->phones as $phone) {
            assert('$phone instanceof MPhone');
            $mdn          = MPhone::stripMDN($phone->MDN);
            $phones[$mdn] = $mdn;
        }
        return $phones;
    }

    /**
     * Processes calculated data members
     *
     * Computes:
     * - Display status ($statusDisplay)
     * - Billing & due dates ($dueDate, $billingPeriodStart, $billingPeriodEnd)
     * - Balances ($pastDueAmount, $currentCharges)
     *
     * @return LModel This
     */
    public function calculate()
    {
        $isHO = REDACTED_Factory_Biller::instance()->getBiller()
                    == REDACTED_Factory_Biller::BILLER_HO;
        if ($isHO) {
            $this->extractMarketCode();
        }
        $this->formatDisplayName();
        $this->formattedAccountNumber =
            self::formatAccountNumber($this->accountNumber);
        $this->calculateDisplayStatus();
        $this->calculateBillingAndDueDates();
        $this->calculateBalances();
        $this->formattedSSN = $this->formatSSN($this->SSN);
        $this->calculateNumberOfLines();

        $c = REDACTED_Factory_Biller::instance()
                    ->getCommand(REDACTED_Factory_Biller::PENDING_ORDERS_GET);
        $this->setPendingWorkOrder($c);

        $this->customerTypeName = $this->getCustomerTypeName();
        return $this;
    }

    /**
     * Extracts the market code from the account number
     *
     * Rumor has it, that the market code is the first three digits of the
     * account number.
     *
     * @return null
     */
    public function extractMarketCode()
    {
        $this->marketCode = substr($this->accountNumber, 0, 3);
    }

    /**
     * Sets the users display name
     *
     * First name, middle initial (with period), then last name
     *
     * @return null
     */
    public function formatDisplayName()
    {
        $names = array();
        if ($this->firstName) {
            $names[] = $this->firstName;
        }
        if ($this->middleInitial) {
            $names[] = $this->middleInitial . '.';
        }
        if ($this->lastName) {
            $names[] = $this->lastName;
        }

        $this->displayName = implode(' ', $names);
    }

    /**
     * Formats the account number with hyphens
     *
     * @param string $acctNumber Account number to be formatted
     *
     * @return null
     */
    public static function formatAccountNumber($acctNumber)
    {
        $e = array();
        $r = preg_match('/^(\d{3})(\d{7})(\d+)$/', $acctNumber, $e);

        return  $e[1] . '-' . $e[2] . '-' . $e[3];
    }

    /**
     * strips out any non-digit from the account number
     *
     * @param string $acctNumber account number
     *
     * @return string account number
     */
    public static function stripAccountNumber($acctNumber)
    {
        // strip all non-digits
        return preg_replace('/[^\\d]/', '', trim($acctNumber));
    }

    /**
     * Formats the SSN with hyphens
     *
     * @param string $ssn Social Security Number
     *
     * @return string Formatted SSN
     */
    public static function formatSSN($ssn)
    {
        if ($ssn != '' && $ssn != null) {
            $e = array();
            $r = preg_match('/^(\d{3})(\d{2})(\d{4})$/', $ssn, $e);
    
            return $e[1] . '-' . $e[2] . '-' . $e[3];
        } else {
            return '';
        }
    }

    /**
     * Hides all but last 4 of social security number
     *
     * @param string $ssn       Social Security Number
     * @param string $mask_char Masking character - X by default
     *
     * @return string Masked Social security number
     */
    public static function maskSSN($ssn, $mask_char='X')
    {
        $e = array();
        $r = preg_match('/^\d{3}\-?\d{2}\-?(\d{4})$/', $ssn, $e);

        return str_repeat($mask_char, 3) . '-' . str_repeat($mask_char, 2)
            . '-' . $e[1];
    }

    /**
     * Sets $statusDisplay using M.Hoffenkamp logic
     *
     * @return null
     */
    protected function calculateDisplayStatus()
    {
        /* updated per Jamie Mayberry (2/1/2007) */
        switch($this->status) {
        case 'ACTIVE':
            $this->displayStatus = 'Active';
            break;
        case 'HOT':
            $this->displayStatus = 'Suspended';
            break;
        case 'DISC':
            $this->displayStatus = 'Disconnected';
            break;
        case '30 DAY':
            $this->displayStatus = 'Past Due';
            break;
        case '60 DAY':
            $this->displayStatus = '60 Day Past Due';
            break;
        case '90 DAY':
            $this->displayStatus = '90 Day Past Due';
            break;
        case '120 DAY':
            $this->displayStatus = '120 Day Past Due';
            break;
        case 'COLL':
            $this->displayStatus = 'Disconnected';
            break;
        case 'PREPAY':
            $this->displayStatus = 'Credit Balance';
            break;
        case 'STOP':
            $this->displayStatus = 'Past Due';
            break;
        default:
        }
    }

    /**
     * Calculates & massages the billing and due dates
     *
     * Note: This "formula" is most likely not written down anywhere. The dates
     * are derived from the last billed date. I'm not sure if this works for new
     * customers. Unsupported customer types are treated like prepay accounts.
     *
     * This is done because the billing dates that come from HO (the billing
     * system) are supposedly wrong.
     *
     * @return null
     */
    protected function calculateBillingAndDueDates()
    {
        // we set the billing and due dates depending upon customer type
        switch ($this->customerType) {
        case customerType::POSTPAY:
            $this->dueDate            = strtotime('-1 month', $this->billedDate);
            $this->billingPeriodStart = strtotime('-2 months +1 day',
            $this->billedDate);
            $this->billingPeriodEnd   = strtotime('-1 month', $this->billedDate);
            break;
        case customerType::PREPAY:
            $this->dueDate            = strtotime('-1 month', $this->billedDate);
            $this->billingPeriodStart = strtotime('-1 month +1 day',
            $this->billedDate);
            $this->billingPeriodEnd   = $this->billedDate;
            break;
        default:
            $this->dueDate            = $this->billedDate;
            $this->billingPeriodEnd   = $this->billedDate;
            $this->billingPeriodStart = strtotime('-1 month +1 day',
            $this->billedDate);
        }
    }

    /**
     * Calculates the balances
     *
     * It's important to point out that $currentCharges should be displayed, not
     * $currentBalance. Past due charges are also calculated here.
     *
     * @return null
     */
    protected function calculateBalances()
    {
        // balances are no longer calculated because they are coming from
        // the real time apl adapter already calculated.


        // this calculation was here before we we switched to RTAPL for
        // real time balances
        /*
        if ((
        $this->pastDueAmount = $this->balanceDue +
        $this->mtdAmount - $this->currentBalance) <= 0) {
            // past due is negative, for some odd reason
            $this->currentCharges = $this->balanceDue + $this->mtdAmount;
            $this->pastDueAmount  = 0;
        } else {
            $this->currentCharges = $this->currentBalance;
        }
        */
    }

    /**
     * Calculates the number of phone lines for the account
     *
     * @return int
     */
    public function calculateNumberOfLines()
    {
        foreach ($this->phones as $phone) {
            
        	if ($phone->hasDataUsage) {
                $this->dataUsageLines[] = $phone->formattedMDN;
        	}
        	
        	if ($phone->isData) {
                $this->broadbandLines[] = $phone->formattedMDN;
            } else if ($phone->ratePlanProductId == MPhone::PRODUCT_FAM_PLAN) {
                $this->familyLines[] = $phone->formattedMDN;
            } else {
                $this->wirelessLines[] = $phone->formattedMDN;
            }
        }
        $this->totalLinesOfService = count($this->phones);
    }

    /**
     * Returns the number of non-family plan voice lines for the account
     *
     * @deprecated
     *
     * @return int
     */
    public function calculateNumberOfPhoneLines()
    {
        return count($this->wirelessLines);
    }

    /**
     * Returns the list of non-family plan voice lines for the account
     *
     * @deprecated
     *
     * @return array
     */
    public function getListOfWirelessMDNs()
    {
        return $this->wirelessLines;
    }

    /**
     * Returns the number of broadband lines for the account
     *
     * @deprecated
     *
     * @return int
     */
    public function calculateNumberOfBroadbandLines()
    {
        return count($this->broadbandLines);
    }
    
	/**
     * Returns the number of data usage lines for the account
     *
     * @deprecated
     *
     * @return int
     */
    public function calculateNumberOfDataUsageLines()
    {    
        return count($this->dataUsageLines);
    }

    /**
     * Returns the list of broadband lines for the account
     *
     * @deprecated
     *
     * @return array
     */
    public function getListOfBroadbandMDNs()
    {
        return $this->broadbandLines;
    }
    
	/**
     * Returns the list of data usage lines for the account
     *
     * @deprecated
     *
     * @return array
     */
    public function getListOfDataUsageMDNs()
    {
        return $this->dataUsageLines;
    }

    /**
     * Returns the number of family plan lines for the account
     *
     * @deprecated
     *
     * @return int
     */
    public function calculateNumberOfFamilyPlanLines()
    {
        return count($this->familyLines);
    }

    /**
     * Returns the list of family plan lines for the account
     *
     * @deprecated
     *
     * @return array
     */
    public function getListOfFamilyPlanMDNs()
    {
        return $this->familyLines;
    }

    /**
     * Method to retrieve an account by its mdn.
     *
     * @param string $mdn mobile directory number
     *
     * @return MAccount Account Object
     */
    public static function getByMDN($mdn)
    {
        $c      = new CLookupAccountNumberByMDN();
        $c->MDN = $mdn;
        $c->execute();
        $accountNumber = $c->getResult();

        if (!empty($accountNumber)) {
            $account = MAccount::getByAccountNumber($accountNumber);
        }
        return $account;
    }

    /**
     * Method to retrieve an account by its account number.
     *
     * @param string $accountNumber REDACTED account number
     *
     * @return MAccount Account Object
     */
    public static function getByAccountNumber($accountNumber)
    {
        $c = REDACTED_Factory_Biller::instance()
                    ->getCommand(REDACTED_Factory_Biller::ACCOUNT_LOAD);

        $c->accountNumber = $accountNumber;
        $c->execute();
        $account = $c->getResult();

        $c                = new CLoadWebAccountByAdmin;
        $c->accountNumber = $accountNumber;
        $c->execute();
        $webAccount = $c->getResult();

        if ($account && $webAccount) {
            $account->importFrom($webAccount);
        }
        return $account;
    }

    /**
     * Method to return an array of MDN's
     *
     * @return array an array of mobile directory numbers associated with the
     * account
     */
    public function getFormattedMdnList()
    {
        $mdns = array();
        foreach ($this->phones as $phone) {
            $mdns[] = $phone->formattedMDN;
        }

        return $mdns;
    }

    /**
     * Returns whether or not the account has a family plan
     *
     * @return boolean do they have a family plan?
     */
    public function hasFamilyPlan()
    {
        if ($this->calculateNumberOfFamilyPlanLines() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Sets the pending work order property when there is no cached result
     *
     * @param LCommand $c          Command Object to Get Pending Orders For Customer
     * @param boolean  $honorCache Should we override the cache?
     *
     * @return bool do they have a pending work order
     */
    public function setPendingWorkOrder(LCommand $c, $honorCache = true)
    {
        if ($this->pendingWorkOrder == null || $honorCache === false) {
            $c->accountNumber = $this->accountNumber;
            $c->execute();
            $result                 = $c->getResult();
            $this->pendingWorkOrder =
                ($result['success'] && $result['hasPendingWorkOrders']);
        }

        LApplication::instance()->account->updateSSO();

        return $this->pendingWorkOrder;
    }

    public function getBBMMBalance()
    {
        $lastPaymentAmount = null;
        $lastPaymentDate   = null;
        $currentBalance    = null;

        try {
            $command                       = new REDACTED_Command_Service_InquireBucketTransactions();
            $command->billingAccountNumber = $this->accountNumber;

            $res               = $command->execute();
            $result            = $command->getResult();
            $response          = null;

            if ($res && $result['success']) {
                $response = $result['response']->InquireBucketTransactionsResponse;
            }

            if ($response && $response->BucketTransaction) {
                if (is_array($response->BucketTransaction)) {
                    foreach ($response->BucketTransaction as $bucket) {
                        if ($bucket->bucketID == 0) {
                            $bbmmBucket = $bucket;
                            break;
                        }
                    }
                } else {
                    $bbmmBucket = $response->BucketTransaction;
                }

                $currentBalance = $bbmmBucket->bucketBalance;

                if ($bbmmBucket->TransactionInfo) {
                    if (is_array($bbmmBucket->TransactionInfo)) {
                        $payment = $bbmmBucket->TransactionInfo[0];
                    } else {
                        $payment = $bbmmBucket->TransactionInfo;
                    }
                    $lastPaymentAmount = $payment->transactionAmount;
                    $lastPaymentDate   = strtotime($payment->transactionDateTime);
                }
            }
        } catch (LCommandExecutionException $e) {

        }

        return array(
            'lastPaymentAmount' => $lastPaymentAmount,
            'lastPaymentDate'   => $lastPaymentDate,
            'currentBalance'    => $currentBalance
        );
    }

    public function getFlexBalance($mdn=null)
    {
        $currentBalance = 0;

        try {
            
            $bucketTotal = 0;
            
            // Call inquireSubscriber
            $command = new REDACTED_Command_Service_InquireSubscriber;
            
            if (isset($mdn)) {
                $command->mdn = $mdn;
            }
            
            $res            = $command->execute();
            $result         = $command->getResult();
            $response       = null;
            
            if ($res && $result['success']) {
                $response = $result['response']->Subscriber->BucketBalance;
            }

            // Just use the totalBucketBalance instead of calculating it
            if (isset($response->totalBucketBalance)) {
                $bucketTotal = $response->totalBucketBalance;
            } else {
                if (is_array($response->bucketDetails)) {
                    foreach ($response->bucketDetails as $bucket) {
                        // Have to use regex since there a a bunch of different flex
                        // bucket names. (MRC Flex $5, MRC Flex $10, FLEX_DVI, etc.)
                        if ($bucket->bucketName == self::PIA_CARE ||
                                $bucket->bucketName == self::DVI ||
                                preg_match('/flex/i', $bucket->bucketName) ) {
    
                            $bucketTotal += $bucket->bucketBalance;
                        }
                    }
                } else {
                    $bucketTotal += $bucket->bucketBalance;
                }
            }

            $currentBalance = $bucketTotal;
            
            /*$command                       = new REDACTED_Command_Service_InquireBucketTransactions();
            $command->billingAccountNumber = $this->accountNumber;

            if (isset($mdn)) {
                $command->mdn = $mdn;
            } 

            $res               = $command->execute();
            $result            = $command->getResult();
            $response          = null;

            if ($res && $result['success']) {
                $response = $result['response']->InquireBucketTransactionsResponse;
            }

            if ($response && $response->BucketTransaction) {
                if (is_array($response->BucketTransaction)) {
                    foreach ($response->BucketTransaction as $bucket) {
                        // This is the BBMM Bucket. Until I have a valid
                        // Transaction posted I won't be able to find the
                        // id for PIA
                        if ($bucket->bucketID == 0) {
                            break;
                        }
                    }
                } else {
                    $bucket = $response->BucketTransaction;
                }

                $currentBalance = $bucket->bucketBalance;
            }*/
            
            
        } catch (LCommandExecutionException $e) {

        }
        
        if (isset($mdn)) {
            $mPhone                 =& $this->getPhoneFromMDN($mdn);
        } else {
            $mPhone                 =& $this->getPreferredPhone();
        }
        
        $mPhone->isBalanceAvailable = true;
        $mPhone->flexBucketBalance  = $currentBalance;
        $mPhone->loadedExternalData = true;
    }
    
    public function getCustomerTypeName()
    {
    	switch ($this->customerType) {
    		case customerType::BROADBANDMM:
    			return 'Broadband Mass Merchant';
    		case customerType::CBW:
    			return 'REDACTED By Week';
    		case customerType::EMPLOYEE:
    			return 'Employee';
    		case customerType::HARGRAY:
    			return 'Hargray';
    		case customerType::JUMP:
    			return 'Jump';
    		case customerType::PAYGO:
    			return 'PAYGo';
    		case customerType::POSTPAY:
    			return 'Postpay';
    		case customerType::PREPAY:
    		default:
    			if ($this->isAIMorPIA == self::AIM) {
    				return 'AIM';
    			} else {
    				return 'PIA';
    			}
    			
    	}
    }
    
    /**
     * method to returned the md45 hashed acct number
     * 
     * @return string
     */
    public function getHashedAccountNumber()
    {
        return md5($this->accountNumber);
    }
}