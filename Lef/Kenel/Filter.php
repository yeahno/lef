<?php
namespace Kenel;
/**
 * ============================================================================
 * 数据过滤
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * @author 		yanle<574608908@qq.com>
 * @version		1.0
 * @copyright	Copyright (c) 2014-2020 by yanle All
 * ============================================================================
 */
class Filter{
	//去除标签，编码特殊字符。
	const PARAM_STRING = 'String';
	//URL-encode 字符串，去除或编码特殊字符。
	const PARAM_ENCODE = 'Encode';
	//HTML 转义字符 '"<>& 以及 ASCII 值小于 32 的字符。
	const PARAM_SPECIAL_CHARS='SpecialChars';
	//过滤整数
	const PARAM_INT='Int';
	//过滤单精度
	const PARAM_FLOAT='Float';
	//过滤双精度
	const PARAM_DOUBLE='Double';
	//不进行处理
	const PARAM_IGNORE='Ignore';

	/**
	 * 统一过滤
	 * @param string|array $value
	 * @param string $filter_type
	 * @return mixed
	 */
	public function sanitize($value,$filter_type=self::PARAM_STRING){
		if(is_array($value)){
			$value=array_map(array($this,'sanitize'),$value);
		}else{
			$method='sanitize'.$filter_type;
			if(!method_exists($this,$method)){
				throw new \DomainException('Sanitize filter ' . $filter_type . ' is not supported',500);
			}
			$value=$this->$method($value);
		}
		return $value;
	}
	/**
	 * 去除标签，编码特殊字符。
	 * @param string $value
	 * @param string
	 */
	public function sanitizeString($value){
		return filter_var($value,FILTER_SANITIZE_STRING);
	}
	/**
	 * url-encode 字符串，编码特殊字符。
	 * @param string $value
	 * @return string
	 */
	public function sanitizeEncode($value){
		return filter_var($value,FILTER_SANITIZE_ENCODED);
	}
	/**
	 * 转义HTML字符（ '"<>& ）以及 ASCII 值小于 32 的字符
	 * @param string $value
	 * @return string
	 */
	public function sanitizeSpecialChars($value){
		return filter_var($value,FILTER_SANITIZE_SPECIAL_CHARS);
	}
	/**
	 * 过滤整数
	 * @param int $value
	 * @return int
	 */
	public function sanitizeInt($value){
		return (int) $value;
	}
	/**
	 * 过滤单精度
	 * @param float $value
	 * @return float
	 */
	public function sanitizeFloat($value){
		return (float) $value;
	}
	/**
	 * 过滤双精度
	 * @param double $value
	 * @return double
	 */
	public function sanitizeDouble($value){
		return (double) $value;
	}
	/**
	 * 不过滤,只清除两边空格
	 * @param string $value
	 * @return string
	 */
	public function sanitizeIgnore($value){
		return trim($value);
	}
}
