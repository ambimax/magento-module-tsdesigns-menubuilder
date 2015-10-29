<?php
/**
 * TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Edit_Tabs
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
class TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	
	protected $_menuAttributes = array(
		// General
		'name',
		'menu_code',
		'description',
		'is_active',
		'active_from',
		'active_to',
		'menu_position',
		'custom_position',
		'sort_order',
		
	
		// Design
		'display_name',
		'template',
		'image',
		'custom_template',
		'custom_css_class',
		'custom_font_color',
		'background_color',		
				
	);
		
	protected $_menuOnlyAttributes = array(
		'menu_code',
		'menu_position',
		'custom_position',
		'sort_order',
		'template',
		'custom_template',
		'display_name',			
	);
	
    /**
     * Initialize Tabs
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('menu_info_tabs');
        $this->setDestElementId('menu_tab_content');
        $this->setTitle(Mage::helper('menubuilder')->__('Menu Data'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    /**
     * Retrieve cattegory object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getMenu()
    {
        return Mage::registry('current_menu');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
    	return $this->getLayout()->createBlock('menubuilder/adminhtml_menu_edit_tab_attributes');
    }

    /**
     * Prepare Layout Content
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tabs
     */
    protected function _prepareLayout()
    {  	
    	$menu = Mage::registry('current_menu');
	   
        $categoryAttributes = $this->_getAttributes($menu->getAttributes(), $menu->isRoot());
        $attributeSetId     = $menu->getDefaultAttributeSetId();
        $groupCollection    = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($attributeSetId)
            ->load();
        $defaultGroupId = 0;
        $after = null;
        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            if ($defaultGroupId == 0 or $group->getIsDefault()) {
                $defaultGroupId = $group->getId();
            }
            if(strcmp($group->getAttributeGroupName(), 'General Information') === 0) {
            	$after = 'group_' . $group->getId();
            }
        }

        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            $attributes = array();
            foreach ($categoryAttributes as $attribute) {
                /* @var $attribute Mage_Eav_Model_Entity_Attribute */
                if ($attribute->isInGroup($attributeSetId, $group->getId())) {
                    $attributes[] = $attribute;
                }
            }

            // do not add grops without attributes
            if (!$attributes) {            	
                continue;
            }

            $active  = $defaultGroupId == $group->getId();
            $block = $this->getLayout()->createBlock($this->getAttributeTabBlock(), '')
                ->setGroup($group)
                ->setAttributes($attributes)
                ->setAddHiddenFields($active)
                ->toHtml();
                
            $this->addTab('group_' . $group->getId(), array(
                'label'     => Mage::helper('menubuilder')->__($group->getAttributeGroupName()),
                'content'   => $block,
                'active'    => $active,
            	'after'		=> $after,
            ));
        }
    	
        // dispatch event add custom tabs
        Mage::dispatchEvent('menubuilder_adminhtml_menu_edit_tabs', array(
            'tabs'  => $this,
        	'isRoot' => $menu->isRoot(),
        ));

        if($menu->isRoot()) {
        	$xmlBlock = $this->getLayout()->createBlock('menubuilder/adminhtml_menu_edit_tab_xml')->setMenu($menu);
        	$this->addTab('group_3' . $group->getId(), array(
                'label'     => Mage::helper('menubuilder')->__('XML'),
                'content'   => $xmlBlock->toHtml(),
                'active'    => $active,
            	'after'		=> $after,
            ));
        }
        
        return parent::_prepareLayout();
    }
    
    protected function _getAttributes($attributes, $isRoot = false)
    {
		$useAttributes = array();
		
    	if($isRoot) {	    	
	    	foreach($this->_menuAttributes as $code) {
	    		if(isset($attributes[$code])) {
	    			$useAttributes[$code] = $attributes[$code];
	    		}
	    	}	    	
    	} else {
    		foreach($attributes as $code => $attribute) {
    			if( ! in_array($code, $this->_menuOnlyAttributes)) {
    				$useAttributes[$code] = $attribute;
    			}
    		}
    	}
    	return $useAttributes;
    	
    }
    /*
    protected function _beforeToHtml()
    {
    	print_r($this->_tabs);
    	parent::_beforeToHtml();
    }*/
}

