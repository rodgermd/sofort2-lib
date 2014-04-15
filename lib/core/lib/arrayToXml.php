<?php
require_once(dirname(__FILE__).'/arrayToXmlException.php');
require_once(dirname(__FILE__).'/xmlToArrayNode.php');
require_once(dirname(__FILE__).'/elements/sofortElement.php');
require_once(dirname(__FILE__).'/elements/sofortTag.php');
require_once(dirname(__FILE__).'/elements/sofortText.php');

/**
 * Array To XML conversion
 *
 * Copyright (c) 2013 SOFORT AG
 *
 * Released under the GNU LESSER GENERAL PUBLIC LICENSE (Version 3)
 * [http://www.gnu.org/licenses/lgpl.html]
 *
 * $Date: 2013-08-23 09:57:31 +0200 (Fri, 23 Aug 2013) $
 * @version SofortLib 2.0.1  $Id: arrayToXml.php 266 2013-08-23 07:57:31Z mattzick $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 * @link http://www.sofort.com/
 */
class ArrayToXml {
	
	/**
	 * Maximum allowed depth
	 * @var int
	 */
	private $_maxDepth = 4;
	
	/**
	 * Represents the parsed array structure
	 * @var string
	 */
	private $_parsedData = null;
	
	
	/**
	 * Loads array into XML representation.
	 * @param array $input
	 * @param int $maxDepth (default 10)
	 * @param boolean $trim (default true)
	 * @throws ArrayToXmlException
	 * @return void
	 */
	public function __construct(array $input, $maxDepth = 10, $trim = true) {
		if ($maxDepth > 50) {
			throw new ArrayToXmlException('Max depth too high.');
		}
		
		$this->_maxDepth = $maxDepth;
		
		if (count($input) == 1) {
			$tagName = key($input);
			$SofortTag = new SofortTag($tagName, $this->_extractAttributesSection($input[$tagName]), $this->_extractDataSection($input[$tagName], $trim));
			$this->_render($input[$tagName], $SofortTag, 1, $trim);
			$this->_parsedData = $SofortTag->render();
		} elseif(!$input) {
			$this->_parsedData = '';
		} else {
			throw new ArrayToXmlException('No valid input.');
		}
	}
	
	
	/**
	 * Returns parsed array as XML structure
	 * Pass both params as null to exclude prolog <?xml version="version" encoding="encoding" ?>
	 * @param string $version (default 1.0)
	 * @param string $encoding (default UTF-8)
	 * @return string
	 */
	public function toXml($version = '1.0', $encoding = 'UTF-8') {
		return !$version && !$encoding
			? $this->_parsedData
			: "<?xml version=\"{$version}\" encoding=\"{$encoding}\" ?>\n{$this->_parsedData}";
	}
	
	
	/**
	 * Static entry point. Options are:
	 *  - version: (default 1.0) version string to put in xml prolog
	 *  - encoding: (default UTF-8) use the specified encoding
	 *  - trim: (default true) Trim values
	 *  - depth: (default 10) Maximum depth to parse the given array, throws exception when exceeded
	 *
	 * @param array $input the input array
	 * @param array $options set additional options to pass to XmlToArray
	 * @throws ArrayToXmlException
	 */
	public static function render(array $input, array $options = array()) {
		$options = array_merge(array(
				'version' => '1.0',
				'encoding' => 'UTF-8',
				'trim' => true,
				'depth' => 10,
			),
			$options
		);
		
		$Instance = new ArrayToXml($input, $options['depth'], $options['trim']);
		return $Instance->toXml($options['version'], $options['encoding']);
	}
	
	
	/**
	 * Checks if current depth is exceeded
	 * @param int $currentDepth
	 * @throws ArrayToXmlException if depth is exceeded
	 * @return void
	 */
	private function _checkDepth($currentDepth) {
		if ($this->_maxDepth && $currentDepth > $this->_maxDepth) {
			throw new ArrayToXmlException("Max depth ({$this->_maxDepth}) exceeded");
		}
	}
	
	
	/**
	 * Creates a new XML node
	 * @param string $name
	 * @param array $attributes
	 * @param array $children
	 * @return SofortTag
	 */
	private function _createNode($name, array $attributes, array $children) {
		return new SofortTag($name, $attributes, $children);
	}
	
	
	/**
	 * Creates a new text node
	 * @param string $text
	 * @param bool $trim
	 * @return SofortText
	 */
	private function _createTextNode($text, $trim) {
		return new SofortText($text, true, $trim);
	}
	
	
	/**
	 * Extracts the attributes section from a XmlToArray'd structure
	 * @param mixed $node (reference)
	 * @return array
	 */
	private function _extractAttributesSection(&$node) {
		$attributes = array();
		
		if (is_array($node) && isset($node['@attributes']) && $node['@attributes']) {
			$attributes = is_array($node['@attributes']) ? $node['@attributes'] : array($node['@attributes']);
			unset($node['@attributes']);
		} elseif (is_array($node) && isset($node['@attributes'])) {
			unset($node['@attributes']);
		}
		
		return $attributes;
	}
	
	
	/**
	 * Extracts the data section from a XmlToArray'd structure
	 * @param mixed $node (reference)
	 * @param boolean $trim
	 * @return array
	 */
	private function _extractDataSection(&$node, $trim) {
		$children = array();
		
		if (is_array($node) && isset($node['@data']) && $node['@data']) {
			$children = array($this->_createTextNode($node['@data'], $trim));
			unset($node['@data']);
		} elseif (is_array($node) && isset($node['@data'])) {
			unset($node['@data']);
		}
		
		return $children;
	}
	
	
	/**
	 * Recursivly renders a XmlToArray'd structure into an object notation
	 * @param mixed $input
	 * @param Tag $ParentTag
	 * @param int $currentDepth
	 * @param boolean $trim
	 * @return void
	 */
	private function _render($input, SofortTag $ParentTag, $currentDepth, $trim) {
		$this->_checkDepth($currentDepth);
		
		if (is_array($input)) {
			foreach ($input as $tagName => $data) {
				$dataIsArray = is_array($data);
				$dataIsArrayKeyDataIsInt = $dataIsArray && is_int(key($data));
				
				if ($dataIsArrayKeyDataIsInt) {
					$this->_checkDepth($currentDepth+1);
					
					foreach ($data as $line) {
						$currentDepth = $this->_renderNode($tagName, $line, $ParentTag, $trim, $currentDepth);
					}
				} else {
					$currentDepth = $this->_renderNode($tagName, $data, $ParentTag, $trim, $currentDepth);
				}
			}
			
			return;
		}
		
		$ParentTag->children[] = $this->_createTextNode($input, $trim);
	}
	
	
	
	/**
	 * Renders a single Node of the structure
	 *
	 * @param string $tagName
	 * @param string $data
	 * @param SofortTag $ParentTag
	 * @param int $trim
	 * @param int $currentDepth
	 * @return int
	 */
	private function _renderNode($tagName, $data, SofortTag $ParentTag, $trim, $currentDepth) {
		$attributes = $this->_extractAttributesSection($data);
		if (is_array($data)) {
			$Tag = $this->_createNode($tagName, $attributes, $this->_extractDataSection($data, $trim));
			$ParentTag->children[] = $Tag;
			$this->_render($data, $Tag, $currentDepth+1, $trim);
		} elseif (is_numeric($tagName)) {
			$ParentTag->children[] = $this->_createTextNode($data, $trim);
		} else {
			$ParentTag->children[] = $this->_createNode($tagName, $attributes, array($this->_createTextNode($data, $trim)));
		}
		
		return $currentDepth;
	}
}
?>