<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-12-02  */

namespace think\annotation\document;


use Doctrine\Common\Annotations\Annotation;
use think\facade\Route;
use think\facade\View;

final class Doc
{
    /**
     * @var \think\route\RuleItem $rule
     */
    protected $rule;

    /**
     * @var \think\Route $router
     */
    protected $router;

    protected $path = '/public/annotation.json';

    public function __construct(Annotation $annotation, \think\route\RuleItem &$rule)
    {
        $this->rule = $rule;
        $this->initializeAnnotationJson($annotation,$rule);
        if (config('annotation.management') === true){
            $router = function (){
              return $this->router;
            };
            $this->router = $router->call($rule);
            $this->setPaaLoginRoute();
            $this->setPaaLoginInRoute();
            $this->setPaaIndexRoute();
            $this->setPaaWelcomeRoute();
            $this->setPaaInfoRoute();
            $this->setPaaEditRoute();
            $this->setPaaEditSaveRoute();
            $this->setPaaLoginOutRoute();
        }
    }

    /** 登陆操作 */
    private function setPaaLoginInRoute(): void
    {
        if (!empty($this->router->getRule('paa/login/in'))) return;
            $this->router->post('paa/login/in',function (){
            if (request()->isPost()){
                if (input('username') == 'admin' and input('password') == 'supper'){
                    session('apiAuthorize',json_encode(input()));
                    session('isEdit',true);
                    return json([
                        'msg'=>'登录成功',
                        'code'=>200,
                        'data'=>[
                            'url' => '/paa/index'
                        ]
                    ],200);
                }
                if (input('username') == 'web' and input('password') == '123456'){
                    session('apiAuthorize',json_encode(input()));
                    session('isEdit',false);
                    return json([
                        'msg'=>'登录成功',
                        'code'=>200,
                        'data'=>[
                            'url' => '/paa/index'
                        ]
                    ],200);
                }
            }
            throw new \Exception('登录失败');
        });
    }

    /** 登陆页面 */
    private function setPaaLoginRoute(): void{
        if (!empty($this->router->getRule('paa/login'))) return;
        $this->router->get('/paa/login',function (){
            return View::display(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR. 'stubs' . DIRECTORY_SEPARATOR . 'login.html'));
        });
    }

    /** 欢迎页面👏 */
    private function setPaaWelcomeRoute(): void
    {
        $this->checkUserLogin();
        if (!empty($this->router->getRule('paa/welcome'))) return;
        $this->router->get('/paa/welcome',function (){
            return View::display(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR. 'stubs' . DIRECTORY_SEPARATOR . 'welcome.html'));
        });
    }

    /** 设置接口管理平台首页 */
    private function setPaaIndexRoute(): void
    {
        $this->checkUserLogin();
        if (!empty($this->router->getRule('paa/index'))) return;
        $this->router->get('/paa/index',function (){
            $annotations = $this->getAnnotationJson();
            View::assign('menus',$annotations);
            View::assign('isEdit',session('isEdit'));
            return View::display(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR. 'stubs' . DIRECTORY_SEPARATOR . 'index.html'));
        });
    }

    /** 设置接口管理平台接口详情🔎 */
    private function setPaaInfoRoute(): void
    {
        $this->checkUserLogin();
        if (!empty($this->router->getRule('paa/info'))) return;
        $this->router->get('paa/info',function (){
            $params = request()->get();
            $apiList = $this->getAnnotationJson();
            $apiInfo = $apiList[$params['group']][$params['groupKey']][$params['action']] ?? $apiInfo = $apiList[$params['group']][$params['action']];
            $apiInfo['validate'] = $apiInfo['validate'][0];
            foreach ($apiInfo['validate'] as $key => $item){
                $validateName = explode('|',$key);
                $apiInfo['validate'][$key] = [
                    'name' => $validateName[0],
                    'doc' => $validateName[1] ?? '',
                    'rule' => $item
                ];
            }
            $apiInfo['action'] = $params['action'];
            $apiInfo['group'] = $params['group'];
            $apiInfo['groupKey'] = $params['groupKey'];
            $apiInfo['success'] = json_encode($apiInfo['success']);
            $apiInfo['error'] = json_encode($apiInfo['error']);
            return View::display(
                file_get_contents(__DIR__ . DIRECTORY_SEPARATOR. 'stubs' . DIRECTORY_SEPARATOR . 'info.html'),
                ['info'=>$apiInfo,'title'=>'API接口详情','isEdit'=>session('isEdit')]
            );
        });
    }

    /** 编辑接口文档 */
    private function setPaaEditRoute(): void
    {
        $this->checkUserLogin();
        if (!empty($this->router->getRule('paa/edit'))) return;
        $this->router->get('/paa/edit',function (){
            if (session('isEdit') !== true){
                throw new \Exception('你没有编辑权限');
            }
            $params = request()->get();
            $apiList = $this->getAnnotationJson();
            $apiInfo = $apiList[$params['group']][$params['groupKey']][$params['action']] ?? $apiInfo = $apiList[$params['group']][$params['action']];
            $apiInfo['validate'] = $apiInfo['validate'][0];
            foreach ($apiInfo['validate'] as $key => $item){
                $validateName = explode('|',$key);
                $apiInfo['validate'][$key] = [
                    'name' => $validateName[0],
                    'doc' => $validateName[1] ?? '',
                    'rule' => $item
                ];
            }
            $apiInfo['action'] = $params['action'];
            $apiInfo['group'] = $params['group'];
            $apiInfo['groupKey'] = $params['groupKey'];
            $apiInfo['success'] = json_encode($apiInfo['success']);
            $apiInfo['error'] = json_encode($apiInfo['error']);
            return View::display(
                file_get_contents(__DIR__ . DIRECTORY_SEPARATOR. 'stubs' . DIRECTORY_SEPARATOR . 'edit.html'),
                ['info'=>$apiInfo,'title'=>'编辑API接口','isEdit'=>session('isEdit')]
            );
        });
    }

    /** 保存接口文档 */
    private function setPaaEditSaveRoute(): void
    {
        $this->checkUserLogin();
        if (!empty($this->router->getRule('paa/edit/save'))) return;
        $this->router->post('paa/edit/save',function (){
            if (request()->isPost()){
                $params = input();
                $return_params = [];
                if (isset($params['return_params']['name']) and !empty($params['return_params']['name'])){
                    foreach ($params['return_params']['name'] as $key => $value){
                        $return_params[$value] = $params['return_params']['value'][$key];
                    }
                }
                $success = json_decode($params['success'],true);
                $error = json_decode($params['error'],true);
                if (is_null($error) or is_null($success)){
                    throw new \Exception('返回值格式为：Json');
                }
                $apiList = $this->getAnnotationJson();
                if (isset($apiList[$params['group']][$params['action']])){
                    $apiList[$params['group']][$params['action']]['success'] = $success;
                    $apiList[$params['group']][$params['action']]['error'] = $error;
                    $apiList[$params['group']][$params['action']]['return_params'] = $return_params;
                }elseif (isset($apiList[$params['group']][$params['groupKey']][$params['action']])){
                    $apiList[$params['group']][$params['groupKey']][$params['action']]['success'] = $success;
                    $apiList[$params['group']][$params['groupKey']][$params['action']]['error'] = $error;
                    $apiList[$params['group']][$params['groupKey']][$params['action']]['return_params'] = $return_params;
                }

                $this->setAnnotationJson($apiList);
                return json([
                    'msg'=>'操作成功',
                    'code'=>200,
                    'data'=>[]
                ],200);
            }
            throw new \Exception('保存失败，返回值格式为：Json');
        });
    }

    /** 设置接口管理平台退出接口 */
    private function setPaaLoginOutRoute(): void{
        $this->router->get('/paa/login/out',function (){
            session('apiAuthorize',null);
            return redirect('/paa/login');
        });
    }

    /** 检查用户登陆 */
    private function checkUserLogin(): void{
        if (session('apiAuthorize')){
            redirect('/paa/login');
        }
    }

    /** 获取注解json文件 */
    public function getAnnotationJson(){
        return json_decode(file_get_contents(root_path().$this->path),true);
    }

    /** 设置注释json文件 */
    public function setAnnotationJson(array $annotations){
        $res = file_put_contents(
            root_path().$this->path,
            json_encode($annotations,JSON_UNESCAPED_UNICODE),
            FILE_USE_INCLUDE_PATH
        );
        return $res;
    }

    /** 初始化注解Json数据 */
    private function initializeAnnotationJson(Annotation $annotation, \think\route\RuleItem &$rule):void {
        $data = json_decode(file_get_contents(root_path().$this->path),true);
        if (!empty($annotation->group)){
            $annotationGroup = explode('.',$annotation->group);
            if (isset($annotationGroup[1])){
                $data[$annotationGroup[0]][$annotationGroup[1]][$annotation->value] = $this->getRuleData(
                    $data[$annotationGroup[0]][$annotationGroup[1]][$annotation->value] ?? [],
                    $rule
                );
                $data[$annotationGroup[0]][$annotationGroup[1]][$annotation->value]['hide'] = $annotation->hide == 'false' ? false : true ;
            }else{
                $data[$annotationGroup[0]][$annotation->value] =  $this->getRuleData(
                    $data[$annotationGroup[0]][$annotation->value] ?? [],
                    $rule
                );
                $data[$annotationGroup[0]][$annotation->value]['hide'] = $annotation->hide == 'false' ? false : true ;
            }
        }else{
            $data[$annotation->group][$annotation->value] =  $this->getRuleData(
                $data[$annotation->group][$annotation->value] ?? [],
                $rule
            );
            $data[$annotation->group][$annotation->value]['hide'] = $annotation->hide == 'false' ? false : true ;
        }
    }

    /** 获取注解路由数据 */
    private function getRuleData(array $api, \think\route\RuleItem $rule){
        return [
            'rule' => $rule->getRule(),
            'route' => $rule->getRoute(),
            'method' => $rule->getMethod(),
            'validate' => $rule->getOption('validate'),
            'success' => $api['success'] ?? [],
            'error' => $api['error'] ?? [],
            'return_params' => $api['return_params'] ?? [],
        ];
    }
}