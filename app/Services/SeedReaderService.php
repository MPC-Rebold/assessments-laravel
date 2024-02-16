<?php

namespace App\Services;

class SeedReaderService
{
    /**
     * Checks if course is a valid directory in \database\seed
     *
     * @param string $courseTitle
     * @return bool
     */
    public static function isValidCourse(string $courseTitle): bool
    {
        return is_dir(database_path('seed/' . $courseTitle));
    }

    /**
     * Checks if assignment is a valid txt file in \database\seed\{courseTitle}
     *
     * @param string $courseTitle
     * @param string $assessmentTitle
     * @return bool
     */
    public static function isValidAssessment(string $courseTitle, string $assessmentTitle): bool
    {
        return file_exists(database_path('seed/' . $courseTitle . '/' . $assessmentTitle . '.txt'));
    }

    /**
     * Returns the questions and answers from the file \database\seed\{courseTitle}\{assessmentTitle}.txt
     *
     * @param string $courseTitle
     * @param string $assessmentTitle
     * @return array
     */
    public static function getQuestions(string $courseTitle, string $assessmentTitle): array
    {
        $questions_txt = file_get_contents(database_path('seed/' . $courseTitle . '/' . $assessmentTitle . '.txt'));
        $exploded = explode('@@', $questions_txt);

        $res = [];
        for ($i = 0; $i < count($exploded) - 1; $i += 2) {
            $res[] = [
                'question' => trim($exploded[$i]),
                'answer' => $exploded[$i + 1],
                'number' => $i / 2 + 1,
            ];
        }

        return $res;
    }
}
