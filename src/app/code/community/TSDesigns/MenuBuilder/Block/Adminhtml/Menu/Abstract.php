<?php
/**
 * TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Abstract
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
class TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Abstract extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getMenu()
    {
        return Mage::registry('current_menu');
    }

    public function getMenuId()
    {
        if ($this->getMenu()) {
            return $this->getMenu()->getId();
        }
        return TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID;
    }

    public function getMenuName()
    {
        return $this->getMenu()->getName();
    }

    public function getMenuPath()
    {
        if ($this->getMenu()) {
            return $this->getMenu()->getPath();
        }
        return TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID;
    }

    public function hasRootMenu()
    {
        $root = $this->getRoot();
        if (is_object($root) && $root->getId()) {
            return true;
        }
        return false;
    }

    public function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        return Mage::app()->getStore($storeId);
    }

    public function getRoot($parentNodeMenu=null, $recursionLevel=3)
    {
        if (!is_null($parentNodeMenu) && $parentNodeMenu->getId()) {
            return $this->getNode($parentNodeMenu, $recursionLevel);
        }
        
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');
			$rootId = TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID;
            $tree = Mage::getResourceSingleton('menubuilder/menu_tree')
                ->load(null, $recursionLevel);
                
            if ($this->getMenu()) {
                $tree->loadEnsuredNodes($this->getMenu(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getMenuCollection());
            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID) {
                $root->setName(Mage::helper('menubuilder')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }

    /**
     * Get and register categories root by specified categories IDs
     *
     * IDs can be arbitrary set of any categories ids.
     * Tree with minimal required nodes (all parents and neighbours) will be built.
     * If ids are empty, default tree with depth = 2 will be returned.
     *
     * @param array $ids
     */
    public function getRootByIds($ids)
    {
        $root = Mage::registry('root');
        if (null === $root) {
            $tree = Mage::getResourceSingleton('menubuilder/menu_tree')
                ->loadByIds($ids);
            $rootId = TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID;
            $root   = $tree->getNodeById($rootId);
            if ($root && $rootId != TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID) {
                $root->setName(Mage::helper('menubuilder')->__('Root'));
            }
            Mage::register('root', $root);
        }
        return $root;
    }

    public function getNode($parentNodeMenu, $recursionLevel=2)
    {
        $tree = Mage::getResourceModel('menubuilder/menu_tree');

        $nodeId     = $parentNodeMenu->getId();
        $parentId   = $parentNodeMenu->getParentId();

        $node = $tree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif($node && $node->getId() == TSDesigns_MenuBuilder_Model_Abstract::TREE_ROOT_ID) {
            $node->setName(Mage::helper('menubuilder')->__('Root'));
        }
        
        $tree->addCollectionData($this->getMenuCollection());

        return $node;
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    public function getEditUrl()
    {
        return $this->getUrl("*/adminhtml_menu/edit", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if (is_null($ids)) {
            $ids = Mage::getResourceModel('menubuilder/menu')->getRootIds();
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
}