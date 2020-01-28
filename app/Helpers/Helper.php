<?php

namespace App\Helpers;

use App\Exceptions\SException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;


class Helper
{

    const DEFAULT_ORDER_PREFIX = 'SO';

    /**
     * @param $json
     *
     * @return bool
     */
    public static function isJson($json)
    {
        $result = json_decode($json);

        if (json_last_error() === JSON_ERROR_NONE) {
            return true;
        }

        return false;
    }

    /**
     * This function merges $class2 to $class1. Values from Class2 is assigned to Class1
     * $matchingKeys is array of keys that should match both classes.
     * ex. name : $class1->name = $class2->name
     *
     * $conditions is array of keys matching conditions.
     * ex store_price => defaultStore.store_price :
     * $class1->store_price = $class2->defaultStore.store_price
     *
     * @param $class1
     * @param $class2
     * @param array $matchingKeys
     * @param array $conditions
     *
     * @return bool | $class1
     */
    public static function classMerger($class1, $class2, $matchingKeys = [], $conditions = [])
    {
        if (!is_object($class1) || !is_object($class2)) {
            return false;
        }

        foreach ($matchingKeys as $key => $matchingKey) {
            if (isset($class2->$matchingKey)) {
                $class1->$matchingKey = $class2->$matchingKey;
            }
        }

        foreach ($conditions as $key => $value) {
            $keyValue = self::getValueFromKeyCondition($class2, $value);

            $class1->$key = $keyValue;
        }

        return $class1;
    }

    /**
     * Gets value from a class variable. Example of $key values
     * 'name' which returns $class->name
     * 'user.id' which returns $class->user.id
     * ['key'=>'status', 'conditions' => ['active'=1,'inactive'=>0] which returns
     * $class->status which has value of active or inactive returned as either 1 or 0 respectively
     *
     * @param $class
     * @param array|string $key
     *
     * @return null | mixed
     */
    public static function getValueFromKeyCondition($class, $key)
    {
        $value = null;

        if (is_string($key)) {

            if (isset($class->$key)) {
                $value = $class->$key;
            } else {
                $params = explode('.', $key);

                $keyToCheck = $class->{$params[0]};

                if (isset($keyToCheck)) {
                    for ($i = 1; $i < count($params); $i++) {
                        $keyToCheck = array_key_exists($params[$i], $keyToCheck) ? $keyToCheck[$params[$i]] : false;
                    }

                    $value = $keyToCheck && isset($class->$keyToCheck) ? $class->$keyToCheck : null;
                }
            }
        }

        if (is_array($key)) {
            if (
                isset($class->{$key['key']})
                && array_key_exists(strtolower($class->{$key['key']}), $key['conditions'])
            ) {
                $value = $key['conditions'][strtolower($class->{$key['key']})];
            }
        }

        return $value;
    }

    /**
     * This removes unwanted values from array.
     *
     * @param array $array
     * @param array $values
     *
     * @return array
     */
    public static function removeValueFromArray(array $array, array $values)
    {
        foreach ($values as $value) {
            if (($key = array_search($value, $array)) !== false) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     *
     * @return string
     */
    public static function convertUnderscoreToCamelcase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    /**
     * Sanitize number ensure it starts with 0
     *
     * @param $phoneNumber
     *
     * @return string
     * @throws SException
     */
    public static function sanitizePhoneNumber($phoneNumber)
    {
        if (!is_numeric($phoneNumber)) {
            throw new SException("Invalid phone number entered.", ResponseCodes::INVALID_PARAM);
        }
        $firstThreeCharacters = substr($phoneNumber, 0, 3);
        $numberLength = strlen($phoneNumber);

        if ($firstThreeCharacters == '234') {
            $phoneNumber = '0' . substr($phoneNumber, 3);
        }

        if ($firstThreeCharacters != '0' && $numberLength <= 10) {
            $phoneNumber = '0' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * @return string
     */
    public static function generateOrderNumber(string $prefix = 'SO'): string
    {
        return strtoupper(substr(uniqid($prefix ?? self::DEFAULT_ORDER_PREFIX, true), 0, 12));
    }

    /**
     * Converts a traversable to array. It performs deep conversion making sure
     * that the result returned only contains arrays.
     * Can help convert results returned by MongoDb aggregation to an array.
     *
     * @param \Traversable|array $results the results returned by the aggregation
     *
     * @return array the results converted to array
     */
    public static function convertToArray($results)
    {
        $data = [];
        //anonymous class to aid in deep conversion
        $converter = new class ()
        {
            public function toArray($value)
            {
                if ($value instanceof \ArrayObject) {
                    return ArrayHelper::arrayMapRecursive($value->getArrayCopy(), [$this, "toArray"]);
                } elseif ($value instanceof \Traversable || is_array($value) || $value instanceof Arrayable) {
                    return Helper::convertToArray($value);
                } elseif (is_bool($value)) {
                    return (bool) $value;
                } else {
                    return (string) $value;
                }
            }
        };
        foreach ($results as $key => $row) {
            $data[$key] = $converter->toArray($row);
        }

        return $data;
    }

    /**
     * Creates a Laravel custom paginator.
     *
     * @param array $items
     * @param int $page
     * @param int $limit
     * @param int $total
     *
     * @return LengthAwarePaginator|Paginator
     */
    public static function createCustomPaginator(array $items, int $page, int $limit, int $total = 0)
    {
        return new LengthAwarePaginator($items, $total, $limit, $page);
    }

    /**
     * @param int $length
     * @param bool $characterOnly
     * @param bool $capsOnly
     *
     * @return string
     */
    public static function generateRandomString($length = 5, $characterOnly = true, $capsOnly = true)
    {
        $upperChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $lowerChars = 'abcdefghijklmnopqrstuvwxyz';

        $characters = $characterOnly ? $upperChars : $upperChars . $numbers;
        $characters = $capsOnly ? $characters : $characters . $lowerChars;

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @param $validateWith
     * @param $value
     *
     * @return bool
     */
    public static function validateInput($validateWith, $value)
    {
        if ($validateWith == 'int' && is_int($value)) {
            return true;
        }

        if ($validateWith == 'string' && is_string($value)) {
            return true;
        }

        if ($validateWith == 'bool' && is_bool($value)) {
            return true;
        }

        if ($validateWith == 'array' && is_string($value)) {
            return true;
        }

        if ($validateWith == 'email') {
            if (is_array($value)) {
                foreach ($value as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }
                }

                return true;
            } elseif (is_string($value)) {
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            } else {
                return false;
            }
        }

        if ($validateWith == 'datetime') {
            $d = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

            return $d && $d->format('Y-m-d H:i:s') === $value;
        }

        if ($validateWith == 'date') {
            $d = \DateTime::createFromFormat('Y-m-d', $value);

            return $d && $d->format('Y-m-d') === $value;
        }

        return false;
    }

    public static function sentence_case($string)
    {
        $sentences = preg_split(
            '/([.?!]+)/',
            $string,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        $new_string = '';
        foreach ($sentences as $key => $sentence) {
            $new_string .= ($key & 1) == 0 ?
                ucfirst(strtolower(trim($sentence))) : $sentence . ' ';
        }
        return trim($new_string);
    }

    public static function createExceptionMessage(\Throwable $ex)
    {
        $message = "{$ex->getMessage()} on line {$ex->getLine()} in {$ex->getFile()}";
        return $message;
    }

    public static function getStrippedDownStackTrace(\Throwable $e)
    {
        $trace = $e->getTrace();
        foreach ($trace as $key => &$elem) {
            foreach ($elem['args'] as $key => &$elemArg) {
                if (
                    is_object($elemArg) && !$elemArg instanceof \App\Models\BaseModel &&
                    !$elemArg instanceof \App\Models\Mongoquent
                ) {
                    $elem['args'][$key] = get_class($elemArg);
                }
            }
            $trace[$key] = $elem;
        }
        return $trace;
    }

    public static function random_username($string)
    {
        $pattern = " ";
        $firstPart = strstr(strtolower($string), $pattern, true);
        $secondPart = substr(strstr(strtolower($string), $pattern, false), 0, 4);
        $nrRand = rand(0, 100);

        $username = trim($firstPart) . trim($secondPart) . trim($nrRand);
        return $username;
    }
}
