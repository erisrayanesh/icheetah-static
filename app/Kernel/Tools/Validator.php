<?php

namespace App\Kernel\Tools;

class Validator
{
    public static function filter($value, $filterID = null, $options = null)
    {
        if (!is_null($filterID)){
            if (is_array($value)){
                $value = filter_var_array($value, $filterID, false);                
            } else {
                if (is_array($filterID)){
                    foreach ($filterID as $key => $filter) {
                        $filter_options = isset($options[$key])? $options[$key] : null;
                        $value = self::filter($value, $filter, $filter_options);
                    }
                } else {
                    $value = filter_var($value, $filterID, $options);                    
                }
            }            
        }
        return $value;
    }
}

?>