<?php

namespace app\components;

use yii\base\Component;
use yii\helpers\VarDumper;

class Helper extends Component
{

    public static function normalizeArray($arr, $arrayOut = false)
    {
        if (is_array($arr)) {
            $arr = implode(",", $arr);
        }
        $arr = str_ireplace("\n", ",", $arr);
        $arr = str_ireplace(",", ",", $arr);
        $arr = str_ireplace("،", ",", $arr);
        $arr = explode(",", $arr);
        $arr = array_map("trim", $arr);
        $arr = array_unique($arr);
        $arr = array_filter($arr);
        sort($arr);
        if ($arrayOut) {
            return $arr;
        }
        return implode(",", $arr);
    }

    public static function templatedArray($template = [], $values = [], $const = [])
    {
        return $const + array_intersect_key($values, $template) + $template;
    }

    public function normalizeEmail($email)
    {
        $email = explode('@', $email);
        $email[0] = str_replace('.', '', $email[0]);
        return implode('@', $email);
    }

    public static function formatDecimal($input, $decimal = 4)
    {
        if (!empty($input) || $input == 0) {
            return number_format((float) $input, $decimal, '.', '');
        }
        return null;
    }

    public static function formatFloat($input)
    {
        return (float) $input;
    }

    public static function formatBoolean($value)
    {
        return boolval(intval($value));
    }

    public static function formatString($value)
    {
        return strval($value);
    }

    public static function rulesDumper($scenariosRules, $attributesRules)
    {
        $rules = [];
        foreach ($scenariosRules as $scenario => $scenarioAttributesRules) {
            foreach ($scenarioAttributesRules as $attributeLabel => $scenarioRules) {
                $attribute = ($attributeLabel[0] == '!' ? substr($attributeLabel, 1) : $attributeLabel);
                foreach ($scenarioRules as $scenarioRule) {
                    $rules[] = array_merge([[$attributeLabel]], $scenarioRule, ['on' => $scenario]);
                }
                if (isset($attributesRules[$attribute])) {
                    foreach ($attributesRules[$attribute] as $attributeRule) {
                        $rules[] = array_merge([[$attributeLabel]], $attributeRule, ['on' => $scenario]);
                    }
                }
            }
        }
        return VarDumper::export($rules);
    }

}
