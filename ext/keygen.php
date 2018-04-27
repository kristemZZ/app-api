<?php

/**
 * Crypt Key Generator Extension
 *
 * Copyright 2017 Jerry Shaw <jerry-shaw@live.com>
 * Copyright 2018 秋水之冰 <27206617@qq.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ext;

use ext\lib\key;

class keygen implements key
{
    /**
     * Create Crypt Key
     *
     * @return string (32 bits)
     */
    public static function create(): string
    {
        return hash('md5', uniqid(mt_rand(), true));
        // create a token use hash 
        // return hash('sha256', uniqid(mt_rand(), true));
    }

    /**
     * Extract AES Keys from Crypt Key
     *
     * @param string $key (32 bits)
     *
     * @return array
     */
    public static function extract(string $key): array
    {
        $keys = [];
        $keys['key'] = &$key;
        $keys['iv'] = 0 === ord(substr($key, 0, 1)) & 1 ? substr($key, 0, 16) : substr($key, -16, 16);

        unset($key);
        return $keys;
    }

    /**
     * Obscure Crypt Key
     *
     * @param string $key (32 bits)
     * 
     *
     * @return string (40 bits)
     */
    public static function obscure(string $key): string
    {
        $unit = str_split($key, 1);
        foreach ($unit as $k => $v) {
            $bit = substr($v, 0, 1);
            if ($k%2 === 0 && ord($bit)%2 !==0) $v = chr(ord($bit)<<1); 
            $unit[$k] = $v . $bit;
        }
        $key = implode($unit);
        unset($unit, $k, $v, $bit);
        return $key;
    }

    /**
     * Rebuild Crypt Key
     *
     * @param string $key (40 bits)
     *
     * @return string (32 bits)
     */
    public static function rebuild(string $key): string
    {
        $unit = str_split($key, 2);
        foreach ($unit as $k => $v) {
            $bit = substr($v, -1, 1);
            $unit_item = substr($v, 0, -1);
            $unit[$k] = ($k%2 === 0 && ord($bit)%2 !==0 ) ? chr(ord($unit_item)>>1) : $bit; 
        }

        $key = implode($unit);

        unset($unit, $k, $v, $bit);
        return $key;
    }
}