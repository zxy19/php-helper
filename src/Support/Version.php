<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @author 晋<657306123@qq.com>
 */

namespace Xin\Support;

/**
 * 版本检测器
 * 当前版本大于新版本 Version::check( '1.20.63.56' , '1.20.63.55.56' )===1;
 * 当前版本等于新版本 Version::check( '1.20.63.56' , '1.20.63.056' )===0;
 * 当前版本小于新版本 Version::check( '1.20.62.56' , '1.20.63.056' )===-1;
 * 当前版本大于新版本 Version::gt( '1.20.63.56' , '1.20.63.55.56' )===true;
 * 当前版本等于新版本 Version::eq( '1.20.63.56' , '1.20.63.056' )===true;
 * 当前版本小于新版本 Version::lt( '1.20.62.56' , '1.20.63.056' )===true;
 */
final class Version
{

	/**
	 * 当前版本大于新版本
	 *
	 * @param string $current
	 * @param string $new
	 * @return bool
	 */
	public static function gt($current, $new)
	{
		return self::check($current, $new) === 1;
	}

	/**
	 * 版本检测
	 *
	 * @param $current
	 * @param $new
	 * @return int
	 */
	public static function check($current, $new)
	{
		if ($current == $new) {
			return 0;
		}

		$current = explode(".", ltrim($current, 'v'));
		$new = explode(".", ltrim($new, 'v'));

		foreach ($current as $k => $cur) {
			if (isset($new[$k])) {
				if ($cur < $new[$k]) {
					return -1;
				} elseif ($cur > $new[$k]) {
					return 1;
				}
			} else {
				return 1;
			}
		}

		return count($new) == count($current) ? 0 : -1;
	}

	/**
	 * 当前版本大于或等于新版本
	 *
	 * @param string $current
	 * @param string $new
	 * @return bool
	 */
	public static function egt($current, $new)
	{
		$res = self::check($current, $new);

		return $res === 1 || $res === 0;
	}

	/**
	 * 当前版本等于新版本
	 *
	 * @param string $current
	 * @param string $new
	 * @return bool
	 */
	public static function eq($current, $new)
	{
		return self::check($current, $new) === 0;
	}

	/**
	 * 当前版本小于新版本
	 *
	 * @param string $current
	 * @param string $new
	 * @return bool
	 */
	public static function lt($current, $new)
	{
		return self::check($current, $new) === -1;
	}

	/**
	 * 当前版本小于或等于新版本
	 *
	 * @param string $current
	 * @param string $new
	 * @return bool
	 */
	public static function elt($current, $new)
	{
		$res = self::check($current, $new);

		return $res === -1 || $res === 0;
	}

}
