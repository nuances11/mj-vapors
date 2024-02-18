<?php

namespace App\Constants;

use ReflectionClass;

class BaseConstant
{
    /**
     * Get the value of a constant by passing a search string.
     *
     * @param string $search
     * @return mixed
     */
    public static function fromString(string $search) : mixed
    {
        $constants = self::asList();

        foreach ($constants as $constant) {
            if ($constant === $search) {
                return $constant;
            }
        }

        return '';
    }

    /**
     * Get the list of constants from the class which extended BaseConstant.
     *
     * @param bool $preserveKeys - optional //Default - false | If true, it returns the list of constants with their corresponding keys
     * @return array
     */
    public static function asList(bool $preserveKeys = false) : array
    {
        $reflectionClass = new ReflectionClass(get_called_class());
        $constants = $reflectionClass->getConstants();

        if ($preserveKeys) {
            return $constants;
        }

        $constantValues = array();

        foreach ($constants as $constant) {
            $constantValues[] = $constant;
        }

        return $constantValues;
    }
}
