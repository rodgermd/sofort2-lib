<?php
require_once('xmlToArrayException.php');
require_once('xmlToArrayNode.php');

/**
 * XML To Array conversion
 *
 * Copyright (c) 2013 SOFORT AG
 *
 * Released under the GNU LESSER GENERAL PUBLIC LICENSE (Version 3)
 * [http://www.gnu.org/licenses/lgpl.html]
 *
 * $Date: 2013-07-24 14:28:45 +0200 (Wed, 24 Jul 2013) $
 * @version SofortLib 2.0.1  $Id: xmlToArray.php 243 2013-07-24 12:28:45Z mattzick $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 * @link http://www.sofort.com/
 */
class XmlToArray {
	
	/**
	 * Holds start tags in a row.
	 * Used for error reporting and counting the current depth
	 * @var array
	 */
	private $_tagStack = array();
	
	/**
	 * Reference to the current node the parser is at
	 * @var XmlToArrayNode
	 */
	private $_CurrentXmlToArrayNode = null;
	
	/**
	 * Object reference for logging purposes
	 * @var Object
	 */
	private $_Object = null;
	
	/**
	 * stop parsing when maxDepth is exceeded, defaults to no maximum (=0).
	 *
	 * @var $_maxDepth int
	 */
	private $_maxDepth = 0;
	
	private static $_htmlEntityExceptions = array(
		'&euro;' => '€',
	);
	
	/**
	 * Loads XML into array representation.
	 * @param string $input
	 * @param int $maxDepth (default 20)
	 * @throws XmlToArrayException
	 * @return void
	 */
	public function __construct($input, $maxDepth = 20) {
		if (!is_string($input)) throw new XmlToArrayException('No valid input.');
		$this->_maxDepth = $maxDepth;
		
		$XMLParser = xml_parser_create();
		xml_parser_set_option($XMLParser, XML_OPTION_SKIP_WHITE, false);
		xml_parser_set_option($XMLParser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($XMLParser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_character_data_handler($XMLParser, array($this, '_contents'));
		xml_set_default_handler($XMLParser, array($this, '_default'));
		xml_set_element_handler($XMLParser, array($this, '_start'), array($this, '_end'));
		
		if (!xml_parse($XMLParser, $input, true)) {
			$errorCode = xml_get_error_code($XMLParser);
			$message = sprintf('%s. line: %d, char: %d'.($this->_tagStack ? ', tag: %s' : ''),
				xml_error_string($errorCode),
				xml_get_current_line_number($XMLParser),
				xml_get_current_column_number($XMLParser)+1,
				implode('->', $this->_tagStack));
			xml_parser_free($XMLParser);
			throw new XmlToArrayException($message, $errorCode);
		}
		
		xml_parser_free($XMLParser);
	}
	
	
	/**
	 *
	 * Log messages (debugging purpose)
	 * @param string $msg
	 * @param int $type (default 2)
	 * @return <boolean/string>
	 */
	public function log($msg, $type = 2) {
		if (class_exists('Object')) {
			!($this->_Object instanceof Object) && $this->_Object = new Object();
			return $this->_Object->log($msg, $type);
		}
		
		return false;
	}
	
	
	/**
	 * Returns parsed XML as array structure
	 * @param boolean $simpleStructure (default false)
	 * @return array
	 */
	public function toArray($simpleStructure = false) {
		return $this->_CurrentXmlToArrayNode->render($simpleStructure);
	}
	
	
	/**
	 * Static entry point
	 * @param string $input
	 * @param bool $simpleStructure (default false)
	 * @param int $maxDepth only parse XML to the provided depth (default 20)
	 * @throws XmlToArrayException
	 * @return array
	 */
	public static function render($input, $simpleStructure = false, $maxDepth = 20) {
		$Instance = new XmlToArray($input, $maxDepth);
		return $Instance->toArray($simpleStructure);
	}
	
	
	/**
	 * Handles cdata of the XML (user data between the tags)
	 * @param resource $parser a resource handle of the XML parser
	 * @param string $data
	 * @return void
	 */
	private function _contents($parser, $data) {
		if (trim($data) !== '' && $this->_CurrentXmlToArrayNode instanceof XmlToArrayNode) $this->_CurrentXmlToArrayNode->setData($data);
	}
	
	
	/**
	 * Default handler for all other XML sections not implemented as callback
	 * @param resource $parser a resource handle of the XML parser
	 * @param mixed $data
	 * @throws XmlToArrayException
	 * @return void
	 */
	private function _default($parser, $data) {
		$data = trim($data);
		
		if (in_array($data, get_html_translation_table(HTML_ENTITIES))) {
			$this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->setData(html_entity_decode($data));
		} elseif ($data && isset(self::$_htmlEntityExceptions[$data])) {
			$this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->setData(self::$_htmlEntityExceptions[$data]);
		} elseif ($data && is_string($data) && strpos($data, '<!--') === false && strpos($data, '<?xml') === false) {
			if (getenv('sofortDebug') == 'true') {
				trigger_error('Default data handler used. The data passed was: '.$data, E_USER_WARNING);
				throw new XmlToArrayException('Unknown error occurred');
			}
		}
	}
	
	
	/**
	 * Handler for end tags
	 * @param resource $parser a resource handle of the XML parser
	 * @param string $name
	 * @return void
	 */
	private function _end($parser, $name) {
		array_pop($this->_tagStack);
		
		if ($this->_CurrentXmlToArrayNode instanceof XmlToArrayNode) {
			$this->_CurrentXmlToArrayNode->setClosed();
			
			$breaker = 0;
			
			// step up the stack and close all tags as long as current tag is reached in stack
			if ($this->_CurrentXmlToArrayNode->getName() != $name) {
				do {
					$this->_CurrentXmlToArrayNode = $this->_CurrentXmlToArrayNode->getParentXmlToArrayNode();
					$this->_CurrentXmlToArrayNode->setClosed();
					
					if ($breaker > 100) {
						trigger_error('Had to break out from endless loop.', E_USER_WARNING);
						break;
					}
					
					++$breaker;
				} while($this->_CurrentXmlToArrayNode->getName() != $name);
			} elseif ($this->_CurrentXmlToArrayNode->hasParentXmlToArrayNode()) {
				$this->_CurrentXmlToArrayNode = $this->_CurrentXmlToArrayNode->getParentXmlToArrayNode();
			}
		}
	}
	
	
	/**
	 * Handler for start tags
	 * @param resource $parser a resource handle of the XML parser
	 * @param string $name
	 * @param array $attributes
	 * @return void
	 */
	private function _start($parser, $name, $attributes) {
		$this->_tagStack[] = $name;
		
		if ($this->_maxDepth && count($this->_tagStack) > $this->_maxDepth) {
			throw new XmlToArrayException('Parse Error: max depth exceeded.', '7005');
		}
		
		$XmlToArrayNode = new XmlToArrayNode($name, $attributes);
		
		if ($this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->isOpen()) {
			$this->_CurrentXmlToArrayNode->addChild($XmlToArrayNode);
			$XmlToArrayNode->setParentXmlToArrayNode($this->_CurrentXmlToArrayNode);
		}
		
		$this->_CurrentXmlToArrayNode = $XmlToArrayNode;
	}
}

?>