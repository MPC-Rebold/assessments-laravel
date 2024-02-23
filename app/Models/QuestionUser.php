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
     * @param string $user_answer
     * @param string $correct_answer
     * @return string
     */
    public static function calculateFeedbackHelper(string $user_answer, string $correct_answer): string
    {
        if (! $user_answer) {
            return str_repeat('_', strlen($correct_answer));
        }

        $string_1 = strrev($correct_answer);
        $string_2 = strrev($user_answer);
        $string_1_length = strlen($string_1);
        $string_2_length = strlen($string_2);

        $num = array_fill(0, $string_1_length + 1, array_fill(0, $string_2_length + 1, 0));

        // Fill the table with the best path scores
        for ($i = 1; $i <= $string_1_length; $i++) {
            for ($j = 1; $j <= $string_2_length; $j++) {
                // Check every combination of characters
                if ($string_1[$i - 1] == $string_2[$j - 1] || ($string_1[$i - 1] == '_' && $string_2[$j - 1] == ' ')) {
                    $num[$i][$j] = 1 + $num[$i - 1][$j - 1];
                } else {
                    $num[$i][$j] = max($num[$i][$j - 1], $num[$i - 1][$j]);
                }
            }
        }

        $s1_position = $string_1_length;
        $s2_position = $string_2_length;
        $result = '';
        while ($s1_position != 0 && $s2_position != 0) {
            if ($string_1[$s1_position - 1] == $string_2[$s2_position - 1] || ($string_1[$s1_position - 1] == '_' && $string_2[$s2_position - 1] == ' ')) {
                $keep_char = $string_2[$s2_position - 1];
                $result = self::delimitKeep($keep_char) . $result;
                $s1_position--;
                $s2_position--;
            } elseif ($num[$s1_position][$s2_position - 1] >= $num[$s1_position][$s2_position]) {
                $delete_char = $string_2[$s2_position - 1];
                $result = self::delimitDelete($delete_char) . $result;
                $s2_position--;
            } else {
                if ($string_1[$s1_position - 1] != ' ') {
                    $result = '_' . $result;
                }
                $s1_position--;
            }
        }

        // Take care of any leading mismatch errors
        if ($s2_position == 0) {
            for ($_ = 0; $_ < $s1_position; $_++) {
                $result = '_' . $result;
            }
        }
        if ($s1_position == 0) {
            for ($_ = 0; $_ < $s2_position; $_++) {
                $deleted_char = $string_2[$s2_position - 1];
                $result = self::delimitDelete($deleted_char) . $result;
                $s2_position--;
            }
        }

        return strrev($result);
    }

    private static function delimitKeep(string $string): string
    {
        return strrev('</keep__>') . "$string" . strrev('<keep__>');
    }

    private static function delimitDelete(string $string): string
    {
        return strrev('</delete__>') . "$string" . strrev('<delete__>');
    }
}
