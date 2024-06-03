<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionUser extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'course_id',
        'answer',
        'is_correct',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Returns the feedback for the user's answer to the question
     * using the longest common subsequence algorithm
     *
     * @return string
     */
    public function calculateFeedback(): string
    {
        $user_answer = $this->answer;
        $correct_answer = $this->question->answer;

        return $this->calculateFeedbackHelper($user_answer, $correct_answer);
    }

    /**
     * Helper function for calculateFeedback using explicit parameters
     *
     * @param string $userAnswer
     * @param string $correctAnswer
     * @return string
     */
    public static function calculateFeedbackHelper(string $userAnswer, string $correctAnswer): string
    {
        if (! $userAnswer) {
            return str_repeat('_', strlen($correctAnswer));
        }

        $string_1 = strrev($correctAnswer);
        $string_2 = strrev($userAnswer);
        $string_1_length = strlen($string_1);
        $string_2_length = strlen($string_2);

        $num = [];

        // Initialize the num scores table to assume there are no similarities
        for ($i = 0; $i < $string_1_length + 1; $i++) {
            $num[$i] = [];
            for ($j = 0; $j < $string_2_length + 1; $j++) {
                $num[$i][$j] = 0;
            }
        }

        // fill the table with the best path scores
        for ($i = 1; $i <= $string_1_length; $i++) {
            for ($j = 1; $j <= $string_2_length; $j++) {
                // Check every combination of characters
                if ($string_1[$i - 1] === $string_2[$j - 1] || $string_1[$i - 1] === '_' && $string_2[$j - 1] == ' ') {
                    $num[$i][$j] = 1 + $num[$i - 1][$j - 1];
                } else {
                    $num[$i][$j] = max($num[$i][$j - 1], $num[$i - 1][$j]);
                }
            }
        }

        $s1position = $string_1_length;
        $s2position = $string_2_length;
        $result = '';
        while ($s1position != 0 && $s2position != 0) {
            if ($string_1[$s1position - 1] === $string_2[$s2position - 1]
                || $string_1[$s1position - 1] === '_' && $string_2[$s2position - 1] === ' ') {  // characters match
                $result = self::delimitKeep($string_2[$s2position - 1]) . $result;
                $s1position--;
                $s2position--;
            } elseif ($num[$s1position][$s2position - 1] >= $num[$s1position][$s2position]) { // deletion required
                $result = self::delimitDelete($string_2[$s2position - 1]) . $result;
                $s2position--;
            } else { // insertion required
                if ($string_1[$s1position - 1] !== ' ') {
                    $result = self::delimitMissing() . $result;
                }      // only indicate insertions for non-spaces
                $s1position--;
            }
        }
        // take care of any leading mismatch errors
        if ($s2position == 0) {
            for ($k = 0; $k < $s1position; $k++) {
                $result = self::delimitMissing() . $result;
            }
        }
        if ($s1position == 0) {
            for ($k = $s2position - 1; $k >= 0; $k--) {
                $result = self::delimitDelete($string_2[$k]) . $result;
            }
        }

        return str_replace(' ', '&nbsp;', strrev($result));
    }

    public static function delimitKeep(string $string): string
    {
        return strrev('</keep__>') . "$string" . strrev('<keep__>');
    }

    public static function delimitDelete(string $string): string
    {
        return strrev('</delete__>') . "$string" . strrev('<delete__>');
    }

    public static function delimitMissing(): string
    {
        return strrev('</missing__>') . '_' . strrev('<missing__>');
    }
}
