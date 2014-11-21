<?php
require_once ("ASTObject.class.php");

/**
 * @author Pieter Moens, Nick Steijaert, Michael Steenbeek
 * LeafNode is part of a abstract syntax tree pattern used for parsing SPARQL data.
 */
class LeafNode extends ASTObject {

	public function __construct($name) {
		$this -> setName($name);
	}

}
?>