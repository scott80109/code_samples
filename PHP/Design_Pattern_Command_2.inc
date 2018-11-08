<?php
/* vim: set tabstop=4 shiftwidth=4*/
/**
 * CAddMemo
 *
 * PHP version 5.1 or greater
 *
 */
class CAddMemo extends LCommand
{
    /**
     * @var string $accountNumber (HO Account Number)
     */
    protected $accountNumber;
    /**
     * The $memo field needs to be a XH2O_MemoCSP object if the memo type must be
     * specified.  If it can be the default of "S", then a string can be
     * passed in for the memo.
     *
     * @var mixed $memo
     */
    protected $memo;

    /**
     * Adds a memo to an HO account
     *
     * @return boolean
     */
    protected function doExecute()
    {

        if (!LApplication::instance()->isBusinessHours()) {
            throw new CAddMemoBusinessHoursException(
                'Unable to add memo to account outside of business hours'); 
        }

        // To make the command a little more friendly if I don't care what memo type.
        if (is_string($this->memo)) {
            $this->memo = new XH2O_MemoCSP('S', $this->memo);
        }

        try{
            $CSP      = new cspOutlet(cspOutlet::ADAPTER_HO, 
                cspOutlet::CSP_VERSION3);
            $response = $CSP->addMemo(new XH2O_AddMemoRequestMsg($CSP->sessionId,
            $this->accountNumber,
            $this->memo
            ));
                
            $this->result = array( 'success' => 
                $response->AddMemoResponseMsg->result);
            // if we're here it means that we have a response from the adapter
            return true;
        } catch (Exception $e) {
            $this->result = array(
                    'success'=>false,
                    'displayMessage'=>"Unable to add memo to account"
                    );
                    throw new CAddMemoFailedException(
                'Unable to add memo to account: [' . $e . ']'
                );
                return false;
        }

    }

    /**
     * Adds several memos at once
     *
     * @param array  $memos         Array of memos
     * @param string $accountNumber Account number to add memos to
     * 
     * @return null
     */
    public static function addInBulk($memos, $accountNumber)
    {
        $ms = array();
        $m  = $o = 'MyREDACTED.com';
        foreach ($memos as $memo) {
            if (strlen($m . ';' . $memo) > 240) {
                $ms[] = $m;
                $m    = $o;
            }
            $m .= ';' . $memo;
        }
        // get the straggler
        if ($m) {
            $ms[] = $m;
        }

        try{
            foreach ($ms as $memo) {

                $c = new self;
                // must use __set(), otherwise it will not go through setter
                // because it's within the class context
                $c->__set('memo', $memo);
                $c->__set('accountNumber', $accountNumber);
                $c->execute();
            }
        } catch(Exception $e) {
            // forget any failures
        }


    }
}

