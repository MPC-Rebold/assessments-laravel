<?php
 
namespace App\Livewire;
 use Livewire\Component;
 use Illuminate\Support\Facades\DB;
 use App\Models\Assessment;
 use Illuminate\Support\Facades\Log;

class UploadGrades extends Component
{


    public function submitGrade()
    {
        $questions = DB::table('questions')->get();
        $assessment = Assessment::find(last(request()->segments()));
        foreach ($questions as $question) {
            if ($assessment->id == $question->assessment_id) {
                if ( $this->Question1 == $question->assessment_id) {
                    Log::info('correct');
                };


        };
    };
}
 
    public function render()
    {
        return view('livewire.assessment');
    }
}