<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Source_Position
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
class TSDesigns_MenuBuilder_Model_Menu_Source_Position extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const POSITION_NOWHERE 					= 'nowhere';
	//const POSITION_PAGE_TOP 				= 'page-top';
	const POSITION_MENU_TOP 				= 'menu-top';
	const POSITION_MENU_BOTTOM 				= 'menu-bottom';
	const POSITION_CONTENT_TOP 				= 'content-top';
	const POSITION_SIDEBAR_RIGHT_TOP 		= 'sidebar-right-top';
	const POSITION_SIDEBAR_RIGHT_BOTTOM 	= 'sidebar-right-bottom';
	const POSITION_SIDEBAR_LEFT_TOP 		= 'sidebar-left-top';
	const POSITION_SIDEBAR_LEFT_BOTTOM 		= 'sidebar-left-bottom';
	const POSITION_FOOTER_TOP 				= 'footer-top';
	const POSITION_FOOTER_BOTTOM			= 'footer-bottom';
	const POSITION_PAGE_BOTTOM 				= 'page-bottom';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
     		self::POSITION_NOWHERE 					=> Mage::helper('menubuilder')->__('Nowhere (For Manual Positioning)'),
    		//self::POSITION_PAGE_TOP 				=> Mage::helper('menubuilder')->__('Page Top'),
     		self::POSITION_MENU_TOP 				=> Mage::helper('menubuilder')->__('Before Top Menu'),
    		self::POSITION_MENU_BOTTOM 				=> Mage::helper('menubuilder')->__('After Top Menu'),
    		self::POSITION_CONTENT_TOP 				=> Mage::helper('menubuilder')->__('Content Top'),
    		self::POSITION_SIDEBAR_RIGHT_TOP 		=> Mage::helper('menubuilder')->__('Sidebar Right Top'),
    		self::POSITION_SIDEBAR_RIGHT_BOTTOM 	=> Mage::helper('menubuilder')->__('Sidebar Right Bottom'),
    		self::POSITION_SIDEBAR_LEFT_TOP 		=> Mage::helper('menubuilder')->__('Sidebar Left Top'),
    		self::POSITION_SIDEBAR_LEFT_BOTTOM 		=> Mage::helper('menubuilder')->__('Sidebar Left Bottom'),
    		self::POSITION_FOOTER_TOP 				=> Mage::helper('menubuilder')->__('Footer Top'),
    		self::POSITION_FOOTER_BOTTOM			=> Mage::helper('menubuilder')->__('Footer Bottom'),
    		self::POSITION_PAGE_BOTTOM 				=> Mage::helper('menubuilder')->__('Page Bottom'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}