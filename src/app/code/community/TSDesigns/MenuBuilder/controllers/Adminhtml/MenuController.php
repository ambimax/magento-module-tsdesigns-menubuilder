<?php
/**
 * TSDesigns_MenuBuilder_Adminhtml_MenuController
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
class TSDesigns_MenuBuilder_Adminhtml_MenuController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('TSDesigns_MenuBuilder');
    }

    /**
     * Initialize menu from request parameters
     *
     * @return TSDesigns_MenuBuilder_Model_Menu
     */
    protected function _initMenu()
    {
    	if(null === Mage::registry('current_menu')) {
    		$treeRootId = TSDesigns_MenuBuilder_Model_Menu::TREE_ROOT_ID;
	        $menuId  	= (int) $this->getRequest()->getParam('id', false);
	        $isRoot 	= (int) $this->getRequest()->getParam('root');
	        $menu		= Mage::getModel('menubuilder/menu')
	            	 	->setStoreId($this->getRequest()->getParam('store', 0));
	            
	        if ($menuId) {
	            $menu->load($menuId);
	        } 
	        
	        if( ! $menuId && $isRoot) {
	        	$menu->setIsRoot(true);
	        	$menu->setParentId($treeRootId);
	        	$this->getRequest()->setParam('parent', $treeRootId);
	        }
	        
	        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
	            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
	        }
	
	        Mage::register('current_menu', $menu);
    	}
    	
        return Mage::registry('current_menu');
    }
    
    public function indexAction()
    {
    	$this->_forward('edit');
    }

    /**
     * Add new category form
     */
    public function addAction()
    {
        $this->_forward('edit');
    }
    
    public function newAction()
    {
    	$this->_forward('edit');

    	/*
    	$menu = $this->_initMenu();
        Mage::dispatchEvent('menubuilder_menu_new_action', array('menu' => $menu));

    	$this->loadLayout(array(
    		'default',
    		strtolower($this->getFullActionName()),
    	));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
        */    	
    }

    /**
     * Edit category page
     */
    public function editAction()
    {
    	$params['_current'] = true;
        $redirect = false;

        $storeId = (int) $this->getRequest()->getParam('store');
        $parentId = (int) $this->getRequest()->getParam('parent');        
        $_prevStoreId = Mage::getSingleton('admin/session')
            ->getLastViewedStore(true);

        if ($_prevStoreId != null && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $_prevStoreId;
            $redirect = true;
        }

        $menuId = (int) $this->getRequest()->getParam('id');
        $_prevCategoryId = Mage::getSingleton('admin/session')
            ->getLastEditedCategory(true);


        if ($_prevCategoryId
            && !$this->getRequest()->getQuery('isAjax')
            && !$this->getRequest()->getParam('clear')) {
           // $params['id'] = $_prevCategoryId;
            $this->getRequest()->setParam('id',$_prevCategoryId);
            //$redirect = true;
        }

         if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }

        if ($storeId && !$menuId && !$parentId) {
            $store = Mage::app()->getStore($storeId);
            $_prevCategoryId = TSDesigns_MenuBuilder_Model_Menu::TREE_ROOT_ID;
            $this->getRequest()->setParam('id', $_prevCategoryId);
        }

        if (!($menu = $this->_initMenu())) {
            return;
        }        
        /**
         * Check if we have data in session (if duering category save was exceprion)
         */
        $data = Mage::getSingleton('adminhtml/session')->getMenuData(true);
        if (isset($data['general'])) {
            $menu->addData($data['general']);
        }
		
        /**
         * Build response for ajax request
         */
        if ($this->getRequest()->getQuery('isAjax')) {
            // Check for warnings        	
	        $typeInstance = $menu->getTypeInstance();
	        $typeInstance->checkForWarnings();
	        if($typeInstance->hasWarnings()) {
	        	foreach($typeInstance->getWarnings() as $warning) {
	        		$this->_getSession()->addWarning($warning);
	        	}
	        }
            // prepare breadcrumbs of selected category, if any
            $breadcrumbsPath = $menu->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = Mage::getSingleton('admin/session')->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    }
                    else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }         
                    
            Mage::getSingleton('admin/session')
                ->setLastViewedStore($this->getRequest()->getParam('store'));
            Mage::getSingleton('admin/session')
                ->setLastEditedCategory($menu->getId());
            //$this->_initLayoutMessages('adminhtml/session');
            $this->loadLayout();
            
            $this->getResponse()->setBody(
                $this->getLayout()->getMessagesBlock()->getGroupedHtml()
                . $this->getLayout()->getBlock('menu.edit')->getFormHtml()
                . $this->getLayout()->getBlock('menu.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs')
            );
            
            return;
        }
		
        $this->loadLayout();
        $this->_setActiveMenu('catalog/menubuilder');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');

        $this->_addBreadcrumb(Mage::helper('menubuilder')->__('Manage Menu Nodes'),
             Mage::helper('menubuilder')->__('Manage Menu Nodes')
        );
        $this->renderLayout();
    }
    
    /**
     * Menu grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('menubuilder/adminhtml_menu_grid')->toHtml()
        );
    }

    /**
     * Node save
     */
    public function saveAction()
    {
        if (!$menu = $this->_initMenu()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        $isNewNode = false;
        if ($data = $this->getRequest()->getPost()) {
            $menu->addData($data['general']);
            if (!$menu->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId || isset($data['general']['_is_root'])) {
	                $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
                }
                $parentCategory = Mage::getModel('menubuilder/menu')->load($parentId);
                $menu->setPath($parentCategory->getPath());
            }
            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $menu->setData($attributeCode, null);
                }
            }

            $menu->setAttributeSetId($menu->getDefaultAttributeSetId());

            Mage::dispatchEvent('menubuilder_menu_prepare_save', array(
                'menu' => $menu,
                'request' => $this->getRequest()
            ));

            try {
           		$isNewNode = ( ! $menu->getId());
            	$nodeType = $menu->getTypeInstance();
            	if($nodeType->isValid()) {            		
                	$menu->save(); 
                	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('menubuilder')->__($menu->isRoot() ? 'Menu saved' : 'Node saved'));
                	$refreshTree = 'true';
            	} else {
            		foreach($nodeType->getErrors() as $error) {
            			$this->_getSession()->addError($error);
            		}
                	$this->_getSession()->setMenuData($data);
                	$refreshTree = 'false';
            	}
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setMenuData($data);
                $refreshTree = 'false';
            }
        }
        
        	$url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $menu->getId()));        
	        $this->getResponse()->setBody(
	            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
	        );
        return;
        
        
        if($isNewNode && version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
	        $this->getResponse()->setBody(
	            '<script type="text/javascript">parent.reloadFullTree("'. $menu->getId() . '", "'. $storeId . '");</script>'
	        );
        } else {
        	$url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $menu->getId()));        
	        $this->getResponse()->setBody(
	            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
	        );
        }
    }
    
    /**
     * Get tree node (Ajax version)
     */
    public function nodesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($id = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $id);

            if (!$menu = $this->_initMenu()) {
                return;
            }
            
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('menubuilder/adminhtml_menu_edit_tree')
                    ->getTreeJson($menu)
            );
        }
    }

    public function treeAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $id = (int) $this->getRequest()->getParam('id');
        $menu = $this->_initMenu();
		if( ! $storeId && $menu) {
			$this->getRequest()->setParam('id', $menu->getId());
		}

        $block = $this->getLayout()->createBlock('menubuilder/adminhtml_menu_edit_tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody(Zend_Json::encode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'       	=> $block->buildNodeName($root),
                'draggable'   	=> false,
                'allowDrop'   	=> ($root->getIsVisible()) ? true : false,
                'id'          	=> (int) $root->getId(),
                'expanded'    	=> (int) $block->getIsWasExpanded(),
                'store_id'    	=> (int) $block->getStore()->getId(),
                'node_id' 		=> (int) $menu->getId(),
                'root_visible'	=> (int) $root->getIsVisible()
        ))));
    }

    /**
     * Move category tree node action
     */
    public function moveAction()
    {
        $nodeId           = $this->getRequest()->getPost('id', false);
        $parentNodeId     = $this->getRequest()->getPost('pid', false);
        $prevNodeId       = $this->getRequest()->getPost('aid', false);
        $prevParentNodeId = $this->getRequest()->getPost('paid', false);

        try {
            $tree = Mage::getResourceModel('menubuilder/menu_tree')
                ->load();

            $node = $tree->getNodeById($nodeId);
            $newParentNode  = $tree->getNodeById($parentNodeId);
            $prevNode       = $tree->getNodeById($prevNodeId);

            if (!$prevNode || !$prevNode->getId()) {
                $prevNode = null;
            }

            $tree->move($node, $newParentNode, $prevNode);

            Mage::dispatchEvent('menu_node_move',
                array(
                    'category_id' => $nodeId,
                    'prev_parent_id' => $prevParentNodeId,
                    'parent_id' => $parentNodeId
            ));

            $this->getResponse()->setBody("SUCCESS");
        }
        catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
        catch (Exception $e){
            $this->getResponse()->setBody(Mage::helper('menubuilder')->__('Node move error'));
        }

    }

    /**
     * Delete category action
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $menu = Mage::getModel('menubuilder/menu')->load($id);
                Mage::dispatchEvent('menubuilder_controller_menu_node_delete', array('node'=>$menu));

                Mage::getSingleton('admin/session')->setDeletedPath($menu->getPath());

                $menu->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('menubuilder')->__('Node deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('menubuilder')->__('Node delete error'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }
    
    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('admin/catalog/menubuilder');
    }
    
}