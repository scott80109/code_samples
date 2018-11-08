<?php

class REDACTED_Command_EnrollInABP extends LCommand
{
    /**
     * Payment device object.  Should be either a
     * REDACTED_Model_Payment_BankAccountToken or a
     * REDACTED_Model_Payment_CreditCardToken object
     * @var mixed
     */
    protected $device;

    /**
     * Billing system account number
     * @var string
     */
    protected $accountNumber;

    /**
     * Customer's phone number
     * @var string
     */
    protected $mdn;

    /**
     * Customer's bill cycle date
     * @var int (1-28)
     */
    protected $billCycle;

    /**
     * Joint venture code for customer's account
     *
     * @var string
     */
    protected $jointVentureCode;

    /**
     * Optional customer ID - only needed for CBS
     *
     * @var string
     */
    protected $customerId;

    /**
     * Makes the call using the ESP service to enroll a customer in ABP. The code
     * must provide the customer's billing account number and payment device
     * object.  Please @see REDACTED_Service_ManageABP for the response code
     * values.  Please @see REDACTED_Model_Payment_CreditCardToken for credit card
     * payment device information and please
     * @see REDACTED_Model_Payment_BankAccountToken for e-check payment device info.
     *
     * @return boolean
     */
    public function doExecute()
    {
        $this->result            = array();
        $this->result['success'] = false;

        if (!$this->everyRequiredPropertyHasBeenSet()) {
            throw new RequiredPropertiesNotSetException('You must provide all the required properties.');
        }

        $service = new REDACTED_Service_ManageABP();
        $service->setAccountNumber($this->accountNumber);
        $service->setMDN($this->mdn);
        $service->setBillCycle($this->billCycle);
        $service->setAccountType($this->accountType);
        $service->setJointVentureCode($this->jointVentureCode);
       
        if ($this->device instanceof REDACTED_Model_Payment_CreditCardToken) {        
            $service->setAddress($this->device->chargeCardBillingAddress);
        } else {
            $service->setAddress($this->device->customerAddress);
        }

        if (isset($this->customerId)) {
            $service->setCustomerId($this->customerId);
        }

        $result = $service->doEnrollment($this->device);

        $response = $result->ABPManagementResponse;

        $this->result['success'] =
            ($response->VestaResponse->responseCode ==
            REDACTED_Service_ManageABP::RESPONSE_CODE_SUCCESS);

        $this->result['responseCode']  = $response->VestaResponse->responseCode;
        $this->result['responseText']  = $response->VestaResponse->responseText;
        $this->result['accountNumber'] = $response->accountNumber;

        $this->result['espResponseCode'] = $result->Response->code;
        $this->result['espDescription']  = $result->Response->description;
        $this->result['errorMessage'] =
            $service->getErrorMessage($this->result['responseCode']);

        return true;
    }

    /**
     * Used to ensure that the code has provided a payment device and and account
     * number before we attempt to call the service.
     *
     * @return array
     */
    public function getRequiredProperties()
    {
        return array('device', 'accountNumber', 'mdn', 'billCycle',
            'jointVentureCode');
    }
}