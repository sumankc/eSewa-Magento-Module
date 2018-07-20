<?php
/**
 * Sumankcdotcom_Esewa extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @package        Sumankcdotcom_Esewa
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
class Sumankcdotcom_Esewa_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'esewa';
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('esewa/payment/redirect', array('_secure' => true));
	}
}