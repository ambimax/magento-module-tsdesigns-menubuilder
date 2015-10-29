<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Source_Display
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
class TSDesigns_MenuBuilder_Model_Menu_Source_Display extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
	const DISPLAY_NAME 			= 'name';
	const DISPLAY_IMAGE 		= 'image';
	const DISPLAY_ICON 			= 'icon';
	const DISPLAY_BACKGROUND 	= 'background';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		self::DISPLAY_NAME 			=> Mage::helper('menubuilder')->__('Name'),
    		self::DISPLAY_IMAGE 		=> Mage::helper('menubuilder')->__('Image'),
    		self::DISPLAY_ICON 			=> Mage::helper('menubuilder')->__('Icon And Name'),
    		self::DISPLAY_BACKGROUND 	=> Mage::helper('menubuilder')->__('Background And Name'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}