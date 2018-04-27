<?php

/**
 * PDO Database Parser Script
 *
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

namespace com;

use ext\pdo;
use ext\file;
use core\ctr\router;

class pdo_parser extends pdo
{
    //Scan dir
    public static $dir = null;

    /**
     * Prepare
     */
    private static function prep(): void
    {
        //Check dir value
        if (is_string(self::$dir) && '' !== self::$dir) self::$dir = [self::$dir];
        if (is_array(self::$dir) && !empty(self::$dir)) return;

        //Get dir from CLI argv
        $dir = router::get_cli_argv();
        if (!empty($dir)) {
            self::$dir = &$dir;
            return;
        }

        //Get dir from Router variables
        if (isset(router::$data['dir'])) {
            self::$dir = is_array(router::$data['dir']) ? router::$data['dir'] : [router::$data['dir']];
            return;
        }

        //Default dir
        self::$dir = ['db'];
    }

    /**
     * Import SQL
     *
     * @return array
     * @throws \Exception
     */
    public static function import(): array
    {
        //Prepare
        self::prep();

        //Check dir value
        if (!is_array(self::$dir) || empty(self::$dir)) return [];

        //Loop dir
        $result = [];
        foreach (self::$dir as $dir) {
            //Get absolute path
            $path = ROOT . '/' . $dir;
            $path = realpath($path);

            //Check path
            if (false === $path) {
                $result[] = 'Notice: ' . $dir . ' not exist!';
                continue;
            }

            //List SQL files
            $files = file::get_list($path, '*.sql');

            //Loop SQL files
            foreach ($files as $file) {
                //Get SQL file content
                $sql = file_get_contents($file);

                //Exec SQL & gather results
                $result[] = -1 !== parent::connect()->exec($sql) ? $dir . '/' . basename($file) . ' import succeed!' : $dir . '/' . basename($file) . ' import failed!';
            }
        }

        return $result;
    }
}