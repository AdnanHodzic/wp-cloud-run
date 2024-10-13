<?php // phpcs:ignoreFile
/**
 * Encodes XML data.
 *
 * Based on code from the Symfony package
 *
 * Copyright (c) 2004-2016 Fabien Potencier <fabien@symfony.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author John Wards <jwards@whiteoctober.co.uk>
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Advanced_Ads_XmlEncoder
{
	/**
	 * @var DOMDocument
	 */
	private $dom;

	/**
	 * @var Advanced_Ads_XmlEncoder
	 */
	private static $instance;

	private function __construct() {}

	/**
	 * @return Advanced_Ads_XmlEncoder
	 */
	public static function get_instance()
	{
		if ( ! isset(self::$instance) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function encode( $data, $options = []) {
		if ( ! extension_loaded( 'simplexml' ) ) {
			throw new Exception( sprintf( __( 'The %s extension(s) is not loaded', 'advanced-ads' ), 'simplexml' ) );
		}
		if ( ! extension_loaded( 'dom' ) ) {
			throw new Exception( sprintf( __( 'The %s extension(s) is not loaded', 'advanced-ads' ), 'dom' ) );
		}

		$this->dom = new DOMDocument();
		$this->dom->preserveWhiteSpace = false;
		$this->dom->formatOutput = true;
		if (isset($options['encoding'])) {
			$this->dom->encoding = $options['encoding'];
		}

		if ( ! is_array($data) ) {
			throw new UnexpectedValueException( _x( 'The data must be an array', 'import_export', 'advanced-ads' ) );
		}

		if (isset($options['skip_root'])) {
			$this->buildXml($this->dom, $data );
		} else {
			// create root <advads-export> tag
			$root = $this->dom->createElement('advads-export');
			$this->dom->appendChild($root);
			$this->buildXml($root, $data );
		}


		return $this->dom->saveXML();
	}

	/**
	 * Parse the data and convert it to DOMElements.
	 */
	private function buildXml(DOMNode $parentNode, $data ) {
		$append = true;

		foreach ($data as $key => $data) {
			if (is_numeric($key) ) {
				$append = $this->appendNode($parentNode, $data, 'item', $key);
			} elseif ( $this->isElementNameValid($key) ) {
				$append = $this->appendNode($parentNode, $data, $key);
			}
		}

		return $append;
	}


	/**
	 * Selects the type of node to create and appends it to the parent.
	 *
	 * @param DOMNode     $parentNode
	 * @param array|object $data
	 * @param string       $nodeName
	 * @param string       $key
	 *
	 * @return bool
	 */
	private function appendNode(DOMNode $parentNode, $data, $nodeName, $key = null) {
		$node = $this->dom->createElement($nodeName);

		if (null !== $key) {
			$node->setAttribute('key', $key);
		}

		$appendNode = false;
		if (is_array($data)) {
			$node->setAttribute('type', 'array' );
			$appendNode = $this->buildXml($node, $data);
		} elseif (is_numeric($data)) {
			$node->setAttribute('type', is_string( $data) ? 'string' : 'numeric' );
			$appendNode = $this->appendText($node, (string) $data);
		} elseif (is_string($data)) {
			$node->setAttribute('type', 'string');
			$appendNode = $this->needsCdataWrapping($data) ? $this->appendCData($node, $data) : $this->appendText($node, $data);
		} elseif (is_bool($data)) {
			$node->setAttribute('type', 'boolean');
			$appendNode = $this->appendText($node, (int) $data);
		} elseif (is_null($data)) {
			$node->setAttribute('type', 'null');
			$appendNode = $this->appendText($node, '');
		}

		if ($appendNode) {
			$parentNode->appendChild($node);
		} else {
			/* translators: %s node data */
			throw new UnexpectedValueException( sprintf( _x( 'An unexpected value could not be serialized: %s', 'import_export', 'advanced-ads' ), var_export($data, true) ) );
		}

		return $appendNode;
	}

	final protected function appendText(DOMNode $node, $val) {
		$nodeText = $this->dom->createTextNode($val);
		$node->appendChild($nodeText);

		return true;
	}

	final protected function appendCData(DOMNode $node, $val) {
		$nodeText = $this->dom->createCDATASection($val);
		$node->appendChild($nodeText);

		return true;
	}

	/**
	 * Checks if a value contains any characters which would require CDATA wrapping.
	 *
	 * @param string $val
	 *
	 * @return bool
	 */
	private function needsCdataWrapping($val) {
		return preg_match('/[<>&]/', $val);
	}

	/**
	 * Checks the name is a valid xml element name.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	final protected function isElementNameValid($name) {
		return $name && false === strpos($name, ' ') && preg_match('#^[\pL_][\pL0-9._:-]*$#ui', $name);
	}

	/**
	 * Decode XML data.
	 *
	 * @throws Exception If an extension is lt loaded.
	 * @throws UnexpectedValueException If XML data is invalid.
	 *
	 * @param string $data XML data.
	 * @return array Decoded XML data.
	 */
	public function decode( $data ) {
		if ( ! extension_loaded( 'simplexml' ) ) {
			/* translators: %s: A name of not loaded extension. */
			throw new Exception( sprintf( __( 'The %s extension(s) is not loaded', 'advanced-ads' ), 'simplexml' ) );
		}
		if ( ! extension_loaded( 'dom' ) ) {
			/* translators: %s: A name of not loaded extension. */
			throw new Exception( sprintf( __( 'The %s extension(s) is not loaded', 'advanced-ads' ), 'dom' ) );
		}


		if ('' === trim($data)) {
			throw new UnexpectedValueException( _x( 'Invalid XML data, it can not be empty', 'import_export', 'advanced-ads' ) );
		}

		$internal_errors = libxml_use_internal_errors( true );

		if ( LIBXML_VERSION < 20900 ) {
			// The `libxml_disable_entity_loading` function has been deprecated in PHP 8.0 because in
			// libxml >= 2.9.0 (that is required by PHP 8), external entity loading is disabled by default,
			// so this function is no longer needed to protect against XXE attacks.
			// phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.libxml_disable_entity_loaderDeprecated
			$disable_entities = libxml_disable_entity_loader( true );
		}

		libxml_clear_errors();

		$dom = new DOMDocument();

		if ( strpos( $data, '<advads-export>' ) === false ) {
			$data = preg_replace('/^<\?xml.*?\?>/', '', $data );
			$data = '<advads-export>' . $data . '</advads-export>';
		}

		$dom->loadXML($data, LIBXML_NONET | LIBXML_NOBLANKS);

		libxml_use_internal_errors( $internal_errors );

		if ( LIBXML_VERSION < 20900 ) {
			// phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.libxml_disable_entity_loaderDeprecated -- see L215ff. for an explanation
			libxml_disable_entity_loader( $disable_entities );
		}

		if ($error = libxml_get_last_error()) {
			libxml_clear_errors();

			/* translators: %s error messages while trying to decode xml file */
			throw new UnexpectedValueException( sprintf( _x( 'XML error: %s', 'import_export', 'advanced-ads' ), $error->message ) );

		}

		// <advads-export>
		$rootNode = $dom->firstChild;

		if ($rootNode->hasChildNodes()) {
			return $this->parseXml($rootNode);
		}
	}

	/**
	 * Parse the input DOMNode into an array or a string.
	 *
	 * @param DOMNode $node xml to parse
	 *
	 * @return array|string
	 */
	private function parseXml(DOMNode $node) {
		// Parse the input DOMNode value (content and children) into an array or a string
		$data = [];
		if ( $node->hasAttributes() ) {
			foreach ($node->attributes as $attr) {
				if (ctype_digit($attr->nodeValue)) {
					$data['@'.$attr->nodeName] = (int) $attr->nodeValue;
				} else {
					$data['@'.$attr->nodeName] = $attr->nodeValue;
				}
			}
		}

		$text_type = isset($data['@type']) ? $data['@type'] : null;
		unset( $data['@type'] );

		// Parse the input DOMNode value (content and children) into an array or a string.
		if (!$node->hasChildNodes()) {
			$value = $node->nodeValue;
		} elseif (1 === $node->childNodes->length && in_array($node->firstChild->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])) {
			$value = $node->firstChild->nodeValue;
		} else {


			$value = [];

			foreach ($node->childNodes as $subnode) {
				$val = $this->parseXml($subnode);

				if ('item' === $subnode->nodeName && is_array($val) && isset($val['@key'])) {
					$a = $val['@key'];
					if (isset($val['#'])) {
						$value[$a] = $val['#'] !== 'null' ? $val['#'] : null;
					} else {
						$value[$a] = $val !== 'null' ? $val : null;
					}

				} else {
					$value[$subnode->nodeName][] = $val === 'null' ? null : $val;
				}
			}
			foreach ($value as $key => $val) {
				if (is_array($val) && 1 === count($val)) {
					$value[$key] = current($val);
				} else if ( is_array( $value[$key] ) && isset( $value[$key]['@key'] ) ) {
					unset( $value[$key]['@key'] );
				}
			}
		}

		if (!count($data)) {
			$value = $this->changeType( $value, $text_type );
			return $value;
		}

		if (!is_array($value)) {
			$value = $this->changeType( $value, $text_type );
			$data['#'] = $value;
			return $data;
		}

		if (1 === count($value) && key($value)) {
			$data[key($value)] = current($value);

			return $data;
		}

		foreach ($value as $key => $val) {
			$data[$key] = $val;
		}

		return $data;
	}

	private function changeType( $text, $type ) {
		if ( $type === 'string' ) return (string) $text;
		if ( $type === 'numeric' ) return 0 + $text;
		if ( $type === 'boolean' ) return (boolean) $text;
		if ( $type === 'array' && $text=== '' ) return [];
		if ( $type === 'null' ) return 'null';
		return $text;
	}
}
