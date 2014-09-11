<?php

/**
 *	Builder Manager. A class for constructing Laravel fluent/Eloquent queries
 *	in a way that allows you to easily add and remove SQL.
 *
 *	Copyright 2014 - Dave Hodgins
 */

namespace Heroicpixels\Buildermanager;

use Illuminate\Database\Query\Builder as Builder;

class BuilderManager {
	
	protected $builder;
	protected $methods;
	
	public function __construct(Builder $builder) {
		$this->builder = $builder;
	}
	
	/**
	 *	Loop our array of Builder methods and apply each to the Builder object
	 */
	public function call() {
		foreach ( $this->methods as $k => $v ) {
			if ( is_array($v) && sizeof($v) > 0 ) {
				if ( $k != 'select' ) {
					foreach ( $v as $v1 ) {
						if ( is_array($v1) ) {
							call_user_func_array(array($this->builder, $k), $v1);	
						}
					}
				} else {
					call_user_func_array(array($this->builder, $k), $v);
				}
			}
		}
		return $this->builder;
	}
	
	/**
	 *	Execute query and get Builder results
	 */
	public function get() {
		return $this->call()->get();	
	}
	
	/**
	 *	Get Builder object
	 */
	public function getBuilder() {
		return $this->builder;
	}
	
	/**
	 *	Pass BuilderManager method calls to the Builder object, if the method exists in the latter.
	 */
	public function __call($method, $parameters) {
		
		if ( method_exists($this->builder, $method) && sizeof($parameters) >= 1 ) {
			if ( !isset($this->methods[$method]) || !is_array($this->methods[$method]) ) {
				$this->methods[$method] = array();
			}
			if ( $method != 'select' ) {
				foreach ( $parameters as $k => $v ) {
					if ( is_bool($v) ) {
						unset($parameters[$k]);	
					}
				}
				$key = array_shift($parameters);
				$hasFalse = in_array(false, $parameters);
				if ( !$hasFalse ) {
					$this->methods[$method][$key] = $parameters;
				}
			} else {
				$parameters = is_array($parameters) ? current($parameters) : array($parameters);
				$hasFalse = in_array(false, $parameters);
				$parameters = array_filter($parameters, 'trim');
				$func = $hasFalse ? 'array_diff' : 'array_merge';
				$this->methods[$method] = array_unique($func($this->methods[$method], $parameters));
			}
		}
	}
	
}