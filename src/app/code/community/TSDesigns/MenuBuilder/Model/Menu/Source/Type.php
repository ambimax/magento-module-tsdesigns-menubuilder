<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Source_Type
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
class TSDesigns_MenuBuilder_Model_Menu_Source_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
	const TYPE_NOWHERE 		= 'nowhere';
	const TYPE_CATEGORY 	= 'category';
	const TYPE_CMS_PAGE 	= 'cms_page';
	const TYPE_PRODUCT 		= 'product';
	const TYPE_INTERNAL 	= 'internal';
	const TYPE_EXTERNAL 	= 'external';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		self::TYPE_NOWHERE 		=> Mage::helper('menubuilder')->__('Nowhere'),
    		self::TYPE_CATEGORY 	=> Mage::helper('menubuilder')->__('Category'),
    		self::TYPE_CMS_PAGE 	=> Mage::helper('menubuilder')->__('CMS Page'),
    		self::TYPE_PRODUCT 		=> Mage::helper('menubuilder')->__('Product'),
    		self::TYPE_INTERNAL 	=> Mage::helper('menubuilder')->__('Internal Url'),
    		self::TYPE_EXTERNAL 	=> Mage::helper('menubuilder')->__('External Url'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
    
    public function getTypes()
    {
    	return array_keys($this->getOptionArray());
    }
}