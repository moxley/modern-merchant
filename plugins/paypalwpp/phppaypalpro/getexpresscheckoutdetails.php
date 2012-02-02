<?php

/**
 * GetExpressCheckoutDetails Object
 *
 * This class is used to perform the GetExpressCheckoutDetails operation
 * 
 * @author Israel Ekpo <perfectvista@users.sourceforge.net>
 * @copyright Copyright 2007, Israel Ekpo
 * @license http://phppaypalpro.sourceforge.net/LICENSE.txt BSD License
 * @version 0.2.0
 * @package ExpressCheckout
 * @filesource
 */


/**
 * Used to invoke the GetExpressCheckoutDetails Operation
 * 
 * @author Israel Ekpo <perfectvista@users.sourceforge.net>
 * @copyright Copyright 2007, Israel Ekpo
 * @license http://phppaypalpro.sourceforge.net/LICENSE.txt BSD License
 * @package ExpressCheckout
 */
final class GetExpressCheckoutDetails extends PaypalAPI implements OperationsTemplate
{   
    /**
     * Message Sent to the Webservice
     *
     * @var array
     * @access private
     */
    private $apiMessage;
    
    /**
     * Token value returned from setExpressCheckout
     *
     * This is a 20 single-byte characters timestamped token, the value of which was returned by SetExpressCheckoutResponse
     * 
     * @var string
     * @access private
     */
    private $tokenValue;
    
    
    /**
     * Prepares the message to be sent
     *
     * This method prepares the message to be sent to the 
     * Paypal Webservice
     * 
     * @access public
     * @param string $Token
     */
    public function setParams($Token)
    {
        $this->tokenValue = $Token;
    }
    
    /**
     * Executes the Operation
     *
     * Prepares the final message and the calls the Webservice operation. If it is successfull the response is registered
     * and the OperationStatus is set to true, otherwise the Operation status will be set to false and an Exception of the type
     * soapFault will be registered instead.
     * 
     * @throws SoapFault
     * @access public
     */
    public function execute()
    {
        try
        {
            $this->apiMessage['Version'] = API_VERSION;
            
            $this->apiMessage['Token']   = $this->tokenValue;
            
            $this->apiMessage = array('GetExpressCheckoutDetailsRequest' => $this->apiMessage);
            
            $this->apiMessage = array($this->apiMessage);
            
            parent::registerAPIResponse(PayPalBase::getSoapClient()->__soapCall('GetExpressCheckoutDetails', $this->apiMessage, null, PayPalBase::getSoapHeader()));
            
            PaypalBase::setOperationStatus(true);           
        }
        
        catch (SoapFault $Exception)
        {
            parent::registerAPIException($Exception);
            
            PaypalBase::setOperationStatus(false);
        }
    }
}
?>
