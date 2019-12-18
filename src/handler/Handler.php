<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-11-14  */

namespace think\annotation\handler;


use Doctrine\Common\Annotations\Annotation;

abstract class Handler implements HandleInterface
{
    public function cls(\ReflectionClass $refClass, Annotation $annotation, \think\Route &$route)
    {
        // TODO: Implement cls() method.
    }

    public function func(\ReflectionMethod $refMethod, Annotation $annotation, \think\route\RuleItem &$rule)
    {
        // TODO: Implement func() method.
    }

    public function isCurrentMethod(\ReflectionMethod $refMethod,\think\route\RuleItem $rule){
        if (strtolower(PHP_SAPI) != 'cli'){
            if (strtolower(request()->method()) !== strtolower($rule->getMethod())) return false;
            $routeRule = $rule->parseUrlPath($rule->getRule());
            $requestRule = $rule->parseUrlPath(request()->url());
            if (count($requestRule) !== count($routeRule))return false;

            $lasteRouteRule = array_pop($routeRule);
            $lasteRequestRule = array_pop($requestRule);


            $diff = array_diff($requestRule,$routeRule);
            if ($diff) return false;

            if (strstr($lasteRouteRule,'<') and strstr($lasteRouteRule,'>')){
                return true;
            }
        }
        return false;
    }
}