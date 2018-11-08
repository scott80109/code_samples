<?php
/* vim: set tabstop=4 shiftwidth=4: */
/**
 * Biller.php
 *
 * Biller Factory class to load the correct billing system based on the product
 * and market.
 *
 * PHP version 5.1 or greater
 */

class MyREDACTED_Factory_Biller
{
    const BILLER_CONVERGYS = 'CBS';
    const BILLER_HO        = 'HO';

    //TODO: These will likely change depending on
    //what the ESP Interface looks like
    const PRODUCT_BBMM  = 'BBMM';
    const PRODUCT_PAYGO = 'PAYGO';
    const PRODUCT_PIA   = 'PIA';

    const MEMO_ADD           = 'addMemo';
    const OFFERS_GET         = 'getOffers';
    const ESN_VALIDATE       = 'validateESN';
    const BILLCYCLE_GET      = 'getBillCycle';
    const ACCOUNT_LOAD       = 'loadAccount';
    const BILL_VIEW          = 'viewBill';
    const BILL_LIST          = 'listBill';
    const SMS_SEND           = 'sendSMS';
    const SMS_ALERT_SEND     = 'sendSMSAlert';
    const ACCOUNT_UPDATE     = 'accountUpdate';
    const PAYMENT_POST       = 'paymentPost';
    const PENDING_ORDERS_GET = 'getPendingOrders';
    const INQUIRE_TAX_QUOTE  = 'inquireTaxQuote';
    const CHANGE_PLB_STATUS  = 'changePLBStatus';

    const APPLICATION_CACHE_KEY = 'billingInfo';

    /**
     * Return the loaded instance using the Singleton pattern.
     *
     * @return MyREDACTED_Factory_Biller the active instance.
     */
    public static function instance()
    {
        static $instance = null;

        if ($instance !== null) {
            return $instance;
        } else {
            $instance = new self;
            return $instance;
        };
    }

    /**
     * Initialize the factory
     *
     * @return null
     */
    protected function __construct()
    {
        if (!LApplication::instance()->cache[self::APPLICATION_CACHE_KEY]) {
            $this->setDefaultBillerInfo();
        }
    }

    /**
     * Update the default billing information for the application.
     *
     *  @return null
     */
    protected function setDefaultBillerInfo()
    {
        $billerInfo = array(
            'product' => null,
            'market'  => null,
            'biller'  => null,
            'dirty'   => true
        );

        LApplication::instance()->cache[self::APPLICATION_CACHE_KEY] = $billerInfo;
    }

    /**
     * Get a specific command based on the command map for a particular biller.
     *
     * @param string $cmd the name of the command.
     *
     * @return string the command class which maps to the name.
     */
    public function getCommand($cmd)
    {
        $map           = $this->getCommandMap();
        $mappedCommand = $map[$cmd];
        if (!class_exists($mappedCommand)) {
            throw new BillerCommandFactoryException(
                'Could not find Command ['.$mappedCommand.']'
            );
        }
        $cmd = new $mappedCommand();

        return $cmd;
    }

    /**
     * Get a list of available commands based on the biller being used.
     *
     * @return array An associative array mapping command key to command name.
     */
    protected function getCommandMap()
    {
        if ($this->getBiller() == self::BILLER_CONVERGYS) {
            $map = array(
                /* Command constant => ESP Command */
                self::MEMO_ADD       => 'CAddNote',
                self::OFFERS_GET     => 'CGetBillingOffers',
                self::ESN_VALIDATE   => 'CValidateESN',
                self::BILLCYCLE_GET  => 'CGetBillCycleDates',
                self::ACCOUNT_LOAD   => 'CLoadAccount',
                self::BILL_VIEW      => 'CViewLegacyBill',
                self::BILL_LIST      => 'CGetBillSelectHTML',
                self::SMS_SEND       => 'CSendSMSESP',
                self::SMS_ALERT_SEND => 'CSendSMSAlertESP',
                self::ACCOUNT_UPDATE => 'CUpdateAccount',
                self::PAYMENT_POST   => 'CPostPayment',
                self::PENDING_ORDERS_GET    => 'CGetPendingOrders',
                self::INQUIRE_TAX_QUOTE     => 'CInquireTaxQuotation',
                self::CHANGE_PLB_STATUS     => 'CChangePLBStatus'
            );
        } elseif ($this->getBiller() == self::BILLER_HO) {
            $map = array(
                /* Command constant => HO Command */
                self::MEMO_ADD       => 'CAddMemo',
                self::OFFERS_GET     => 'CGetPARCOffers',
                self::ESN_VALIDATE   => 'CValidateESNCSP',
                self::BILLCYCLE_GET  => 'CGetHOBillCycle',
                self::ACCOUNT_LOAD   => 'CLoadHOAccount',
                self::BILL_VIEW      => 'CViewBill',
                self::BILL_LIST      => 'CGetListOfBills',
                self::SMS_SEND       => 'CSendSMS',
                self::SMS_ALERT_SEND => 'CSendSMSAlert',
                self::ACCOUNT_UPDATE => 'CChangePersonalInformation',
                self::PAYMENT_POST   => 'CPostPayment', // Using the same for both billers
                self::PENDING_ORDERS_GET    => 'CGetMyREDACTEDPendingWorkOrdersForCustomer',
                self::INQUIRE_TAX_QUOTE     => 'CGetTaxForAmount',
                self::CHANGE_PLB_STATUS     => 'CChangePLBStatusHO'
            );
        } else {
            throw new MyREDACTEDFactoryBillerException("Error retrieving command - biller was not set");
        }
        return $map;
    }

    /**
     * Return if the biller is up to date or not.
     *
     * @return boolean true if dirty, false if clean.
     */
    public function isBillerDirty()
    {
        return LApplication::instance()->cache[self::APPLICATION_CACHE_KEY]['dirty'];
    }

    /**
     * Get the appropriate biller for the product and market that have been set.
     * Uses caching to improve efficiency if the product and market have not
     * changed.
     *
     * @return string the biller to use.
     */
    public function getBiller()
    {
        /* DO NOT CHECK THIS IN!!!!!!!!! */
        return 'CBS';
        //return 'HO';
        //////////////////////////////////
        
        
        
        
        $cache = LApplication::instance()->cache;

        //Allow the biller to be overridden for testing
        //outside of ESP's configuration as far as trial rollout
        //routing is concerned
        $billerInfo = $cache[self::APPLICATION_CACHE_KEY];

        $billerVal = null;
        
        //allow config overrides in non-prod environments
        if (!PRODUCTION) {
            
            $product   = $billerInfo['product'];
            $default   = null;
            
            switch($product) {
            case self::PRODUCT_PIA:
                $key       = MConfigOverrides::BILLER_PIA;
                $billerVal = MConfigOverrides::getValueByKey($key, $default);
                break;
            case self::PRODUCT_PAYGO:
                $key       = MConfigOverrides::BILLER_PAYGO;
                $billerVal = MConfigOverrides::getValueByKey($key, $default);
                break;
            case self::PRODUCT_BBMM:
                $key       = MConfigOverrides::BILLER_BBMM;
                $billerVal = MConfigOverrides::getValueByKey($key, $default);
                break;
            }
        }
     
        if (isset($billerVal)) {
            //An override is provided, use that.
            $billerInfo['biller'] = $billerVal;
            $billerInfo['dirty']  = false;

            //Update the cached biller information.
            LApplication::instance()
                ->cache[self::APPLICATION_CACHE_KEY] = $billerInfo;
        } else {
            //if we changed market or product, get the new biller information.
            if (self::isBillerDirty()) {
                $c 	        = new CInquireBillingSystem();
                $c->product = $cache[self::APPLICATION_CACHE_KEY]['product'];
                $c->market 	= $cache[self::APPLICATION_CACHE_KEY]['market'];
                $success    = $c->execute();

                if ($success) {
                    $result = $c->getResult();

                    $billerInfo = $cache[self::APPLICATION_CACHE_KEY];
                    $biller     = $result['biller'];
                    //Translate the biller from the service to an internal
                    //billing code
                    if ($biller == 'HO') {
                        $billerInfo['biller'] = self::BILLER_HO;
                    } else if ($biller == 'CBS') {
                        $billerInfo['biller'] = self::BILLER_CONVERGYS;
                    }

                    $billerInfo['dirty'] = false;

                    LApplication::instance()
                        ->cache[self::APPLICATION_CACHE_KEY] = $billerInfo;
                }
            }
        }

        return LApplication::instance()
            ->cache[self::APPLICATION_CACHE_KEY]['biller'];
    }

    /**
     * Set the market for the purchase and update the dirty flag if it has
     * changed.
     *
     * @param int $marketId The new market id for the transaction.
     *
     * @return null
     */
    public function setBillerMarket($marketId)
    {
        $billerInfo = LApplication::instance()->cache[self::APPLICATION_CACHE_KEY];

        if ($billerInfo['market'] == $marketId) {
            return;
        }

        $billerInfo['market']	= $marketId;
        $billerInfo['dirty'] 	= true;

        LApplication::instance()->cache[self::APPLICATION_CACHE_KEY] = $billerInfo;
    }

    /**
     * Set the type of product being purchased and update the dirty flag if it has
     * changed.
     *
     * @param string $product the new product type
     *
     * @return null
     */
    public function setBillerProduct($product)
    {
        $billerInfo = LApplication::instance()->cache[self::APPLICATION_CACHE_KEY];

        if ($billerInfo['product'] == $product) {
            return;
        }

        $billerInfo['product'] = $product;
        $billerInfo['dirty']   = true;

        LApplication::instance()->cache[self::APPLICATION_CACHE_KEY] = $billerInfo;
    }

    /**
     * Sets the biller directly. This is done in conditions that the biller is
     * provided directly, or a particular product type dictates that the biller is
     * always one.
     *
     * @param string $biller The biller to set and save
     */
    public function setBiller($biller)
    {
        if ($biller != self::BILLER_CONVERGYS && $biller != self::BILLER_HO ) {
            throw new MyREDACTEDFactoryBillerException('An invalid biller was' .
                'passed to setBiller');
        }

        $billerInfo = LApplication::instance()->cache[self::APPLICATION_CACHE_KEY];

        $billerInfo['product'] = null;
        $billerInfo['market']  = null;
        $billerInfo['dirty']   = false;
        $billerInfo['biller']  = $biller;

        LApplication::instance()->cache[self::APPLICATION_CACHE_KEY] = $billerInfo;
    }

    /**
     * Sets the product and market based upon the data contained within
     * the shopping cart
     *
     * @param XCART_CartType $cart
     */
    public function setBillerFromCart($cart)
    {
        // If we have an account number, we shouldn't change the 
        // biller b/c it was set when the user logged in.
        // This will prevent a person from being split between two billing systems
        if (!empty($cart->accountNumber)) {
            return;
        }

        $packages = array();

        if(
            $cart->containsPackage(XCART_PackageType::NEW_ACTIVATION_TYPE)
            || $cart->containsPackage(XCART_PackageType::PURCHASE_ACCESSORIES_TYPE)
            || $cart->containsPackage(XCART_PackageType::CHANGE_FEATURES_TYPE)
            || $cart->containsPackage(XCART_PackageType::CHANGE_PLAN_TYPE)
            || $cart->containsPackage(XCART_PackageType::ADD_A_LINE_TYPE)
            || $cart->containsPackage(XCART_PackageType::UPGRADE_PHONE_TYPE)
            || $cart->containsPackage(XCART_PackageType::CUSTOMER_ADD_A_LINE)
        ) {
            $this->setBillerProduct(self::PRODUCT_PIA);
        } elseif (
            $cart->containsPackage(XCART_PackageType::PAYGO_ACTIVATION)
        ) {
            $this->setBillerProduct(self::PRODUCT_PAYGO);
        } elseif (
            $cart->containsPackage(XCART_PackageType::BBMM_ACTIVATION)
            || $cart->containsPackage(XCART_PackageType::BBMM_CHANGE_PLAN_TYPE)
        ) {
            $this->setBillerProduct(self::PRODUCT_BBMM);
        } else {
            try {
                throw new MyREDACTEDFactoryBillerException("Unknown package " .
                    "type passed to the Factory");
            } catch (Exception $e) {

            }
        }
        
        $packages = $cart->packages;
        
        if (count($packages) > 0) {
            foreach ($packages as $package) {
                //accessory only doesn't have a packageMarket
                if ($package->packageType == XCART_PackageType::PURCHASE_ACCESSORIES_TYPE) {
                    if (!empty($cart->billingAddress->zip->zip)) {
                        $c      = new CGetMarketFromZip();
                        $c->zip = $cart->billingAddress->zip->zip;
                        $res    = $c->execute();
                        $result = $c->getResult();
                        if ($res && $result['success']) {
                            $this->setBillerMarket($result['market_code']);
                        }
                    }
                } else {
                    if (!empty($package->packageMarket)) {
                        $this->setBillerMarket($package->packageMarket);
                        break;
                    }
                }
            }
        } else {
            try {
                throw new MyREDACTEDFactoryBillerException("The cart did not " .
                    "contain any valid packages to set the market with.");
            } catch (Exception $e)
            {
            }
        }
    }
}