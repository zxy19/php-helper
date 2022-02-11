<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Thinkphp\Foundation\Model;

use think\db\Query;
use Xin\Support\SQL;

/**
 * @mixin \think\Model
 * @method self plainList()
 * @method self simple()
 * @method self search(array $data, array $withoutFields = [])
 */
trait Modelable
{

	/**
	 * 获取数据列表
	 *
	 * @param mixed $query
	 * @param array $options
	 * @return \think\Collection
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function getList($query = [], $options = [])
	{
		return static::plainQuery($query, $options)->select();
	}

	/**
	 * 获取数据分页
	 *
	 * @param mixed $query
	 * @param array $options
	 * @param mixed $listRows
	 * @param bool $simple
	 * @return \think\Paginator
	 * @throws \think\db\exception\DbException
	 */
	public static function getPaginate($query, $options = [], $listRows = 15, $simple = false)
	{
		return static::plainQuery($query, $options)->paginate($listRows, $simple);
	}

	/**
	 * 获取简单的信息数据
	 *
	 * @param mixed $query
	 * @param array $options
	 * @return self
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function getSimpleInfo($query, $options = [])
	{
		$info = static::plainQuery($query, $options)->find();

		return static::resolvePlain($info, $options);
	}

	/**
	 * 获取简单的信息数据
	 *
	 * @param int $id
	 * @param array $options
	 * @return self
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function getSimpleInfoById($id, $options = [])
	{
		$info = static::plainQuery(null, $options)->find($id);

		return static::resolvePlain($info, $options);
	}

	/**
	 * 获取简单的信息数据
	 *
	 * @param mixed $query
	 * @param array $options
	 * @return self
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @deprecated
	 */
	public static function getPlain($query, $options = [])
	{
		return self::getSimpleInfo($query, $options);
	}

	/**
	 * 获取简单的信息数据
	 *
	 * @param int $id
	 * @param array $options
	 * @return self
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @deprecated
	 */
	public static function getPlainById($id, $options = [])
	{
		return self::getSimpleInfoById($id, $options);
	}

	/**
	 * 简单数据额外处理
	 *
	 * @param self $info
	 * @param array $options
	 * @return self
	 */
	protected static function resolvePlain($info, $options = [])
	{
		return $info;
	}

	/**
	 * 获取数据详细信息
	 *
	 * @param mixed $query
	 * @param array $with
	 * @param array $options
	 * @return self
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function getDetail($query, $with = [], $options = [])
	{
		$query = static::with($with)->where($query);

		$info = static::applyOptions($query, $options)->find();

		return static::resolveDetail($info, $options);
	}

	/**
	 * 根据主键ID获取数据详细信息
	 *
	 * @param int $id
	 * @param array $with
	 * @param array $options
	 * @return self
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function getDetailById($id, $with = [], $options = [])
	{
		$info = static::applyOptions(static::with($with), $options)->find($id);

		return static::resolveDetail($info, $options);
	}

	/**
	 * 详细数据额外处理
	 *
	 * @param self $info
	 * @param array $options
	 * @return self
	 */
	protected static function resolveDetail($info, $options = [])
	{
		return $info;
	}

	/**
	 * 获取列表要查询的字段列表，一般用于接口列表查询
	 *
	 * @return array
	 */
	public static function getSimpleFields()
	{
		return static::getPlainFields();
	}

	/**
	 * 获取列表要查询的字段列表，一般用于接口列表查询
	 *
	 * @return array
	 * @deprecated
	 */
	public static function getPlainFields()
	{
		return [];
	}

	/**
	 * 获取要公开的字段列表，一般用于管理查询数据
	 * @return string[]
	 */
	public static function getPublicFields()
	{
		return static::getSimpleFields();
	}

	/**
	 * 获取要搜索的字段列表
	 * @return array
	 */
	public static function getSearchFields()
	{
		$allowSearchFields = static::getSimpleFields();

		$keywordField = static::getSearchKeywordParameter();
		if ($keywordField) {
			return array_merge($allowSearchFields, is_array($keywordField) ? [
				$keywordField[0] => $keywordField[1]
			] : [$keywordField]);
		}

		return $allowSearchFields;
	}

	/**
	 * 获取关键字搜索参数
	 * @return string
	 */
	public static function getSearchKeywordParameter()
	{
		return "keywords";
	}

	/**
	 * 简单数据查询作用域
	 * @param \think\db\Query $query
	 * @deprecated
	 */
	public function scopePlainList(Query $query)
	{
		$query->field(static::getSimpleFields());
	}

	/**
	 * 简单数据查询作用域
	 * @param \think\db\Query $query
	 */
	public function scopeSimple(Query $query)
	{
		$query->field(static::getSimpleFields() ?: static::getPlainFields());
	}

	/**
	 * 搜索数据作用域
	 * @param Query $query
	 * @param array $data
	 * @param array $withoutFields
	 * @return void
	 */
	public function scopeSearch(Query $query, array $data, array $withoutFields = [])
	{
		$data = array_filter($data, 'filled');

		$fields = array_diff(static::getSearchFields(), $withoutFields);

		$query->withSearch($fields, $data);
	}

	/**
	 * 标题搜索器
	 * @param Query $query
	 * @param string $value
	 * @return void
	 */
	public function searchKeywordsAttr(Query $query, $value)
	{
		$values = SQL::keywords($value);
		if (empty($values)) {
			return;
		}

		$query->where(implode('|', static::getSearchKeywordFields()), 'like', $values);
	}

	/**
	 * 获取关键字搜索字段
	 * @return string[]
	 */
	public static function getSearchKeywordFields()
	{
		return ["title"];
	}

	/**
	 * 解析基础查询对象
	 *
	 * @param mixed $query
	 * @param array $options
	 * @return \think\db\Query|\think\Model
	 * @deprecated
	 */
	public static function newPlainQuery($query = null, $options = [])
	{
		return static::plainQuery($query, $options);
	}

	/**
	 * 获取基础查询对象
	 *
	 * @param mixed $query
	 * @param array $options
	 * @return \think\db\Query|\think\Model
	 */
	public static function plainQuery($query = null, $options = [])
	{
		$fields = static::getSimpleFields();
		if (isset($options['field'])) {
			if (is_callable($options['field'])) {
				$fields = $options['field']($fields);
			} else {
				$fields = $options['field'];
			}
			unset($options['field']);
		}

		$model = new static;
		$newQuery = $model->field($fields);

		if ($query) {
			$newQuery->where($query);
		}

		return static::applyOptions($newQuery, $options);
	}

	/**
	 * 应用 options
	 *
	 * @param mixed $baseQuery
	 * @param array $options
	 * @return \think\Model|\think\db\Query
	 */
	public static function applyOptions($baseQuery, $options = null)
	{
		if ($options === null) {
			return $baseQuery;
		}

		if (is_callable($options)) {
			return $options($baseQuery);
		}

		foreach ($options as $method => $option) {
			if (method_exists($baseQuery, $method)) {
				if (is_array($option) && in_array($method, ['limit', 'page'])) {
					$baseQuery->$method(...$option);
				} else {
					$baseQuery->$method($option);
				}
			}
		}

		return $baseQuery;
	}

}
