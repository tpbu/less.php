<?php

namespace Less\Node;

class Expression {

	public $value = array();
	public $parens = false;
	public $parensInOp = false;

	public function __construct($value) {
		$this->value = $value;
	}

	public function compile($env) {

		$inParenthesis = $this->parens && !$this->parensInOp;
		$doubleParen = false;
		if( $inParenthesis ) {
			$env->inParenthesis();
		}

		if (is_array($this->value) && count($this->value) > 1) {

			$ret = array();
			foreach($this->value as $e){
				$ret[] = $e->compile($env);
			}
			$returnValue = new \Less\Node\Expression($ret);

		} else if (is_array($this->value) && count($this->value) == 1) {

			if( !isset($this->value[0]) ){
				$this->value = array_slice($this->value,0);
			}

			if( property_exists($this->value[0], 'parens') && $this->value[0]->parens && !$this->value[0]->parensInOp ){
				$doubleParen = true;
			}

			$returnValue = $this->value[0]->compile($env);
		} else {
			$returnValue = $this;
		}
		if( $inParenthesis ){
			$env->outOfParenthesis();
		}
		if( $this->parens && $this->parensInOp && !$env->isMathsOn() && !$doubleParen ){
			$returnValue = new \Less\Node\Paren($returnValue);
		}
		return $returnValue;
	}

	public function toCSS ($env) {

		$ret = array();
		foreach($this->value as $e){
			$ret[] = method_exists($e, 'toCSS') ? $e->toCSS($env) : '';
		}

		return implode(' ',$ret);
	}

	function throwAwayComments() {

		if( is_array($this->value) ){
			$new_value = array();
			foreach($this->value as $v){
				if( $v instanceof \Less\Node\Comment ){
					continue;
				}
				$new_value[] = $v;
			}
			$this->value = $new_value;
		}
	}
}
