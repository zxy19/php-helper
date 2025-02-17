<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

use Xin\Support\HigherOrderTapProxy;

if (!function_exists('tap')) {
	/**
	 * Call the given Closure with the given value then return the value.
	 *
	 * @param mixed $value
	 * @param callable|null $callback
	 * @return mixed
	 */
	function tap($value, $callback = null)
	{
		if (is_null($callback)) {
			return new HigherOrderTapProxy($value);
		}

		$callback($value);

		return $value;
	}
}

if (!function_exists('value')) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if (!function_exists('blank')) {
	/**
	 * Determine if the given value is "blank".
	 *
	 * @param mixed $value
	 * @return bool
	 */
	function blank($value)
	{
		if (is_null($value)) {
			return true;
		}

		if (is_string($value)) {
			return trim($value) === '';
		}

		if (is_numeric($value) || is_bool($value)) {
			return false;
		}

		if ($value instanceof Countable) {
			return count($value) === 0;
		}

		return empty($value);
	}
}

if (!function_exists('filled')) {
	/**
	 * Determine if a value is "filled".
	 *
	 * @param mixed $value
	 * @return bool
	 */
	function filled($value)
	{
		return !blank($value);
	}
}

if (!function_exists('windows_os')) {
	/**
	 * Determine whether the current environment is Windows based.
	 *
	 * @return bool
	 */
	function windows_os()
	{
		return strtolower(substr(PHP_OS, 0, 3)) === 'win';
	}
}

if (!function_exists('object_get')) {
	/**
	 * Get an item from an object using "dot" notation.
	 *
	 * @param object $object
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function object_get($object, $key, $default = null)
	{
		if (is_null($key) || trim($key) == '') {
			return $object;
		}

		foreach (explode('.', $key) as $segment) {
			if (!is_object($object) || !isset($object->{$segment})) {
				return value($default);
			}

			$object = $object->{$segment};
		}

		return $object;
	}
}

if (!function_exists('build_mysql_distance_field')) {
	/**
	 * 生成计算位置字段
	 *
	 * @param float $longitude
	 * @param float $latitude
	 * @param string $lng_name
	 * @param string $lat_name
	 * @param string $as_name
	 * @return string
	 */
	function build_mysql_distance_field($longitude, $latitude, $lng_name = 'longitude', $lat_name = 'latitude', $as_name = 'distance')
	{
		$sql = "ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$latitude}*PI()/180-{$lat_name}*PI()/180)/2),2)+COS({$latitude}*PI()/180)*COS({$lat_name}*PI()/180)*POW(SIN(({$longitude}*PI()/180-{$lng_name}*PI()/180)/2),2)))*1000)";
		if ($as_name) {
			$sql .= " AS {$as_name}";
		}

		return $sql;
	}
}

if (!function_exists('analysis_words')) {
	/**
	 * 关键字分词
	 *
	 * @param string $keyword
	 * @param int $num 最大返回条数
	 * @param int $holdLength 保留字数
	 * @return array
	 * @deprecated
	 */
	function analysis_words($keyword, $num = 5, $holdLength = 48)
	{
		if ($keyword === null || $keyword === "") {
			return [];
		}

		if (mb_strlen($keyword) > $holdLength) {
			$keyword = mb_substr($keyword, 0, 48);
		}

		//执行分词
		$pa = new \Xin\Analysis\Analysis('utf-8', 'utf-8');
		$pa->setSource($keyword);
		$pa->startAnalysis();
		$result = $pa->getFinallyResult($num);
		if (empty($result)) return [$keyword];

		return array_unique($result);
	}
}

if (!function_exists('build_keyword_sql')) {
	/**
	 * 编译查询关键字SQL
	 *
	 * @param string $keywords
	 * @return array
	 * @deprecated
	 */
	function build_keyword_sql($keywords)
	{
		$keywords = analysis_words($keywords);

		return array_map(function ($item) {
			return "%{$item}%";
		}, $keywords);
	}
}

if (!function_exists('get_class_const_list')) {
	/**
	 * 获取常量列表
	 *
	 * @param string $class
	 * @return array|bool
	 */
	function get_class_const_list($class)
	{
		try {
			$ref = new \ReflectionClass($class);

			return $ref->getConstants();
		} catch (\ReflectionException $e) {
		}

		return false;
	}
}

if (!function_exists('get_const_value')) {
	/**
	 * 获取常量列表
	 *
	 * @param string $class
	 * @param string $name
	 * @return mixed
	 */
	function get_const_value($class, $name)
	{
		try {
			$ref = new \ReflectionClass($class);
			if (!$ref->hasConstant($name)) {
				return null;
			}

			return $ref->getConstant($name);
		} catch (\ReflectionException $e) {
		}

		return false;
	}
}

if (!function_exists('const_exist')) {
	/**
	 * 类常量是否存在
	 *
	 * @param string $class
	 * @param string $name
	 * @return bool
	 */
	function const_exist($class, $name)
	{
		try {
			$ref = new \ReflectionClass($class);

			return $ref->hasConstant($name);
		} catch (\ReflectionException $e) {
		}

		return false;
	}
}

if (!function_exists('now')) {
	/**
	 * 获取当前时间实例
	 *
	 * @param DateTimeZone|string|null $tz $tz
	 * @return \Carbon\Carbon
	 */
	function now($tz = null)
	{
		return \Carbon\Carbon::now($tz);
	}
}
