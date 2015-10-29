<?php
/**
 * TSDesigns_MenuBuilder_Block_Collection
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
class TSDesigns_MenuBuilder_Block_Collection extends Mage_Core_Block_Template
{
    protected $_collection = null;
    
    public function getMenuCodes()
    {
    	if(is_null(Mage::registry('menubuilder_menu_codes'))) {    		
    		$collection = Mage::getModel('menubuilder/menu')->getCollection();
    		    //->initCache(Mage::app()->getCache(), 'menu_codes', array(TSDesigns_MenuBuilder_Model_Menu::CACHE_TAG));
    		$collection->addAttributeToSelect('*')
    				   ->addLevelFilter(1)
    				   ->addIsActiveFilter()
    				   ->addAttributeToSort('sort_order');
    				   
    		Mage::register('menubuilder_menu_codes', $collection);
    	}
        return Mage::registry('menubuilder_menu_codes');
    }

    protected function _toHtml()
    {
    	$menuCodes = $this->getMenuCodes();
    	
    	if(is_null($menuCodes) || ! count($menuCodes)) {
    		return '';
    	}
    	
    	$blocks = array();
    	foreach($menuCodes as $menu) {	    		
    		
    		$code = $menu->getData('menu_code');
    		$position = $menu->getData('menu_position');
    		
    		if($this->getBlockId() != $position) {
    			continue;
    		}
    		
    		$blocks[$code] = $this->getLayout()
    						    ->createBlock('menubuilder/menu', "{$position}-{$code}", array('position' => 999999))
    						 	->setInstance($menu)
   								->toHtml();
    	}
    	return implode("<!-- BLOCK -->", $blocks);    	
    }
}