<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Source_Cms
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
class TSDesigns_MenuBuilder_Model_Menu_Source_Cms extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        $options = array();
                
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please select a cms page --'),
                'value' => null
            );
        }
        
        // Get collection and prepare
        $collection = Mage::getResourceModel('menubuilder/menu')->getCmsPageByStore($storeId);
        $pages = $used = array();
        foreach ($collection as $page) {
            $pages[$page['store_id']][$page['page_id']] = array(
               'label' => $page['store_id'] ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $page['title'] : $page['title'],
               'value' => $page['page_id']
            );
        }
        
        // Add all store views
        if(isset($pages[0]) && count($pages[0])) {
	        $options[] = array(
	            'label' => 'All Store Views',
	            'value' => $pages[0],
	        );
        }
        
        // Add by store
        foreach(Mage::app()->getWebsites() as $website) {
            $options[$website->getId()+1] = array(
                'label' => $website->getName(),
                'value' => null//$page[$store->getId()],
            );
            foreach($website->getGroups() as $group) {
	            $options[$website->getId()+1]['value'][$group->getId()] = array(
	                'label' => '&nbsp;&nbsp;' . $group->getName(),
	                'value' => null//$page[$store->getId()],
	            );
                foreach($group->getStores() as $store) {
                    if( ! isset($pages[$store->getId()]) || ! count($pages[$store->getId()])) {
                        continue;
                    }
	                $options[$website->getId()+1]['value'][$group->getId()]['value'][$store->getId()] = array(
	                    'label' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $store->getName(),
	                    'value' => ! isset($pages[$store->getId()]) ? array() : $pages[$store->getId()]
	                );
	            }
	            if( ! is_array($options[$website->getId()+1]['value'][$group->getId()]['value'])
	                || ! count($options[$website->getId()+1]['value'][$group->getId()]['value'])) {
	                unset($options[$website->getId()+1]['value'][$group->getId()]);
	            }
	        }
            if( ! is_array($options[$website->getId()+1]['value']) || ! count($options[$website->getId()+1]['value'])) {
                unset($options[$website->getId()+1]);
            }
        }
       
        return $options;
    }
    
    public function getAllOptions()
    {
    	return $this->toOptionArray();
    }
    
}