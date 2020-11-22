<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Xin\Thinkphp\Foundation\Upload;

use think\exception\HttpException;
use think\exception\ValidateException;
use think\helper\Str;
use think\Request;
use Xin\Thinkphp\Facade\Filesystem;
use Xin\Thinkphp\Facade\Hint;

/**
 * Trait UploadToken
 *
 * @property string callbackAction
 */
trait UploadToken{
	
	/**
	 * 获取上传token
	 *
	 * @param \think\Request $request
	 * @return \think\Response
	 */
	public function token(Request $request){
		Hint::shouldUseApi();
		
		if(!$request->isPost()){
			throw new HttpException(404);
		}
		
		// 上传的文件类型
		$type = $this->uploadType($request);
		
		$file = $request->post('file/a');
		if(empty($file) || !is_array($file)){
			return Hint::error("没有上传文件");
		}
		
		$ext = $this->resolveExt($type, $file);
		$filename = date('YmdHis').Str::random(6);
		$key = $this->savePath($type)."/{$filename}.{$ext}";
		
		$policy = $this->policy($request, $type);
		$token = Filesystem::disk($this->disk())->getUploadToken(
			$key, 300, $policy, true
		);
		
		return Hint::result([
			'key'   => $key,
			'token' => $token,
		]);
	}
	
	/**
	 * 上传策略
	 *
	 * @param \think\Request $request
	 * @param string         $type
	 * @return array
	 */
	protected function policy(Request $request, $type){
		// $returnBody = '{"url":"'.$domain.'/$(key)","key":"$(key)","hash":"$(etag)","fsize":$(fsize)}';
		// '{"app_id":'.$appId.',"url":"'.$domain.'/$(key)","key":"$(key)","hash":"$(etag)","fsize":$(fsize)}';
		
		$uploadType = config('filesystem.disks.'.$this->disk().'.type', 'qiniu');
		if($uploadType === 'qiniu'){
			$policy = [
				'callbackUrl'      => $this->callbackUrl($request),
				'callbackBody'     => $this->callbackBody(),
				'callbackBodyType' => 'application/json',
			];
			
			if($type == 'image'){
				$policy['fsizeLimit'] = 1024 * 1024 * 2;
				$policy['mimeLimit'] = 'image/*';
			}elseif($type == 'video'){
				$policy['fsizeLimit'] = 1024 * 1024 * 10;
				$policy['mimeLimit'] = 'video/*';
			}elseif($type == 'audio'){
				$policy['fsizeLimit'] = 1024 * 1024 * 4;
				$policy['mimeLimit'] = 'audio/*';
			}
			
			return $policy;
		}
		
		throw new HttpException(500, "暂未支持！");
	}
	
	/**
	 * @param string $type
	 * @param array  $file
	 * @return string
	 */
	protected function resolveExt($type, array $file){
		if(!isset($file['type'])){
			throw new ValidateException("文件类型不支持上传。");
		}
		
		$fileType = $file['type'];
		$maps = $this->extMaps();
		if(!isset($maps[$type]) || !isset($maps[$type][$fileType])){
			throw new ValidateException("文件类型不支持上传。");
		}
		
		return $maps[$type][$fileType];
	}
	
	/**
	 * @return \string[][]
	 */
	protected function extMaps(){
		return [
			'image' => [
				'image/png'  => 'png',
				'image/jpeg' => 'jpeg',
				'image/gif'  => 'gif',
				'image/bmp'  => 'bmp',
			],
			'audio' => [
				'audio/mp3' => 'mp3',
				'audio/wma' => 'wma',
				'audio/ogg' => 'ogg',
			],
			'video' => [
				'video/mp3' => 'mp4',
			],
		];
	}
	
	/**
	 * @param \think\Request $request
	 * @return string
	 */
	protected function callbackUrl(Request $request){
		return $request->domain().url($this->callbackAction());
	}
	
	/**
	 * @return string
	 */
	protected function callbackBody(){
		$url = config('filesystem.disks.'.$this->disk().'.url');
		return '{"url":"'.$url.'/$(key)","key":"$(key)","hash":"$(etag)","fsize":$(fsize)}';
	}
	
	/**
	 * @param \think\Request $request
	 * @return \think\Response
	 */
	protected function saveByToken(Request $request){
		Hint::shouldUseApi();
		
		$data = $request->post();
		//		$id = DB::table('images')->insertGetId([
		//			'app_id'     => $data['app_id'],
		//			'url'        => $data['url'],
		//			'size'       => $data['fsize'],
		//			'hash'       => $data['hash'],
		//			'created_at' => now(),
		//		]);
		//
		//		$result = [
		//			'id'   => $id,
		//			'url'  => $data['url'],
		//			'hash' => $data['hash'],
		//		];
		
		return Hint::result($data);
	}
	
	/**
	 * @return string
	 */
	protected function callbackAction(){
		if(property_exists($this, 'callbackAction')){
			return $this->callbackAction;
		}
		
		return "callback";
	}
	
	/**
	 * @param string $method
	 * @param array  $args
	 * @return \think\Response
	 */
	public function __call($method, $args){
		if($method === $this->callbackAction()){
			return $this->saveByToken(app()->request);
		}
		
		throw new HttpException(404);
	}
}
