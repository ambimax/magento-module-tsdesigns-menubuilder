<?php
/**
 * TSDesigns_MenuBuilder_Model_Resource_Eav_Mysql4_Menu
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
class TSDesigns_MenuBuilder_Model_Resource_Eav_Mysql4_Menu 
	extends TSDesigns_MenuBuilder_Model_Resource_Eav_Mysql4_Abstract
{

	protected $_rootIds;
    /**
     * Default menu attributes
     *
     * @return array
     *
    protected function _getDefaultAttributes()
    {
          return array('entity_id', 'entity_type_id', 'attribute_set_id', 'parent_id', 'menu_code', 'created_at', 'updated_at', 'position', 'level', 'children_count');
    }

    /**
     * Node tree object
     *
     * @var Varien_Data_Tree_Db
     */
    protected $_tree;

    /**
     * Catalog products table name
     *
     * @var string
     */
    protected $_categoryProductTable;

    /**
     * Id of 'is_active' category attribute
     *
     * @var int
     */
    protected $_isActiveAttributeId = null;

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('menubuilder')
            ->setConnection(
                $resource->getConnection('menubuilder_read'),
                $resource->getConnection('menubuilder_write')
            );
    }

    /**
     * Set store Id
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve category tree object
     *
     * @return Varien_Data_Tree_Db
     */
    protected function _getTree()
    {
        if (!$this->_tree) {
            $this->_tree = Mage::getResourceModel('menubuilder/menu_tree')
                ->load();
        }
        return $this->_tree;
    }

    /**
     * Process category data before delete
     * update children count for parent category
     * delete child categories
     *
     * @param   Varien_Object $object
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Category
     */
    protected function _beforeDelete(Varien_Object $object)
    {
        parent::_beforeDelete($object);

        /**
         * Update children count for all parent categories
         */
        $parentIds = $object->getParentIds();
        $childDecrease = $object->getChildrenCount() + 1; // +1 is itself
        $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count'=>new Zend_Db_Expr('`children_count`-'.$childDecrease)),
            $this->_getWriteAdapter()->quoteInto('entity_id IN(?)', $parentIds)
        );

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getEntityTable(), array('entity_id'))
            ->where($this->_getWriteAdapter()->quoteInto('`path` LIKE ?', $object->getPath().'/%'));

        $childrenIds = $this->_getWriteAdapter()->fetchCol($select);

        if (!empty($childrenIds)) {
            $this->_getWriteAdapter()->delete(
                $this->getEntityTable(),
                $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $childrenIds)
            );
        }

        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }

    /**
     * Process category data before saving
     * prepare path and increment children count for parent categories
     *
     * @param   Varien_Object $object
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Category
     */
    protected function _beforeSave(Varien_Object $object)
    {
        parent::_beforeSave($object);

        if (!$object->getId()) {
            $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            $path  = explode('/', $object->getPath());
            $level = count($path);
            $object->setLevel($level);
            if ($level) {
                $object->setParentId($path[$level - 1]);
            }
            $object->setPath($object->getPath() . '/');

            $toUpdateChild = explode('/',$object->getPath());

            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('children_count'=>new Zend_Db_Expr('`children_count`+1')),
                $this->_getWriteAdapter()->quoteInto('entity_id IN(?)', $toUpdateChild)
            );

        }
        return $this;
    }

    /**
     * Process category data after save category object
     * save related products ids and update path value
     *
     * @param   Varien_Object $object
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Category
     */
    protected function _afterSave(Varien_Object $object)
    {
        /**
         * Add identifier for new category
         */
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }

        Mage::app()->cleanCache(array(TSDesigns_MenuBuilder_Model_Menu::CACHE_TAG, TSDesigns_MenuBuilder_Model_Resource_Eav_Mysql4_Menu_Tree::CACHE_TAG));
        return parent::_afterSave($object);
    }

    /**
     * Update path field
     *
     * @param   Mage_Catalog_Model_Category $object
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Category
     */
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('path'=>$object->getPath()),
                $this->_getWriteAdapter()->quoteInto('entity_id=?', $object->getId())
            );
        }
        return $this;
    }

    protected function _getMaxPosition($path)
    {
        $select = $this->getReadConnection()->select();
        $select->from($this->getTable('menubuilder/menu'), 'MAX(position)');
        $select->where('path ?', new Zend_Db_Expr("regexp '{$path}/[0-9]+\$'"));

        $result = 0;
        try {
            $result = (int) $this->getReadConnection()->fetchOne($select);
        } catch (Exception $e) {

        }
        return $result;
    }


    /**
     * Get store identifiers where category is presented
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return  array
     */
    public function getStoreIds($category)
    {
        if (!$category->getId()) {
            return array();
        }

        $nodePath = $this->_getTree()
            ->getNodeById($category->getId())
                ->getPath();

        $nodes = array();
        foreach ($nodePath as $node) {
            $nodes[] = $node->getId();
        }

        $stores = array();
        $storeCollection = Mage::getModel('core/store')->getCollection()->loadByCategoryIds($nodes);
        foreach ($storeCollection as $store) {
            $stores[$store->getId()] = $store->getId();
        }

        $entityStoreId = $category->getStoreId();
        if (!in_array($entityStoreId, $stores)) {
            array_unshift($stores, $entityStoreId);
        }
        if (!in_array(0, $stores)) {
            array_unshift($stores, 0);
        }
        return $stores;
    }

    /**
     * Get chlden categories count
     *
     * @param   int $categoryId
     * @return  int
     */
    public function getChildrenCount($categoryId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'children_count')
            ->where('entity_id=?', $categoryId);

        $child = $this->_getReadAdapter()->fetchOne($select);

        return $child;
    }

    /**
     * Move category to another parent
     *
     * @param   int $categoryId
     * @param   int $newParentId
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Category
     */
    public function move($categoryId, $newParentId)
    {
        $category  = Mage::getModel('menubuilder/menu')->load($categoryId);
        $oldParent = $category->getParentCategory();
        $newParent = Mage::getModel('menubuilder/menu')->load($newParentId);

        $childrenCount = $this->getChildrenCount($category->getId()) + 1;

        // update children count of new parents
        $parentIds = explode('/', $newParent->getPath());
        $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count' => new Zend_Db_Expr("`children_count` + {$childrenCount}")),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $parentIds)
        );

        // update children count of old parents
          $parentIds = explode('/', $oldParent->getPath());
          $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count' => new Zend_Db_Expr("`children_count` - {$childrenCount}")),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $parentIds)
        );

        // update parent id
        $this->_getWriteAdapter()->query("UPDATE
            {$this->getEntityTable()} SET parent_id = {$newParent->getId()}
            WHERE entity_id = {$categoryId}");

        return $this;
    }

    /**
     * Check if category id exist
     *
     * @param   int $id
     * @return  bool
     */
    public function checkId($id)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('entity_id=?', $id);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Check array of category identifiers
     *
     * @param   array $ids
     * @return  array
     */
    public function verifyIds(array $ids)
    {
        $validIds = array();
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('entity_id IN(?)', $ids);
        $query = $this->_getWriteAdapter()->query($select);
        while ($row = $query->fetch()) {
            $validIds[] = $row['entity_id'];
        }
        return $validIds;
    }

    /**
     * Get count of active/not active children categories
     *
     * @param   Mage_Catalog_Model_Category $category
     * @param   bool $isActiveFlag
     * @return  int
     */
    public function getChildrenAmount($category, $isActiveFlag = true)
    {
        $storeId = Mage::app()->getStore()->getId();
        $attributeId = $this->_getIsActiveAttributeId();
        $table = Mage::getSingleton('core/resource')->getTableName('menubuilder/menu') . '_int';

        $select = $this->_getReadAdapter()->select()
            ->from(array('m'=>$this->getEntityTable()), array('COUNT(m.entity_id)'))
            ->joinLeft(
                array('d'=>$table),
                "d.attribute_id = '{$attributeId}' AND d.store_id = 0 AND d.entity_id = m.entity_id",
                array()
            )
            ->joinLeft(
                array('c'=>$table),
                "c.attribute_id = '{$attributeId}' AND c.store_id = '{$storeId}' AND c.entity_id = m.entity_id",
                array()
            )
            ->where('m.path like ?', $category->getPath() . '/%')
            ->where('(IFNULL(c.value, d.value) = ?)', $isActiveFlag);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    protected function _getIsActiveAttributeId()
    {
        if (is_null($this->_isActiveAttributeId)) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$this->getTable('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = ?', 'menubuilder')
                ->where('attribute_code = ?', 'is_active');

            $this->_isActiveAttributeId = $this->_getReadAdapter()->fetchOne($select);
        }
        return $this->_isActiveAttributeId;
    }


    public function findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($attribute->getBackend()->getTable(), array('entity_id'))
            ->where('attribute_id = ?', $attribute->getId())
            ->where('value = ?', $expectedValue)
            ->where('entity_id in (?)', $entityIdsFilter);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Retrieve categories
     *
     * @param integer $parent
     * @param integer $recursionLevel
     * @param boolean|string $sorted
     * @param boolean $asCollection
     * @param boolean $toLoad
     * @return Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        $tree = Mage::getResourceModel('menubuilder/menu_tree');
        /** @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
        $nodes = $tree->loadNode($parent)
            ->loadChildren($recursionLevel)
            ->getChildren();

        $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);

        if ($asCollection) {
            return $tree->getCollection();
        }
        return $nodes;
    }

    /**
     * Return parent categories of category
     *
     * @param Mage_Catalog_Model_Category $category
     * @return array
     */
    public function getParentCategories($category)
    {
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        $categories = Mage::getResourceModel('menubuilder/menu_collection')
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('*')
            /*
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            */
            ->addFieldToFilter('entity_id', array('in'=>$pathIds))
            ->addFieldToFilter('is_active', 1)
            ->load()
            ->getItems();
        return $categories;
    }

    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Category $category
     * @return unknown
     */
    public function getChildrenCategories($category)
    {
        $collection = $category->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        $collection->addAttributeToSelect('*')
            /*             
            ->addAttributeToSelect('url_key')
        	->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            */
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($category->getChildren())
            ->setOrder('position', 'ASC')
            ->joinUrlRewrite()
            ->load();
        return $collection;
    }

    /**
     * Return children ids of category
     *
     * @param Mage_Catalog_Model_Category $category
     * @param boolean $recursive
     * @return array
     */
    public function getChildren($category, $recursive = true)
    {
        $attributeId = $this->_getIsActiveAttributeId();
        $select = $this->_getReadAdapter()->select()
            ->from(array('m' => $this->getEntityTable()), 'entity_id')
            ->joinLeft(
                array('d' => $this->getEntityTable() . '_int'),
                "d.attribute_id = '{$attributeId}' AND d.store_id = 0 AND d.entity_id = m.entity_id",
                array()
            )
            ->joinLeft(
                array('c' => $this->getEntityTable() . '_int'),
                "c.attribute_id = '{$attributeId}' AND c.store_id = '{$category->getStoreId()}' AND c.entity_id = m.entity_id",
                array()
            )
            ->where('(IFNULL(c.value, d.value) = ?)', '1')
            ->where('path LIKE ?', "{$category->getPath()}/%");
        if (!$recursive) {
            $select->where('level <= ?', $category->getLevel() + 1);
        }
        $_categories = $this->_getReadAdapter()->fetchAll($select);
        $categoriesIds = array();
        foreach ($_categories as $_category) {
            $categoriesIds[] = $_category['entity_id'];
        }

        return $categoriesIds;

//        $this->_getTree()->load();
//        return $this->_getTree()->getChildren($category->getId(), false);
    }

    /**
     * Return all children ids of category (with category id)
     *
     * @param Mage_Catalog_Model_Category $category
     * @return array
     */
    public function getAllChildren($category)
    {
        $children = $this->getChildren($category);
        $myId = array($category->getId());
        $children = array_merge($myId, $children);

        return $children;

//        $this->_getTree()->load();
//        $children = $this->_getTree()->getChildren($category->getId());
//
//        $myId = array($category->getId());
//        if (is_array($children)) {
//            $children = array_merge($myId, $children);
//        } else {
//            $children = $myId;
//        }
//
//        return $children;
    }

    /**
     * Check is category in list of store categories
     *
     * @param Mage_Catalog_Model_Category $category
     * @return boolean
     */
    public function isInRootList($menu)
    {	
    	throw new Exception('Deprecated in Menu.php Line 624');
    	
    	$menuId = is_object($menu) ? $menu->getId() : $menu;
    	if( ! $menuId) {
    		return false;
    	}

        $select = $this->_getReadAdapter()->select()
        	->from($this->getEntityTable(), array("level"))
        	->where('entity_id = ? AND level = 2', $menuId);
       	
        return (bool) $this->_getReadAdapter()->fetchOne($select);
    }
    
    /**
     * Check category is forbidden to delete.
     *
     * If category is root and assigned to store group return false
     *
     * @param integer $categoryId
     * @return boolean
     */
    public function isForbiddenToDelete($categoryId)
    {
    	return (TSDesigns_MenuBuilder_Model_Menu::TREE_ROOT_ID === $categoryId);
    }
    
    public function getRootIds()
    {
    	if(is_null($this->_rootIds)) {
	        $select = $this->_getReadAdapter()->select()
	            ->from($this->getTable('menubuilder/menu'), array('entity_id'))
	            ->where('parent_id = ?', 0)
	            ->where('menu_code IS NOT NULL');
	        $this->_rootIds = (array) $this->_getReadAdapter()->fetchCol($select);
    	}

    	return $this->_rootIds;
    }
    
    /**
     * Get product identifier by sku
     *
     * @param   string $sku
     * @return  int|false
     */
    public function getIdByCode($code)
    {
        return $this->_read->fetchOne('select entity_id from '.$this->getEntityTable().' where menu_code=?', $code);
    }
    
    public function getCmsPageByStore($storeId = null)
    {
        
        $select = $this->_read->select();
        $select->from(array('ps' => $this->getTable('cms/page_store')), array('store_id', 'page_id'))
            ->joinLeft(array('p' => $this->getTable('cms/page')), 'p.page_id=ps.page_id', array('identifier', 'is_active', 'title'))
            ->order('store_id')
            ->order('title');

        if($storeId) {
            $select->where('store_id = ?', $storeId);
        }    
        
        return $this->_read->fetchAll($select);
    }
    
}
