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

    public function isCurrentMethod(\think\route\RuleItem $rule){
        if (PHP_SAPI != 'cli'){
            if($rule->getRule() == trim(explode('?',$_SERVER['REQUEST_URI'])[0],'/')){
                return true;
            }
        }
        return false;
    }
}