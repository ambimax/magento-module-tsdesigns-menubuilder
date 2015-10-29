<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Type_Product
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Licence that is bundled with 
 * this package in the file LICENSE.txt. It is also available through 
 * the world-wide-web at this URL: http://www.tsdesigns.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tsdesigns.de so we can send you a copy immediately.
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * versions in the future. If you wish to customize Magento or this extension for your
 * needs please refer to http://www.magentocommerce.com or http://www.tsdesigns.de
 * for more information.
 * 
 *
 * @category TSDesigns
 * @package TSDesigns_MenuBuilder
 * @author Tobias Schifftner, TSDesigns
 * @license http://www.tsdesigns.de/license
 * @copyright This software is protected by copyright, (c) 2011 TSDesigns.
 * @version 1.6.0 - 2011-10-21 10:31:26
 *
 */
class TSDesigns_MenuBuilder_Model_Menu_Type_Product extends TSDesigns_MenuBuilder_Model_Menu_Type_Abstract
{
	protected $_identifier = 'product';
	protected $_model = 'catalog/product';
	
	public function getUrl($addBaseUrl = false)
	{
		// If rewrite is disabled
		if( ! $this->getData('product_url')) {
			$this->setData('product_url', "catalog/product/view/id/{$this->getData('product_id')}/");
		}
	    return $addBaseUrl && $this->getData('product_url') 
					? $this->_addBaseUrl($this->getData('product_url')) 
					: $this->getData('product_url');
	}
			
	public function isActive()
	{
		return $this->getUrl() && $this->getData('product_is_active');
	}
	
	public function isValid()
	{
		if( ! $this->getData('link_to_' . $this->_identifier)) {
			$this->addError(Mage::helper('menubuilder')->__('No valid product set'));
		}
		
		$product = Mage::getModel($this->_model)->loadByAttribute('sku', $this->getLinkId());
		if( ! ($product instanceof Mage_Catalog_Model_Product) || is_null($product->getId())) {
			$this->addError(Mage::helper('menubuilder')->__('Invalid Product Sku'));
		}
		
		return parent::isValid();
	}
	
	public function checkForWarnings()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    
		if($store = Mage::app()->getRequest()->getParam('store')) {
			$product = Mage::getModel($this->_model)->setStoreId(Mage::app()->getRequest()->getParam('store'))->loadByAttribute('sku', $this->getLinkId());
			if( ! $product->getIsActive()) {
				$this->addWarning(Mage::helper('menubuilder')->__('The linked product is not active in this store view and will not be displayed'));
			}
		} else {
			if( ! $this->_checkIsActiveInStores()) {
				$this->addWarning(Mage::helper('menubuilder')->__('The linked product is not set active on all store views'));
			}
		}
		
		return parent::checkForWarnings();
	}
	
	protected function _checkIsActiveInStores()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    foreach(Mage::app()->getStores(true) as $store) {
			$product = Mage::getModel($this->_model)->setStoreId($store->getId())->loadByAttribute('sku', $this->getLinkId());
			
			if( ! ($product instanceof Mage_Catalog_Model_Product) || ! $product->getIsActive()) {
			//if( ! $product->getIsActive()) {
				return false;
			}
		}
		return true;
	}

	
	/*
	public function load()
	{
		if(is_null($this->_object)) {
			$this->_object = Mage::getModel($this->_model)->loadBySku($this->_identifier);
			parent::_afterLoad();
		}
		return $this->_object;
	}
	*/
}