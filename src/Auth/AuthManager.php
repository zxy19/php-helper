<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */
namespace Xin\Auth;

use Closure;
use InvalidArgumentException;
use Xin\Contracts\Auth\Guard as GuardContract;
use Xin\Contracts\Auth\User as UserContract;

/**
 * Class AuthManager
 * @method mixed getUserInfo($field = null, $default = null, $abort = true)
 * @method int getUserId($abort = true)
 */
class AuthManager implements GuardContract{
	
	/**
	 * 守卫者列表
	 *
	 * @var array
	 */
	protected $guards = [];
	
	/**
	 * @var array
	 */
	protected $config = [];
	
	/**
	 * 自定义驱动器
	 *
	 * @var array
	 */
	protected $customCreators = [];
	
	/**
	 * 自定义用户提供者
	 *
	 * @var array
	 */
	protected $customProviderCreators = [];
	
	/**
	 * AuthManager constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config){
		$this->config = $config;
	}
	
	/**
	 * @inheritDoc
	 */
	public function guard($name = null){
		$name = $name ?: $this->getDefaultDriver();
		
		if(!isset($this->guards[$name])){
			$this->guards[$name] = $this->resolve($name);
		}
		
		return $this->guards[$name];
	}
	
	/**
	 * @inheritDoc
	 */
	public function shouldUse($name){
		$name = $name ?: $this->getDefaultDriver();
		
		$this->setDefaultDriver($name);
	}
	
	/**
	 * 解决给定守卫者
	 *
	 * @param string $name
	 * @return \Xin\Contracts\Auth\User
	 * @throws \InvalidArgumentException
	 */
	protected function resolve($name){
		$config = $this->getGuardConfig($name);
		
		if(is_null($config)){
			throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
		}
		
		if(isset($this->customCreators[$config['driver']])){
			return $this->callCustomCreator($name, $config);
		}
		
		$driverMethod = 'create'.ucfirst($config['driver']).'Driver';
		if(method_exists($this, $driverMethod)){
			return $this->{$driverMethod}($name, $config);
		}
		
		throw new InvalidArgumentException(
			"Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
		);
	}
	
	/**
	 * 调用一个自定义驱动创建器
	 *
	 * @param string $name
	 * @param array  $config
	 * @return mixed
	 */
	protected function callCustomCreator($name, array $config){
		return $this->customCreators[$config['driver']](
			$name, $config,
			$this->createUserProvider($config['provider'])
		);
	}
	
	/**
	 * 注册一个驱动创建器
	 *
	 * @param string   $driver
	 * @param \Closure $callback
	 * @return \Xin\Auth\AuthManager
	 */
	public function extend($driver, Closure $callback){
		$this->customCreators[$driver] = $callback;
		
		return $this;
	}
	
	/**
	 * 获取守卫者配置信息
	 *
	 * @param string $name
	 * @return array
	 */
	protected function getGuardConfig($name){
		return $this->config['guards'][$name];
	}
	
	/**
	 * 获取默认的守卫者
	 *
	 * @return string
	 */
	public function getDefaultDriver(){
		return $this->config['defaults']['guard'];
	}
	
	/**
	 * 设置默认的守卫者
	 *
	 * @param string $name
	 */
	public function setDefaultDriver($name){
		$this->config['defaults']['guard'] = $name;
	}
	
	/**
	 * 创建用户提供者
	 *
	 * @param string|null $provider
	 * @return \Xin\Contracts\Auth\UserProvider
	 */
	public function createUserProvider($provider = null){
		if(is_null($config = $this->getProviderConfiguration($provider))){
			throw new InvalidArgumentException(
				"Authentication user provider [{$provider}] is not defined."
			);
		}
		
		$driver = isset($config['driver']) ? $config['driver'] : null;
		if(isset($this->customProviderCreators[$driver])){
			return call_user_func(
				$this->customProviderCreators[$driver]
			);
		}
		
		$driverMethod = 'create'.ucfirst($config['driver']).'Provider';
		if(method_exists($this, $driverMethod)){
			return $this->{$driverMethod}($config);
		}
		
		return new $config['use']();
	}
	
	/**
	 * 获取用户提供者配置
	 *
	 * @param string $provider
	 * @return array|void
	 */
	protected function getProviderConfiguration($provider){
		if($provider = $provider ?: $this->getDefaultUserProvider()){
			return $this->config['providers'][$provider];
		}
	}
	
	/**
	 * 获取默认的提供者
	 *
	 * @return string
	 */
	public function getDefaultUserProvider(){
		return $this->config['defaults']['provider'];
	}
	
	/**
	 * 注册一个用户提供者
	 *
	 * @param string   $name
	 * @param \Closure $callback
	 * @return \Xin\Auth\AuthManager
	 */
	public function provider($name, Closure $callback){
		$this->customProviderCreators[$name] = $callback;
		
		return $this;
	}
	
	/**
	 * 获取用户信息
	 *
	 * @return mixed
	 */
	public function user(){
		return $this->getUserInfo(null, null, false);
	}
	
	/**
	 * 获取当前用户的ID
	 *
	 * @return int
	 */
	public function id(){
		return $this->getUserId(false);
	}
	
	/**
	 * 获取当前用户信息 如果不存在将会抛出异常
	 *
	 * @return mixed
	 * @throws \Xin\Auth\AuthenticationException
	 */
	public function authenticate(){
		$user = $this->getUserInfo();
		
		if(empty($user)){
			throw new AuthenticationException(
				$this->getDefaultDriver(),
				$this->getGuardConfig(
					$this->getDefaultDriver()
				)
			);
		}
		
		return $user;
	}
	
	/**
	 * 获取当前用户ID 如果不存在将会抛出异常
	 *
	 * @return mixed
	 * @throws \Xin\Auth\AuthenticationException
	 */
	public function authenticateId(){
		$id = $this->getUserId();
		
		if(empty($id)){
			throw new AuthenticationException(
				$this->getDefaultDriver(),
				$this->getGuardConfig(
					$this->getDefaultDriver()
				)
			);
		}
		
		return $id;
	}
	
	/**
	 * 检查当前用户是否已经授权
	 *
	 * @return bool
	 */
	public function check(){
		return !is_null($this->user());
	}
	
	/**
	 * 检查当前用户是否是访客模式
	 *
	 * @return bool
	 */
	public function guest(){
		return !$this->check();
	}
	
	/**
	 * 设置一个守卫者实例
	 *
	 * @param string       $name
	 * @param UserContract $user
	 * @return $this
	 */
	public function setGuard($name, UserContract $user){
		$this->guards[$name] = $user;
		
		return $this;
	}
	
	/**
	 * 动态调用默认驱动的方法
	 *
	 * @param string $method
	 * @param array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters){
		return $this->guard()->{$method}(...$parameters);
	}
}
