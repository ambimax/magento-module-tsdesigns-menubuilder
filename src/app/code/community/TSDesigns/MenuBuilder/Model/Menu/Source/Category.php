<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Source_Category
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
class TSDesigns_MenuBuilder_Model_Menu_Source_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	protected $_options = array();

    function getTreeCategories($parentId = null)
    {
        //$categoryCollection = Mage::getModel('catalog/category')->getCollection()
        $categoryCollection = Mage::getResourceModel('menubuilder/category_collection')
            ->addAttributeToSelect('*')
            //->addAttributeToFilter('is_active', 1)
            //->addAttributeToFilter('include_in_menu', 1)
            //->addAttributeToFilter('parent_id', 0)
            //->addAttributeToFilter('parent_id',array('eq' => $parentId))
            ->addAttributeToSort('position', 'asc');

        if(is_null($parentId)) {
            // Root categories
            $categoryCollection->addAttributeToFilter('level', 0);
        } else {
            $categoryCollection
                ->addAttributeToFilter('parent_id',array('eq' => $parentId));
        }

        foreach($categoryCollection as $node) {
            if('' != $node->getName() && $node->getLevel()) {
                $this->_options[] = array(
                    'label' => $this->_addWhitespaceByLevel($node->getName(), $node->getLevel()),
                    'value' => $node->getLevel() > 1 ? $node->getId() : null,
                );
            }

            if($node->hasChildren()) {
                $this->getTreeCategories($node->getId(), true);
            }
        }
    }

    public function toOptionArray($addEmpty = true)
    {
        if( ! count($this->_options)) {
            $this->getTreeCategories();
            $this->_options = $this->_formatArray($this->_options);
        }
        return $this->_options;

//        $parentId = Mage::app()->getStore()->getRootCategoryId();
//        $recursionLevel = max(0, (int) Mage::app()->getStore()->getConfig('catalog/navigation/max_depth'));
//
//        $tree = Mage::getResourceModel('catalog/category_tree');
//        /** @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
//        $nodes = $tree->loadNode($parentId)
//            ->loadChildren($recursionLevel)
//            ->getChildren();
//
//        $tree->addCollectionData(null, false, $parentId, true, false);
//
//        foreach($nodes as $node) {
//        	$this->addNode($node, 0);
//        }
//        $this->_options = $this->_formatArray($this->_options);
//        return $this->_options;
    }

    protected function _formatArray($options)
    {
        $array = array(array(
        	'label' => Mage::helper('menubuilder')->__('Please select a category'),
        	'value' => null
        ));

        $current = 0;
        foreach($options as $option) {
            if(false === strpos($option['label'], '&nbsp;')) {
                $array[++$current] = array(
                    'label' => $option['label'],
                    'value' => array()
                );
                continue;
            }
            
            $array[$current]['value'][] =  $option;
        }
        
        return $array;        
    }
    
    public function _addWhitespaceByLevel($name = '', $level = 0)
    {
    	if(empty($name)) {
    		return $name;
    	}
    	
        $nbsp = '';
        for($x = 1; $x < $level; $x++ ) {
        	$nbsp .= "&nbsp;&nbsp;&nbsp;&nbsp;";        	
        }
    	return $nbsp . $name;
    }

    public function addNode($node, $level=0)
    {
        if( ! $node instanceof Varien_Data_Tree_Node) {
            return;
        }

    	if('' != $node->getName() && $level) {
            $this->_options[] = array(
               'label' => $this->_addWhitespaceByLevel($node->getName(), $level),
               'value' => $level > 1 ? $node->getId() : null,
            );
    	}
    	
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = $node->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $node->getChildren();
            $childrenCount = $children->count();
        }

        foreach ( (array) $children as $child) {
        	$this->addNode($child, $level+1);
        }
        
        return;
    }
    
    
    public function getAllOptions()
    {
    	return $this->toOptionArray();
    }
}
