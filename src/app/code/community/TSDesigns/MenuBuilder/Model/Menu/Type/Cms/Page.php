<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Type_Cms_Page
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
class TSDesigns_MenuBuilder_Model_Menu_Type_Cms_Page extends TSDesigns_MenuBuilder_Model_Menu_Type_Abstract
{
	protected $_identifier = 'cms_page';
	protected $_model = 'cms/page';
	
	public function getUrl($addBaseUrl = false)
	{
		return $addBaseUrl && $this->getData('cms_page_url') 
					? $this->_addBaseUrl($this->getData('cms_page_url')) 
					: $this->getData('cms_page_url');
	}
			
	public function isActive()
	{
		return $this->getUrl() && $this->getData('cms_page_is_active');
	}
	
	public function load()
	{
		$id = $this->getData('link_to_cms_page');
		return Mage::getModel('cms/page')->load($id);
	}
	
	public function isValid()
	{	    
		return parent::isValid();
	}
	
	public function checkForWarnings()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    if($store = Mage::app()->getRequest()->getParam('store')) {
			$cmsPage = Mage::getModel($this->_model)->setStoreId(Mage::app()->getRequest()->getParam('store'))->load($this->getLinkId());
			if( ! $cmsPage->getIsActive()) {
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
			$cmsPage = Mage::getModel($this->_model)->setStoreId($store->getId())->load($this->getLinkId());
			if( ! $cmsPage->getIsActive()) {
				return false;
			}
		}
		return true;
	}
	
}