<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Type_Abstract
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
abstract class TSDesigns_MenuBuilder_Model_Menu_Type_Abstract 
	extends Varien_Object 
	//implements TSDesigns_MenuBuilder_Model_Menu_Type_Interface
{	
	protected $_identifier = null;
	protected $_model = null;
	protected $_isLoaded = false;
	protected $_object = null;
	
	protected $_valid = false;
	protected $_errors = array();
	
	
	public function isActive()
	{
		return false;
	}
	
	public function isValid()
	{
		if( ! $this->getData('link_to')) {
			$this->addError(Mage::helper('menubuilder')->__('Link type is not defined'));
		}
		return (bool) ( ! $this->hasErrors());
	}
	
	public function checkForWarnings()
	{
		return $this;
	}
	
	public function getUrl($addBaseUrl = false)
	{
		return '';
	}
	
    protected function _addBaseUrl($url = null)
    {
    	return $url ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $url : null;
    }
    
	/**** Error handling ******************/
	public function addError($msg = null)
	{
		return $this->setData('errors', array_merge(array($msg), $this->getData('errors') ? $this->getData('errors') : array()));
	}
	
	public function addWarning($msg = null)
	{
		return $this->setData('warnings', array_merge(array($msg), $this->getData('warnings') ? $this->getData('warnings') : array()));
	}
	
	public function load()
	{
		if(is_null($this->_object)) {
			$this->_object = Mage::getModel($this->_model)->load($this->_identifier);
			$this->_afterLoad();
		}
		return $this->_object;
	}
	
	protected function _afterLoad()
	{
		$this->addData($this->_object->getData());
		return $this;
	}
	
	public function getLinkId()
	{
		return $this->getData('link_to_' . $this->_identifier);
	}
	
	/*
	public function getObject()
	{
		if(is_null($this->_object)) {
			$this->_object = $this->load();
		}
		
		return $this->_object;
	}

	
    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     *
    public function __call($method, $args)
    {
    	if(method_exists($this->getObject(), $method)) {
    		return $this->getObject()->$method($args);
    	}
    	return parent::__call($method, $args);
	}
	*/
}