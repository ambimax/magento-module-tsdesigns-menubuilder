<?php
/**
 * TSDesigns_MenuBuilder_Block_Menu
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
class TSDesigns_MenuBuilder_Block_Menu extends Mage_Core_Block_Template
{
    protected $_menuInstance = null;    
    protected $_parentId = null;
    protected $_block = null;

    protected function _construct()
    {
        $this->addData(array(
            'cache_lifetime'    => false,
            'cache_tags'        => array(
        	    TSDesigns_MenuBuilder_Model_Menu::CACHE_TAG,
        	    Mage_Catalog_Model_Product::CACHE_TAG,
        	    Mage_Catalog_Model_Category::CACHE_TAG,
        	    Mage_Cms_Model_Block::CACHE_TAG, 
                Mage_Catalog_Model_Category::CACHE_TAG, 
                Mage_Core_Model_Store_Group::CACHE_TAG
            ),
        ));
    }
    
    /**
     * Retrieve Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->generateBlockName(array($this->getMenuCode(), $this->getName(), $this->getAs()))
        	. '_' . Mage::app()->getStore()->getId()
            . '_' . Mage::getDesign()->getPackageName()
            . '_' . Mage::getDesign()->getTheme('template')
            . '_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
            . '_' . md5($this->getTemplate());
    }

    public function getParentId()
    {
    	return $this->getInstance()->getId();
    }
    
    public function setInstance($instance)
    {
    	$this->setData('instance', $instance);
    	$this->addData($instance->getData());
    	
    	return $this;
    }
    
    public function getInstance()
    {
    	if(is_null($this->getData('instance'))) {
    		$instance = Mage::getModel('menubuilder/menu');    		
    		if($this->getMenuCode()) {
    			$this->setInstance($instance->loadByAttribute('menu_code', (string) $this->getMenuCode()));
    		} else {
    		    $this->setInstance($instance);
    		}
    	}
    	
    	return $this->getData('instance');
    }
    
    /**
     * Get catagories of current store
     *
     * @return Varien_Data_Tree_Node_Collection
     */
    public function getMenuNodes()
    {	
        $helper = Mage::helper('menubuilder/menu');
        $helper->setParentId($this->getParentId());
        return $helper->getMenuNodes();
    }

    /**
     * Retrieve child categories of current category
     *
     * @return Varien_Data_Tree_Node_Collection
     */
    public function getCurrentChildNodes()
    {
        if( ! $this->getMenuCode()) {
        	return;
        }
        return $this->getInstance();
    }

    /**
     * Checkin activity of category
     *
     * @param   Varien_Object $category
     * @return  bool
     */
    public function isCategoryActive($category)
    {
    	if(null === Mage::registry('menubuilder_current_path')) {   	
	    	
	    	if(Mage::registry('current_category')) {
	    		$linkType = 'category';
	    		$linkId = Mage::registry('current_category')->getId();
	    	}
	    	
	    	if( ! isset($linkId) && Mage::registry('current_product')) {
	    		$linkType = 'product';
	    		$linkId = Mage::registry('current_product')->getSku();
	    	}
	    	
	    	if( ! isset($linkId) && Mage::app()->getRequest()->getParam('page_id')) {
	    		$linkType = 'cms_page';
	    		$linkId = Mage::app()->getRequest()->getParam('page_id');
	    	}    	
   			
	    	$path = array();
   			if( isset($linkId) && isset($linkType)) {	
	   			$nodes = Mage::getModel('menubuilder/menu')->getCollection()
	   				->addFieldToFilter('link_to', $linkType)
	   				->addFieldToFilter('link_to_' . $linkType, $linkId);	   			
	   			
	   			foreach($nodes as $node) {
	   				$path = array_merge($path, explode('/', $node->getData('path')));
	   			}
   			}
   			Mage::register('menubuilder_current_path', $path);
   		}
   		
   		return Mage::registry('menubuilder_current_path') ? in_array($category->getId(), Mage::registry('menubuilder_current_path')) : false;
    }

    /**
     * Get url for category data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof Mage_Catalog_Model_Category) {
            $url = $category->getUrl();
        } else {
            $url = $this->getModel()
                ->setData($category->getData())
                ->getUrl();
        }

        return $url;
    }

    public function getModel($data = null)
    {
    	if(is_null($this->_model)) {
    		$this->_model = Mage::getModel('menubuilder/menu');
    	} 
    	
    	if( ! is_null($data)) {
    		$this->_model->setData($data);
    	}
    	return $this->_model;
    }
    
    public function getMenu($asArray = false)
    {
    	$nodes = $this->getMenuNodes();
		$nodeCount = 0;
		$html = $asArray ? array() : '';
		foreach ($nodes as $key => $_node) {
			$node = $this->drawItem($_node, 0, ++$nodeCount == $nodes->count());
			if($asArray) {
				$html[] = $node->toHtml();
			} else {
				$html.= $node->toHtml();
			}
		}
		return $html;
    }
    
    public function generateBlockName($array = array())
    {
        $name = implode('_', $array);
        while(in_array($name, array_keys($this->getLayout()->getAllBlocks()))) {
            $name .= rand(1,9);
        }        
        return $name; 
    }
    
    public function getBlock()
    {
    	if(is_null($this->_block)) {
    	        	    
	        $this->_block = $this->getLayout()->createBlock(
	        	'menubuilder/menu_node',
	            $this->generateBlockName(array($this->getMenuCode(), $this->getName(), $this->getAs()))
	        ); 
	        
	        $this->_block->addElementOptions('class', 'div', array(
	        	$this->getData('custom_css_class'),
	        ));
	        
	        $this->_block->addElementOptions('style', 'div', array(
	        	'background-color' => '#' . $this->getData('background_color'),
	        ));
	        
	        if($this->getData('image')) {
		        $this->_block->addElementOptions('style', 'div', array(
		        	'background-image' => 'url(' . Mage::getBaseUrl('media').'catalog/category/'.$this->getData('image').')',
		        ));
	        }
    	}
    	return $this->_block;
    }
    
    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int $level
     * @param boolean $last
     * @return string
     */
    public function drawItem($node, $level=0, $last=false)
    {
   		$menuNode = $this->getModel($node->getData());	
        $block = $this->getLayout()->createBlock(
        	'menubuilder/menu_node',
            $this->generateBlockName(array($this->getMenuCode(), 'node', $menuNode->getId(), $this->getName(), $this->getAs()))
        ); 
        
        $block->setIsActive($menuNode->isActive());
        
        if (!$block->isActive()) {
        	return $block;
        }
        
        $block->setLevel($level);
        if($last) {
            $block->addElementOptions('class', 'li', array('last'));
        }
        $block->addElementOptions('attribute', 'li.a', array(
			'href' => $menuNode->getUrl(true),
        	'target' => $menuNode->getLinkTarget(),
        ));
        
        $isActive = $this->isCategoryActive($node) ? 'active' : null;
        $block->addElementOptions('class', 'li.a', array("level{$level}", $isActive));
        	
        $cssUrl = Mage::helper('catalog/category')->getCategoryUrlPath(str_replace(array(Mage::getBaseUrl(), '#'), '',$menuNode->getUrl(false)));
        $block->addElementOptions('class', 'li', array(
        	'level'.$level,
        	'nav-'.str_replace('/', '-', $cssUrl),
        	$menuNode->getData('custom_css_class'),
        	$isActive,
        ));
        
        $block->addElementOptions('style', 'li.a.span', array(
        	'color' => '#' . $menuNode->getData('font_color'),
        	'font-weight' => $menuNode->getData('font_weight'),        
        ));
        $block->addElementOptions('class', 'li.a.span', array("level{$level}", $isActive));
                
        $hasImage = (TSDesigns_MenuBuilder_Model_Menu_Source_Display::DISPLAY_IMAGE == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
        $hasBackgroundImage = (TSDesigns_MenuBuilder_Model_Menu_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
        $hasIcon = (TSDesigns_MenuBuilder_Model_Menu_Source_Display::DISPLAY_ICON == $menuNode->getData('display_mode') && $menuNode->getImageUrl());

        $block->addElementOptions('style', 'li.a', array(
        	'background-image' => $hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? "url('{$menuNode->getImageUrl()}')" : null,
        	'background-position' => $hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? 'center center' : null,
        	'background-repeat' => $this->getImageAsBackground() && $hasImage ? 'no-repeat' : null,
        	'background-color' => '#' . $menuNode->getData('background_color'),
        ));
        
        if($hasIcon) {        
        	$block->addElementOptions(array(
        		'li.a.img' => array(
        			'class' 	=> array('image', 'level'.$level),
        			'attribute' => array('src' => $menuNode->getImageUrl()),
        		),
        		'li.a.span' => array(
        		    'class' => array('icon'),
        		)
        	));
        }
        
        if( ! $this->getImageAsBackground() && $hasImage) {
        	$block->setName('<img src="' . $menuNode->getImageUrl() . '" />');
        } else {
        	$block->setName( ! $hasImage ? $this->htmlEscape($node->getName()) : '&nbsp;');
        }
        
        $children = $node->getChildren();
        $childrenCount = $children->count();
        
        // Handle children
        if ($children && $childrenCount){
            $childBlocks = array();
            foreach ($children as $child) {
            	$childblock = $this->drawItem($child, $level+1);
            	if($childblock->isActive()) {
            		$childBlocks[] = $childblock;
            	}
            }
            // Mark node as last
            if(isset($childBlocks[sizeof($childBlocks) - 1])) {
            	$childBlocks[sizeof($childBlocks) - 1]->addElementOptions('class', 'li', array('last'));
            }
            
            if(count($childBlocks)) {                
	        	$block->addElementOptions('attribute', 'li', array(
	        		'onmouseover' => "toggleMenuBuilderMenu(this,1)",
	        		'onmouseout' => "toggleMenuBuilderMenu(this,0)",
	        	));	        	
	        	$block->addElementOptions('class', 'li', array('parent'));
	        	$block->addElementOptions('class', 'li.a', array('parent'));
	        	$block->addElementOptions('class', 'li.a.span', array('parent'));
	        	$block->setChildren($childBlocks);
            }
        }         
        else {
        	$block->addElementOptions('attribute', 'li', array(
        		'onmouseover' => "$(this).addClassName('over')",
        		'onmouseout' => "$(this).removeClassName('over')",
        	));	   
        }                       
        
        return $block;
    }
    
    

    /**
     * Enter description here...
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
    	if( ! Mage::registry('menubuilder_current_node')) {   	
	    	$linkId = false;
	    	if(Mage::registry('current_category')) {
	    		$linkType = 'category';
	    		$linkId = Mage::registry('current_category')->getId();
	    	}
	    	
	    	if( ! $linkId && Mage::registry('current_product')) {
	    		$linkType = 'product';
	    		$linkId = Mage::registry('current_product')->getSku();
	    	}
	    	
	    	if( ! $linkId && Mage::app()->getRequest()->getParam('page_id')) {
	    		$linkType = 'cms_page';
	    		$linkId = Mage::app()->getRequest()->getParam('page_id');
	    	}    	
   				
   			$nodes = Mage::getModel('menubuilder/menu')->getCollection()
   				->addFieldToFilter('link_to', $linkType)
   				->addFieldToFilter('link_to_' . $linkType, $linkId);
   			$path = array();
   			foreach($nodes as $node) {
   				$path = array_merge($path, implode('/', $node->getData('path')));
   			}
   			Mage::register('menubuilder_current_node', 1);
   		}
    	return false;
        if (Mage::getSingleton('catalog/layer')) {
            return Mage::getSingleton('catalog/layer')->getCurrentCategory();
        }
        return false;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCurrentCategoryPath()
    {
        if ($this->getCurrentCategory()) {
            return explode(',', $this->getCurrentCategory()->getPathInStore());
        }
        return array();
    }

    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function drawOpenCategoryItem($category) {
        $html = '';
        if (!$category->getIsActive()) {
            return $html;
        }

        $html.= '<li';

        if ($this->isCategoryActive($category)) {
            $html.= ' class="active"';
        }

        $html.= '>'."\n";
        $html.= '<a href="'.$this->getCategoryUrl($category).'"><span>'.$this->htmlEscape($category->getName()).'</span></a>'."\n";

        if (in_array($category->getId(), $this->getCurrentCategoryPath())){
            $children = $category->getChildren();
            $hasChildren = $children && $children->count();

            if ($hasChildren) {
                $htmlChildren = '';
                foreach ($children as $child) {
                    $htmlChildren.= $this->drawOpenCategoryItem($child);
                }

                if (!empty($htmlChildren)) {
                    $html.= '<ul>'."\n"
                            .$htmlChildren
                            .'</ul>';
                }
            }
        }
        $html.= '</li>'."\n";
        return $html;
    }
    
    public function getTemplate()
    {
    	if(is_null(parent::getTemplate())) {    	    
    		$this->setTemplate($this->getData('custom_template') ? $this->getData('custom_template') : $this->getData('template'));
    	}
    	return parent::getTemplate();
    }
    
    public function _beforeToHtml()
    {
        if( ! $this->hasInstance()) {
            $this->setInstance($this->getInstance());
        }
    }
   
    public function getCss($default = null)
    {
    	if(is_null($default)) {
    		return '';
    	}
    	
    	if(is_array($default)) {
    		return implode(' ', $default);
    	}
    	return $default;
    }
}