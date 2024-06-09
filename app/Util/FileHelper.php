<?php

namespace App\Util;

use App\Models\Assessment;
use App\Models\Master;

class FileHelper
{
    /**
     * Recursively delete a directory or file
     *
     * @param string $path
     */
    public static function rmrf(string $path): void
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($path. DIRECTORY_SEPARATOR .$object) && ! is_link($path.'/'.$object)) {
                        self::rmrf($path . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($path. DIRECTORY_SEPARATOR .$object);
                    }
                }
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    /**
     * Recursively copy a directory
     *
     * @param string $source the source directory
     * @param string $destination the destination directory
     * @return void
     */
    public static function recurseCopy(string $source, string $destination): void
    {
        $dir = opendir($source);

        @mkdir($destination);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    self::recurseCopy($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    /**
     * Returns the path of the assessment in the seed directory
     *
     * @param Assessment $assessment the assessment to get the path of
     * @return string the path of the assessment
     */
    public static function getAssessmentPath(Assessment $assessment): string
    {
        return self::getAssessmentPathByTitles($assessment->master->title, $assessment->title);
    }

    /**
     * Returns the path of the assessment in the seed directory
     *
     * @param string $masterTitle the title of the master
     * @param string $assessmentTitle the title of the assessment
     * @return string the path of the assessment in the seed directory
     */
    public static function getAssessmentPathByTitles(string $masterTitle, string $assessmentTitle): string
    {
        return config('seed.seed_path') . '/' . $masterTitle . '/' . $assessmentTitle . '.txt';
    }

    /**
     * Returns the path of the master in the seed directory
     *
     * @param Master|string $master the master to get the path of as a title string or a Master object
     * @return string the path of the master in the seed directory
     */
    public static function getMasterPath(Master|string $master): string
    {
        if ($master instanceof Master) {
            $masterTitle = $master->title;
        } else {
            $masterTitle = $master;
        }

        return config('seed.seed_path') . '/' . $masterTitle;
    }

    /**
     * Returns the path of the admin file
     *
     * @return string the path of the admin file
     */
    public static function getAdminFilePath(): string
    {
        return config('seed.seed_path') . '/admins.txt';
    }
}
