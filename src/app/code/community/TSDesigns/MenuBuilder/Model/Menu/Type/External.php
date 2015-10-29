<?php
/**
 * TSDesigns_MenuBuilder_Model_Menu_Type_External
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
class TSDesigns_MenuBuilder_Model_Menu_Type_External extends TSDesigns_MenuBuilder_Model_Menu_Type_Abstract
{
	protected $_identifier = 'external';
	protected $_model = null;
	
	public function getUrl($addBaseUrl = false)
	{
    	$url = $this->getData('link_to_external');
    	return ! preg_match('/^(http\:\/\/)/is', $url) ? 'http://' . $url : $url;
	}
	
	public function isActive()
	{
		return $this->getData('link_to_external');
	}
	
	public function isValid()
	{
		if( ! preg_match('/\./', $this->getData('link_to_external'))) {
			$this->addWarning(Mage::helper('menubuilder')->__('External link seems to be incorrect'));
			$valid = false;			
		}
		
		return parent::isValid();
	}
}