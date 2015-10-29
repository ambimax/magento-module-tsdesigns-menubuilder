<?php
/**
 * TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Edit_Tab_Xml
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
class TSDesigns_MenuBuilder_Block_Adminhtml_Menu_Edit_Tab_Xml extends Mage_Core_Block_Template {
	
	protected $_blockString = '{{block type="menubuilder/menu" name="%s" menu_code="%s"}}';
	protected $_xmlString = '<block type="menubuilder/menu" name="%s" before="-"><action method="setMenuCode"><menu_code>%s</menu_code></action></block>';
	
	protected function _construct() {
		$this->setTemplate ( 'menubuilder/menu/edit/tab/xml.phtml' );
	}
	
	public function getBlockString()
	{
		return sprintf($this->_blockString, $this->getMenuCode(), $this->getMenuCode());
	}
	
	public function getXmlString()
	{
		return $this->formatXmlString(sprintf($this->_xmlString, $this->getMenuCode(), $this->getMenuCode()));
	}
	
	public function getXmlExampleString()
	{
		$string  = '<?xml version="1.0" encoding="UTF-8"?><layout version="0.1.0"><default><reference name="right">';
		$string .= $this->_xmlString;
		$string .= '</reference></default></layout>';
		
		return $this->formatXmlString(sprintf($string, $this->getMenuCode(), $this->getMenuCode()));
	}
	
	/**
	 * @see http://recursive-design.com/blog/2007/04/05/format-xml-with-php/
	 */
	function formatXmlString($xml) {
		
		$xml = preg_replace ( '/(>)(<)(\/*)/', "$1\n$2$3", $xml );
		
		$token = strtok ( $xml, "\n" );
		$result = '';
		$pad = 0;
		$matches = array ();
		
		while ( $token !== false ) :
			if (preg_match ( '/.+<\/\w[^>]*>$/', $token, $matches )) :
				$indent = 0;
			elseif (preg_match ( '/^<\/\w/', $token, $matches )) :
				$pad --;
			elseif (preg_match ( '/^<\w[^>]*[^\/]>.*$/', $token, $matches )) :
				$indent = 1;
			else :
				$indent = 0;
			endif;
			
			$line = str_pad ( $token, strlen ( $token ) + $pad, "\t", STR_PAD_LEFT );
			$result .= $line . "\n";
			$token = strtok ( "\n" );
			$pad += $indent;
		endwhile;
		
		return $result;
	}
	
	public function getBlockName() {
		return $this->getMenuCode ();
	}
	
	public function getMenuCode() {
		return $this->getMenu () ? $this->getMenu ()->getMenuCode () : '...';
	}
}