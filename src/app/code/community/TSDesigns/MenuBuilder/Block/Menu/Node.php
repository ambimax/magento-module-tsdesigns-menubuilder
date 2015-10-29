<?php
/**
 * TSDesigns_MenuBuilder_Block_Menu_Node
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
class TSDesigns_MenuBuilder_Block_Menu_Node extends Mage_Core_Block_Template
{
	protected $_template = 'menubuilder/menu/node.phtml';
	protected $_element = array();
	protected $_isActive = false;
	
	public function __construct()
	{		
		parent::__construct();
		$this->setTemplate($this->_template);
	}
/*	
    public function getCacheLifetime()
    {
        return Mage::getStoreConfig('menubuilder/settings/cache_enable') ? (int) Mage::getStoreConfig('menubuilder/settings/cache_lifetime') : false;
    }
    
    public function getCacheTags()
    {
        return array(
        	TSDesigns_MenuBuilder_Model_Menu::CACHE_TAG,
        	Mage_Catalog_Model_Product::CACHE_TAG,
        	Mage_Catalog_Model_Category::CACHE_TAG,
        	Mage_Cms_Model_Block::CACHE_TAG, 
        	Mage_Core_Model_Store_Group::CACHE_TAG,
        	'block_html'
        );        
    }
 
    /**
     * Retrieve Key for caching block content
     *
     * @return string
     *
    public function getCacheKey()
    {
        return $this->getNameInLayout()
        	. '_' . Mage::app()->getStore()->getId()
            . '_' . Mage::getDesign()->getPackageName()
            . '_' . Mage::getDesign()->getTheme('template')
            . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
            . '_' . md5($this->getTemplate());
    }
*/	
	
	public function getElementOptions($element, $type = null)
	{
		if( ! isset($this->_element[$element]) || ($type && ! isset($this->_element[$element][$type]))) {
			return '';
		}
		
		$options = $type ? $this->_element[$element][$type] : $this->_element[$element];
		
		$html = array();
		if( ! $type) {
			foreach($options as $type => $options) {
				$html[] = $this->getElementOptions($element, $type); 
			}
			return count($html) ? ' ' . implode(' ', $html) : '';	
		}
		
		return $this->_parseElementOptions($type, $options);
	}
	
	protected function _parseElementOptions($type = null, $options = null)
	{
		if( ! $type || ! $options || ! is_array($options) || ! count($options)) {
			return '';
		}
		
		$_options = trim(implode(' ', $options));			
		switch($type) {
			case 'style':
				return 'style="' . $_options . '"';
			case 'class':
				return 'class="' . $_options . '"';
			case 'attribute':
				return $_options; 
		}
		return '';
	}
	
	
	public function addElementOptions($type = null, $element = null, array $options = array()) 
	{
		if(is_null($type)) {
			return $this;
		}
		
		if(is_array($type)) {
			foreach($type as $element => $_options) {
				foreach($_options as $type => $options) {
					$this->addElementOptions($type, $element, $options);
				}
			}
			return $this;
		}
		
		if( ! isset($this->_element[$element])) {
			$this->_element[$element] = array();
		}
		if( ! isset($this->_element[$element][$type])) {
			$this->_element[$element][$type] = array();
		}
		
		foreach($options as $name => $value) {
			if($this->_isValid($element, $type, $name, $value)) {
				$this->_element[$element][$type][] = $this->_getValue($type, $name, $value);
			}
		}
		return $this;
	}
	
	public function hasElementOptions($element) 
	{
		return (bool) isset($this->_element[$element])	&& count($this->_element[$element]);
	}
	
	protected function _isValid($element, $type, $name = null, $value = null)
	{
		if(is_null($value) || empty($value) || $value == '') {
			return false;
		}
		
		if(in_array($value, $this->_element[$element][$type])) {
			return false;
		}
		
		switch($type) {
			case 'style':
				return (bool) ( ! empty($name) && '#' != $value);
			case 'class':
				return (bool) ($value);
			case 'attribute':
				return (bool) ($name && $value);
		}
		return false;
	}
	
	protected function _getValue($type, $name = null, $value = null)
	{
		switch($type) {
			case 'style':
				return "{$name}:{$value};";
			case 'class':
				return $value;
			case 'attribute':
				return "{$name}=\"{$value}\"";
		}
		return false;
		
	}
	
	protected function _toHtml()
	{
		if( ! $this->isActive()) {
			return null;
		}
		return parent::_toHtml();
	}
 	
	public function isActive()
	{
		return (bool) $this->_isActive;
	}
	
	public function setIsActive($bool = true)
	{
		$this->_isActive = (bool) $bool;
		return $this;
	}
	
}