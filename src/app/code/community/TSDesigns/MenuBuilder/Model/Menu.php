<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu
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
class TSDesigns_MenuBuilder_Model_Menu extends TSDesigns_MenuBuilder_Model_Abstract
{

    const CACHE_TAG             = 'MENUBUILDER_MENU';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix     = 'menubuilder_menu';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject     = 'menu';

    /**
     * Model cache tag for clear cache in after save and after delete
     */
    protected $_cacheTag        = self::CACHE_TAG;

    /**
     * URL Model instance
     *
     * @var Mage_Core_Model_Url
     */
    protected static $_url;

    /**
     * URL rewrite model
     *
     * @var Mage_Core_Model_Url_Rewrite
     */
    protected static $_urlRewrite;

    /**
     * Use flat resource model flag
     *
     * @var bool
     */
    protected $_useFlatResource = false;

    /**
     * Node design attributes
     *
     * @var array
     */
    private $_designAttributes  = array(
        'custom_design',
        'custom_design_apply',
        'custom_design_from',
        'custom_design_to',
        'page_layout',
        'custom_layout_update'
    );

    /**
     * Node tree model
     *
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
     */
    protected $_treeModel = null;

    protected $_isRoot = false;
    
    protected $_typeInstance = null;

    
    /**
     * Initialize resource mode
     *
     */
    protected function _construct()
    {
        $this->_init('menubuilder/menu');
    }

    /**
     * Retrieve URL instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (!self::$_url) {
            self::$_url = Mage::getModel('core/url');
        }
        return self::$_url;
    }

    /**
     * Get url rewrite model
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewrite()
    {
        if (!self::$_urlRewrite) {
            self::$_urlRewrite = Mage::getModel('core/url_rewrite');
        }
        return self::$_urlRewrite;
    }

    /**
     * Retrieve category tree model
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
     */
    public function getTreeModel()
    {
        return Mage::getResourceModel('menubuilder/menu_tree');
    }

    /**
     * Enter description here...
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
     */
    public function getTreeModelInstance()
    {
        if (is_null($this->_treeModel)) {
            $this->_treeModel = Mage::getResourceSingleton('menubuilder/menu_tree');
        }
        return $this->_treeModel;
    }

    /**
     * Move category
     *
     * @return Mage_Catalog_Model_Category
     */
    /*
    public function move($parentId)
    {
        $this->getResource()->move($this, $parentId);
        return $this;
    }
    */

    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * Retrieve all customer attributes
     *
     * @todo Use with Flat Resource
     * @return array
     */
    public function getAttributes($noDesignAttributes = false)
    {
        $result = $this->getResource()
            ->loadAllAttributes($this)
            ->getSortedAttributes();

        if ($noDesignAttributes){
            foreach ($result as $k=>$a){
                if (in_array($k, $this->_designAttributes)) {
                    unset($result[$k]);
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve array of store ids for category
     *
     * @return array
     */
    public function getStoreIds()
    {
        if ($this->getInitialSetupFlag()) {
            return array();
        }

        if ($storeIds = $this->getData('store_ids')) {
            return $storeIds;
        }
        $storeIds = $this->getResource()->getStoreIds($this);
        $this->setData('store_ids', $storeIds);
        return $storeIds;
    }

    /**
     * Retrieve Layout Update Handle name
     *
     * @return string
     */
    public function getLayoutUpdateHandle()
    {
        $layout = 'menubuilder_menu_';
        if ($this->getIsAnchor()) {
            $layout .= 'layered';
        }
        else {
            $layout .= 'default';
        }
        return $layout;
    }

    /**
     * Return store id.
     *
     * If store id is underfined for category return current active store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Category
     */
    public function setStoreId($storeId)
    {
        if (!is_numeric($storeId)) {
            $storeId = Mage::app($storeId)->getStore()->getId();
        }
        $this->setData('store_id', $storeId);
        $this->getResource()->setStoreId($storeId);
        return $this;
    }


    /**
     * Retrieve category id URL
     *
     * @return string
     */
    public function getCategoryIdUrl()
    {
        Varien_Profiler::start('REGULAR: '.__METHOD__);
        $urlKey = $this->getUrlKey() ? $this->getUrlKey() : $this->formatUrlKey($this->getName());
        $url = $this->getUrlInstance()->getUrl('menubuilder/menu/view', array(
            's'=>$urlKey,
            'id'=>$this->getId(),
        ));
        Varien_Profiler::stop('REGULAR: '.__METHOD__);
        return $url;
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        $str = Mage::helper('core')->removeAccents($str);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getImageUrl()
    {
        $url = false;
        if ($image = $this->getImage()) {
            //$url = Mage::getBaseUrl('media').'menubuilder/menu/'.$image;
            $url = Mage::getBaseUrl('media').'catalog/category/'.$image;
        }
        return $url;
    }

    /**
     * Retrieve URL path
     *
     * @return string
     */
    public function getUrlPath()
    {
        if ($path = $this->getData('url_path')) {
            return $path;
        }

        $path = $this->getUrlKey();

        if ($this->getParentId()) {
            $parentPath = Mage::getModel('menubuilder/menu')->load($this->getParentId())->getCategoryPath();
            $path = $parentPath.'/'.$path;
        }

        $this->setUrlPath($path);

        return $path;
    }

    /**
     * Get parent category object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getParentCategory()
    {
        return Mage::getModel('menubuilder/menu')->load($this->getParentId());
    }

    /**
     * Get parent category identifier
     *
     * @return int
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }

    /**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), array($this->getId()));
    }

    /**
     * Retrieve dates for custom design (from & to)
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    private function _getAttribute($attributeCode)
    {
        return $this->getResource()->getAttribute($attributeCode);
    }

    /**
     * Get all children categories IDs
     *
     * @param boolean $asArray return resul as array instead of comma-separated list of IDs
     * @return array|string
     */
    public function getAllChildren($asArray = false)
    {
        $children = $this->getResource()->getAllChildren($this);
        if ($asArray) {
            return $children;
        }
        return implode(',', $children);
    }

    /**
     * Retrieve children ids comma separated
     *
     * @return string
     */
    public function getChildren()
    {
        return implode(',', $this->getResource()->getChildren($this, false));
    }

    /**
     * Retrieve Stores where isset category Path
     * Return comma separated string
     *
     * @return string
     */
    public function getPathInStore()
    {
        $result = array();
        //$path = $this->getTreeModelInstance()->getPath($this->getId());
        $path = array_reverse($this->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == Mage::app()->getStore()->getRootCategoryId()) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }

    /**
     * Check category id exising
     *
     * @param   int $id
     * @return  bool
     */
    public function checkId($id)
    {
        return $this->_getResource()->checkId($id);
    }

    /**
     * Get array categories ids which are part of category path
     * Result array contain id of current category because it is part of the path
     *
     * @return array
     */
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve level
     *
     * @return int
     */
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData('level');
    }

    /**
     * Verify category ids
     *
     * @param array $ids
     * @return bool
     */
    public function verifyIds(array $ids)
    {
        return $this->getResource()->verifyIds($ids);
    }

    /**
     * Retrieve Is Node has children flag
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->_getResource()->getChildrenAmount($this) > 0;
    }

    /**
     * Retrieve Request Path
     *
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_getData('request_path');
    }

    /**
     * Retrieve Name data wraper
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Before delete process
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            Mage::throwException("Can't delete root category.");
        }
        return parent::_beforeDelete();
    }


    /**
     * Retrieve categories by parent
     *
     * @param int $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return mixed
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        $categories = $this->getResource()
            ->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
        return $categories;
    }

    /**
     * Retrieve categories by parent
     *
     * @param int $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return mixed
     */
    public function getNodes($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        $categories = $this->getResource()
            ->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
            
		return $categories;
    }

    /**
     * Return parent categories of current category
     *
     * @return array
     */
    public function getParentCategories()
    {
        return $this->getResource()->getParentCategories($this);
    }

    /**
     * Retuen children categories of current category
     *
     * @return array
     */
    public function getChildrenCategories()
    {
        return $this->getResource()->getChildrenCategories($this);
    }

    /**
     * Check is category in list of store categories
     *
     * @param Mage_Catalog_Model_Category $category
     * @return boolean
     */
    public function isRoot($nodeId = null)
    {
    	if(	(int) $this->getData('level') === 1
    		|| (int) $this->getParentId() === self::TREE_ROOT_ID
    		|| $this->_isRoot ) {
    			return true;
    	}
    	return false;
    }
    
    public function setIsRoot($bool = false)
    {
    	$this->_isRoot = (bool) $bool;
    	return $this;
    }
    
    /**
     * Retrive menu id by code
     *
     * @param   string $sku
     * @return  integer
     */
    public function getIdByCode($code)
    {
        return $this->_getResource()->getIdByCode($code);
    }
    
    /**
     * Validate attribute values
     *
     * @throws Mage_Eav_Model_Entity_Attribute_Exception
     * @return bool|array
     */
    public function validate()
    {
        return $this->_getResource()->validate($this);
    }
    
    public function getTypeInstance()
    {
    	return Mage::getSingleton('menubuilder/menu_type_' . strtolower($this->getType()))->setData($this->getData());
    	if($this->getType() && ! isset($this->_typeInstance[$this->getType()])) {
    		$this->_typeInstance[$this->getType()] = Mage::getSingleton('menubuilder/menu_type_' . strtolower($this->getType()));
    		$this->_typeInstance[$this->getType()]->setData($this->getData());    		
    	}
    	
    	return $this->_typeInstance[$this->getType()];
    }
    
    public function getType()
    {
    	if($this->getMenuCode()) {
    		return 'root';
    	}
    	
    	if($this->getData('link_to')) {
    		return $this->getData('link_to');
    	}
    	    	
    	return 'undefined';
    }
    
    public function isActive()
    {
    	return $this->getData('is_active');
    }
    
    public function getUrl($getBaseUrl = false)
    {
    	return $this->getTypeInstance()->getUrl($getBaseUrl);
    }
    
    public function hasUrl()
    {
    	return (bool) (! is_null($this->getUrl()) && strlen($this->getUrl()) >= 1);
    }
    
    protected function _addBaseUrl($url = null)
    {
    	return $url ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $url : null;
    }
    
    public function getIsActive()
    {
    	$active = ($this->isActive() && $this->typeIsActive());   
    	$date = $this->getCustomActiveDate();
    	
        if (! $active || !array_key_exists('from', $date) || !array_key_exists('to', $date)) {        	
            return $active;
        }

        // compare dates
        return (Mage::app()->getLocale()->IsStoreDateInInterval(null, $date['from'], $date['to']));
    	
    }
    
    public function typeIsActive()
    {
    	return $this->getTypeInstance()->isActive();
    }

    public function getCustomActiveDate()
    {
        $result = array();
        $result['from'] = $this->getData('active_from');
        $result['to'] = $this->getData('active_to');

        return $result;
    }
    
}