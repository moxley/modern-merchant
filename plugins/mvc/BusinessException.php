<?php
/**
 * @package mvc
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 * Convenience class that can be used when a business rule is violated.
 *
 * Normally business rules are added to the model's errors during validation. In
 * cases where any single validation error should stop validation, and there are
 * many possible validation error types, it may be more convenient to throw a
 * <code>mvc_BusinessException</code> to stop further validation and process the
 * error at a single location. See {@link paypal_PayPal::handleIpn()} for an example.
 *
 * When caught, the exception's message should be added to the
 * object's model errors (see {@link mvc_Model::addError()}).
 */
class mvc_BusinessException extends Exception {
    
}
