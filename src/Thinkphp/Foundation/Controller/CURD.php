<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Thinkphp\Foundation\Controller;

use think\Model;
use think\model\Collection;
use Xin\Contracts\Repository\Factory as RepositoryFactory;
use Xin\Thinkphp\Facade\Hint;
use Xin\Thinkphp\Http\Requestable;
use Xin\Thinkphp\Repository\Repository;

/**
 * @method mixed filterable($input, callable $next)
 * @method mixed detailable($input, callable $next)
 * @method mixed validateable($input, callable $next)
 * @method mixed storeable($input, callable $next)
 * @method mixed showable($input, callable $next)
 * @method mixed updateable($input, callable $next)
 * @method mixed deleteable($input, callable $next)
 * @method mixed recoveryable($input, callable $next)
 * @method mixed restoreable($input, callable $next)
 * @property Requestable $request
 */
trait CURD
{

	/**
	 * 返回首页数据
	 * @return mixed
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public function index()
	{
		$search = $this->request->param();

		$data = $this->attachHandler('filterable')
			->repository()
			->paginate(
				$search,
				$this->property('indexWith', []),
				$this->request->paginate()
			);

		return $this->renderIndex($data);
	}

	/**
	 * 渲染首页数据
	 * @param Collection $data
	 * @return \think\Response
	 */
	protected function renderIndex($data)
	{
		return Hint::result($data);
	}

	/**
	 * 返回详情数据
	 * @return mixed
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public function detail()
	{
		$id = $this->request->idWithValid();

		$info = $this->attachHandler('detailable')
			->repository()
			->detailById($id);

		return $this->renderDetail($info);
	}

	/**
	 * 渲染详情数据
	 * @param Model $info
	 * @return \think\Response
	 */
	protected function renderDetail($info)
	{
		return Hint::result($info);
	}

	/**
	 * 创建数据操作
	 * @return \think\Response
	 */
	public function create()
	{
		$data = $this->request->param();

		$info = $this->attachHandler([
			'validateable', 'storeable'
		])
			->repository()
			->store($data);

		return $this->renderCreateResponse($info);
	}

	/**
	 * 数据创建成功响应
	 * @param Model $info
	 * @return \think\Response
	 */
	protected function renderCreateResponse($info)
	{
		return Hint::success('创建成功！', $this->jumpUrl(), $info);
	}

	/**
	 * 更新数据操作
	 * @return \think\Response
	 */
	public function update()
	{
		$id = $this->request->idWithValid();

		$data = $this->request->param();
		$info = $this->attachHandler([
			'validateable', 'updateable'
		])->repository()->updateById($id, $data);

		return $this->renderUpdateResponse($info);
	}

	/**
	 * 数据更新成功响应
	 * @param Model $info
	 * @return \think\Response
	 */
	protected function renderUpdateResponse($info)
	{
		return Hint::success('保存成功！', $this->jumpUrl(), $info);
	}

	/**
	 * 删除/回收数据操作
	 * @return \think\Response
	 */
	public function delete()
	{
		$ids = $this->request->idsWithValid();
		$isForce = $this->request->param('force/d', 0);

		if ($isForce) {
			$result = $this->attachHandler('deleteable')
				->repository()
				->deleteByIdList($ids);
		} else {
			$result = $this->attachHandler('recoveryable')
				->repository()
				->recoveryByIdList($ids);
		}

		return $this->renderDeleteResponse($result);
	}

	/**
	 * 渲染删除响应
	 * @param array $result
	 * @return \think\Response
	 */
	protected function renderDeleteResponse($result)
	{
		return Hint::success('删除成功！', null, $result);
	}

	/**
	 * @return \think\Response
	 */
	public function restore()
	{
		$ids = $this->request->idsWithValid();

		$result = $this->attachHandler('restoreable')
			->repository()->restoreByIdList($ids);

		return $this->renderRestoreResponse($result);
	}

	/**
	 * 渲染数据恢复响应
	 * @param array $result
	 * @return \think\Response
	 */
	protected function renderRestoreResponse($result)
	{
		return Hint::success('恢复成功！', null, $result);
	}

	/**
	 * @param string|array $scenes
	 * @return $this
	 */
	protected function attachHandler($scenes)
	{
		$scenes = is_array($scenes) ? $scenes : [$scenes];
		$thisRef = new \ReflectionClass($this);

		$repository = $this->repository();
		foreach ($scenes as $scene) {
			if ($thisRef->hasMethod($scene)) {
				$method = $thisRef->getMethod($scene);
				$method->setAccessible(true);
				$repository->$scene($method->getClosure($this));
			}
		}

		return $this;
	}

	/**
	 * @return Repository
	 */
	protected function repository(): Repository
	{
		$repository = $this->repositoryTo();
		if ($repository instanceof Repository) {
			return $repository;
		}

		return app(RepositoryFactory::class)->repository($repository);
	}

	/**
	 * @return string|\Xin\Contracts\Repository\Repository
	 */
	abstract protected function repositoryTo();

	/**
	 * 获取属性
	 *
	 * @param string $property
	 * @param mixed $default
	 * @return mixed
	 */
	protected function property($property, $default = null)
	{
		if (property_exists($this, $property)) {
			return $this->{$property};
		}

		return $default;
	}

	/**
	 * 跳转地址
	 *
	 * @param string $fallback
	 * @return string
	 */
	protected function jumpUrl($fallback = 'index')
	{
		return null;
	}
}
