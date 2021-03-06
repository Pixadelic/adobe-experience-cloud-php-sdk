<?php
/**
 * Created by PhpStorm.
 * User: pixadelic
 * Date: 09/05/2018
 * Time: 17:31
 */
// @codingStandardsIgnoreStart

/**
 * Class Utils
 */
class Utils
{
    /**
     * @param array  $arr
     * @param string $key
     * @param string $prefix
     *
     * @return mixed
     */
    public static function getKey(array $arr, $key, $prefix = '_alt')
    {
        if (isset($arr[$key])) {
            return self::getKey($arr, $key.$prefix, $prefix);
        }

        return $key;
    }

    /**
     * @param object $object
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public static function execute($object, $method, array $args = [])
    {
        global $data;
        $key = self::getKey($data, get_class($object).'->'.$method);
        try {
            $data[$key]['success'] = call_user_func_array([$object, $method], $args);
        } catch (Exception $e) {
            $data[$key]['error'] = $e;
        }

        return $key;
    }
}

function getEvtStatus()
{
    global $evtStatus, $campaignClient, $testEventName, $evtPKey, $prefix, $data;
    sleep(2);
    $key = Utils::execute($campaignClient, 'getEvent', [$testEventName, $evtPKey]);
    if (isset($data[$key]['success']) && isset($data[$key]['success']['status'])) {
        $evtStatus = $data[$key]['success']['status'];
    }
}
// @codingStandardsIgnoreEnd
