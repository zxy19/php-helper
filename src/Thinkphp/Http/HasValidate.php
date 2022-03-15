<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Thinkphp\Http;

use think\exception\ValidateException;
use think\Validate;

/**
 * Trait RequestValidate
 *
 * @mixin Requestable
 */
trait HasValidate
{

	/**
	 * 验证数据
	 *
	 * @param string|array $name 要获取的参数名称
	 * @param string|array $validate 验证器名或者验证规则数组
	 * @param bool $batch 是否批量验证
	 * @return array
	 */
	public function validate($name, $validate, bool $batch = false)
	{
		if (!is_array($validate)) {
			$validate = [
				'rules' => $validate,
			];
		}

		if (is_array($validate['rules'])) {
			$v = new Validate();
			$v->rule(
				$validate['rules'],
				isset($validate['fields']) ? $validate['fields'] : []
			);
		} else {
			$validator = $validate['rules'];
			if (strpos($validator, '.')) {
				// 支持场景
				[$validator, $scene] = explode('.', $validator);
			}

			/** @var Validate $v */
			$v = app($validator);

			if (isset($scene)) {
				$v->scene($scene);
			}
		}

		if (isset($validate['messages'])) {
			$v->message($validate['messages']);
		}

		// 是否批量验证
		$v->batch($batch);

		if (empty($name)) {
			$data = $this->param();
		} else {
			$data = $this->only($name);
		}

		if (!$v->check($data)) {
			throw new ValidateException($v->getError());
		}

		return $data;
	}

	/**
	 * 获取ID 列表
	 *
	 * @param string $field
	 * @return array
	 * @see validIds alias
	 * @deprecated
	 */
	public function idsWithValid($field = 'ids')
	{
		return $this->validIds($field);
	}

	/**
	 * 获取ID并验证
	 *
	 * @param string $field
	 * @return int
	 * @see validId alias
	 * @deprecated
	 */
	public function idWithValid($field = 'id')
	{
		return $this->validId($field);
	}

	/**
	 * 获取整形数据并验证
	 *
	 * @param string $field
	 * @param array $array
	 * @param mixed $default
	 * @return mixed
	 * @deprecated
	 * @see validIntIn alias
	 */
	public function intWithValidArray($field, $array, $default = null)
	{
		return $this->validIntIn($field, $array, $default);
	}

	/**
	 * 获取字符串数据并验证
	 *
	 * @param string $field
	 * @param mixed $default
	 * @param string $filter
	 * @return string
	 * @deprecated
	 * @see validString alias
	 */
	public function stringWithValid($field, $default = null, $filter = '')
	{
		return $this->validString($field, $default, $filter);
	}

	/**
	 * 获取ID并验证
	 *
	 * @param string $field
	 * @return int
	 * @see validId alias
	 */
	public function validId($field = 'id')
	{
		$id = $this->param("{$field}/d");
		if ($id < 1) {
			throw new ValidateException("param {$field} invalid.");
		}

		return $id;
	}

	/**
	 * 获取ID 列表
	 *
	 * @param string $field
	 * @return array
	 */
	public function validIds($field = 'ids')
	{
		$ids = $this->ids($field);
		if (empty($ids)) {
			throw new ValidateException("param {$field} invalid.");
		}

		return $ids;
	}

	/**
	 * 获取整形数据并验证
	 *
	 * @param string $field
	 * @param array $values
	 * @param mixed $default
	 * @return mixed
	 */
	public function validIntIn($field, $values, $default = null)
	{
		$int = $this->param("{$field}/d", $default);
		if (!in_array($int, $values)) {
			throw new ValidateException("param {$field} invalid.");
		}

		return $int;
	}

	/**
	 * 获取字符串数据并验证
	 *
	 * @param string $field
	 * @param mixed $default
	 * @param string $filter
	 * @return string
	 */
	public function validString($field, $default = null, $filter = '')
	{
		$value = $this->param((string)$field, $default, $filter);

		if (empty($value)) {
			throw new ValidateException("param {$field} invalid.");
		}

		return $value;
	}

}
