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
class Sumankcdotcom_Esewa_PaymentController extends Mage_Core_Controller_Front_Action {
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		$this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','esewa',array('template' => 'sumankcdotcom_esewa/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		if($response = $this->getRequest()->getParams()) {
			$validate = $response['q'];
			$orderId = $response['oid'];
			
			if($validate=='su') {
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($orderId);
				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Payment with Esewa authorized.');
				$order->addStatusHistoryComment("Esewa payment reference id : ".$response['refId'], false);

				$order->sendNewOrderEmail();
				$order->setEmailSent(true);

				$order->save();
				$this->invoiceorder($order);
				Mage::getSingleton('checkout/session')->unsQuoteId();
				
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
			}
			else {
				// There is a problem in the response we got
				$this->cancelAction();
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			}
		}
		else
			Mage_Core_Controller_Varien_Action::_redirect('');
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Esewa has declined the payment.')->save();
			}
        }
	}
	/*Invoice an order*/
	public function invoiceorder($order){
		try{
			if($order->canInvoice() && $order->getIncrementId()){

				    $items = array();
				    foreach ($order->getAllItems() as $item) {
				    	$items[$item->getId()] = $item->getQtyOrdered();
				    }

				$invoiceId=Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(),$items,null,false,true);
				Mage::getModel('sales/order_invoice_api')->capture($invoiceId);
			}
			return true;
		}catch(Mage_Core_Exception $e) {}
	}
}