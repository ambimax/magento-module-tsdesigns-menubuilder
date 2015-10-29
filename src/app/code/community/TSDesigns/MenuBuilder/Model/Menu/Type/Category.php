<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Type_Category
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
class TSDesigns_MenuBuilder_Model_Menu_Type_Category extends TSDesigns_MenuBuilder_Model_Menu_Type_Abstract
{
	protected $_identifier = 'category';
	protected $_model = 'catalog/category';
	
	public function getUrl($addBaseUrl = false)
	{
		// If rewrite is disabled
		if( ! $this->getData('category_url')) {
			$this->setData('category_url', "catalog/category/view/id/{$this->getData('link_to_' . $this->_identifier)}/");
		}
	    return $addBaseUrl && $this->getData('category_url') 
					? $this->_addBaseUrl($this->getData('category_url')) 
					: $this->getData('category_url');
	}
	
	public function isActive()
	{
		return $this->getUrl() && $this->getData('category_is_active');
	}
	
	public function isValid()
	{
		if( ! $this->getData('link_to_' . $this->_identifier)) {
			$this->addError(Mage::helper('menubuilder')->__('No valid category set'));
		}
				
		return parent::isValid();
	}
	
	public function checkForWarnings()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    if($store = Mage::app()->getRequest()->getParam('store')) {
			$category = Mage::getModel($this->_model)->setStoreId(Mage::app()->getRequest()->getParam('store'))->load($this->getLinkId());
			if( ! $category->getIsActive()) {
				$this->addWarning(Mage::helper('menubuilder')->__('The linked category is not active in this store view and will not be displayed'));
			}
		} else {
			if( ! $this->_checkIsActiveInStores()) {
				$this->addWarning(Mage::helper('menubuilder')->__('The linked category is not set active on all store views'));
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
			$category = Mage::getModel($this->_model)->setStoreId($store->getId())->load($this->getLinkId());
			if( ! $category->getIsActive()) {
				return false;
			}
		}
		return true;
	}

	/*
	public function load()
	{
		if(is_null($this->_object)) {
			$this->_object = Mage::getModel($this->_model)->load($this->getLinkId());
			parent::_afterLoad();
		}
		return $this->_object;
	}
	*/
	
}