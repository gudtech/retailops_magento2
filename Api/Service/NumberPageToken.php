<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 16.9.16
 * Time: 16.42
 */

namespace RetailOps\Api\Service;


class NumberPageToken
{
    const SALT= 'ENJW8mS2KaJoNB5E5CoSAAu0xARgsR1bdzFWpEn+poYw45q+73az5kYi';

    public function encode($param)
    {
        return $this->generateCode($param);
    }

    public function decode($code)
    {
        return $this->getParamByCode($code);
    }

    protected function generateCode($param)
    {
        $array['page'] = $param;
        $array['key'] = self::SALT;
        $string = serialize($array);
        return base64_encode($string);
    }

    protected function getParamByCode($code)
    {
        $string = base64_decode($code);
        $array = unserialize($string);
        if (!is_array($array)) {
            throw new \Exception(__('wrong pageNumberToken'));
        }
        if (isset($array['key']) && $array['key'] === self::SALT) {
            if (isset($array['page']) && is_numeric($array['page'])) {
                return (int)$array['page'];
            }
            throw new \Exception(__('wrong pageNumberToken'));
        }
        throw new \Exception(__('wrong pageNumberToken'));
    }


}