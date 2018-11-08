<?php
/**
 * CancelAccountQuotes
 *
 * Controller for viewing and paying your bill
 *
 * PHP version 5.1 or greater
 *
 */
class MyREDACTED_Command_CancelAccountQuotes extends LCommand
{
    /* Constants */
    const STATUS_NEW                   = 'N';
    const STATUS_PENDING_CONFIGURATION = 'P';
    const STATUS_CONFIGURED            = 'F';
    const STATUS_QUOTED                = 'Q';
    const STATUS_EXPIRED               = 'E';
    const STATUS_PENDING_APPROVAL      = 'A';
    const STATUS_CANCELLED             = 'C';
    const STATUS_REJECTED              = 'R';
    const STATUS_COMPLETED             = 'X'; 
    
    /* Properties */
    /**
     * @var billingAccountNumber string
     */
    protected $billingAccountNumber;
    

    /* Methods */
    /**
     * Main execution method. Must set billingAccountNumber before 
     * calling execute()
     * 
     * @return boolean true or false.
     */
    protected function doExecute()
    {
        // Get billing quote
        $orders = $this->getBillingOrders();

        if (!is_array($orders)) {
            if (is_null($orders->quoteStatus)) {
                return false;
            }
        }
        if ($orders == false) {
            return false;
        }

        foreach ($orders as $order) {
            // Create a quote
            $quote = new MQuote;

            switch ($order->quoteStatus) {
            case self::STATUS_CANCELLED:
            case self::STATUS_REJECTED:
            case self::STATUS_COMPLETED:
                break;
            default:
                // Cancel the order
                if (!is_null($order->billingQuoteNumber)) {
                    $quote->quoteId = $order->billingQuoteNumber;
                    $result = $quote->cancel();                
                }    
                break;
            }
        }
        return true;
    }

    /**
     * Returns array of billing orders for account.
     *
     * @return array
     */
    public function getBillingOrders()
    {
        try {
            $comm = new MyREDACTED_Command_Service_InquireBillingQuote();
    
            $comm->billingAccountNumber = $this->billingAccountNumber;
            $comm->maxResultsNumber = "10";
            $comm->exactMatch = true;
            
            $response = $comm->execute();
            $result = $comm->getResult();
        } catch (ESPTypeValidationException $e) {
            $this->result['success'] = false;
            $this->result['error']   = 'The service failed validation. ' . 
                                       $e->getMessage();
            throw new InquireBillingQuoteStopException($this->result['error']);
        } catch (Exception $e) {
            throw new InquireBillingQuoteStopException(
                'Unable to retrieve billing quotes.');
        }

        if ($result['success'] == true) {
            return $result['response']->BillingOrderInfo;
        }
        return false;        
    }
}