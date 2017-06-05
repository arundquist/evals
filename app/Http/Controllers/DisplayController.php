<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Comment;
use App\Course;
use App\Gencomment;
use App\Instructor;
use App\Question;
use App\Score;
use App\Semester;
use Sparkflex;
use Stats;

class DisplayController extends Controller
{
    //
    public function pickInstructor()
    {
      $this->authorize('approved');
      $instructors=Instructor::orderBy('name')->get();
      return view('displays.pickinstructor',
        ['instructors'=>$instructors]);
    }



    public function showInstructor($instructor_id)
    {
      $this->authorize('approved');
      $instructor=Instructor::findOrFail($instructor_id);
      $courses=$instructor->courses()->with('scores')->with('gencomments')->get();
      $courseids=$courses->pluck('id')->toArray();
      $allbins=[];
      $avgs=[];
      $gencomments=[];
      $comments=[];
      $evalcounts=[];
      $classinfo=[];
      $deletes=[];
      $questions=Question::orderBy('questionnum')->get();
      foreach ($courses AS $course)
      {
        $all=$course->scores()
                  ->select('question_id','score', \DB::raw("count('score') AS c"))
                  ->groupBy('question_id','score')
                  ->orderBy('score')
                  ->get();
        if (!($all->first()))
          {
            $deletes[]=$course->id;
            continue;
          }
        $avgs[$course->id]=$course->scores()->avg('score');
        $gencomments[$course->id]=$course->gencomments;
        $classinfo[$course->id]=$this->getClassInfo($course->id);
        //$score_ids=$course->scores()->pluck('id')->toArray();
        $comms=\DB::Select("select question_id, score, comment from comments c
                          left join scores s on s.id=c.score_id where s.course_id=$course->id");
        foreach ($comms AS $comm)
        {
          $comments[$course->id][$comm->question_id][$comm->score][]=$comm->comment;
        }

        foreach ($all AS $single)
        {
          $allbins[$course->id][$single->question_id][$single->score]=$single->c;
        }

        $evalcounts[$course->id]=array_sum($allbins[$course->id][$single->question_id]);
      };
      foreach($deletes AS $id)
      {
        $courses=$courses->except($id);
      }
      return view('displays.instructor2',
        ['allbins'=>$allbins,
        'evalcounts'=>$evalcounts,
        'comments'=>$comments,
        'gencomments'=>$gencomments,
        'courses'=>$courses,
        'classinfo'=>$classinfo,
        'instructor'=>$instructor,
        'questions'=>$questions,
        'avgs'=>$avgs]);

    }

    public function getDeptData($dept)
    {
      $courseids=Course::where('dept',$dept)->pluck('id')->toArray();

      $all=\DB::table('scores')
                ->select('course_id','question_id', 'score', \DB::raw("count('score') AS c"))
                ->whereIn('course_id', $courseids)
                ->where('score',"!=","")
                ->groupBy('question_id','score')
                ->orderBy('question_id')
                ->orderBy('score')
                ->get();
      $allbins=[];
      foreach ($all AS $a)
      {
        $allbins[$a->question_id][$a->score]=$a->c;
      }
      $avg=Score::whereIn('course_id',$courseids)->avg('score');
      return(['bins'=>$allbins,'avg'=>$avg]);

    }

    public function getCourseData($courseids)
    {
      $all=\DB::table('scores')
                ->select('course_id','question_id', 'score', \DB::raw("count('score') AS c"))
                ->whereIn('course_id', $courseids)
                ->where('score',"!=","")
                ->groupBy('question_id','score')
                ->orderBy('question_id')
                ->orderBy('score')
                ->get();
      $allbins=[];
      foreach ($all AS $a)
      {
        $allbins[$a->question_id][$a->score]=$a->c;
      }
      $avg=Score::whereIn('course_id',$courseids)->avg('score');
      return(['bins'=>$allbins,'avg'=>$avg]);
    }

    public function getAllDepts()
    {
      $alldepts=Course::select('dept')->orderBy('dept')->distinct()->pluck('dept')->toArray();

      $all=[];
      $questions=Question::orderBy('questionnum')->get();
      foreach($alldepts AS $dept)
      {
        $courseids=Course::where('dept',$dept)->pluck('id')->toArray();
        $all[$dept]=$this->getCourseData($courseids);
      };
      return view('displays.alldepts',
        ['all'=>$all,
        'depts'=>$alldepts,
        'questions'=>$questions]);
    }

    public function getDept($dept)
    {
      $alldepts=[$dept];
      $all=[];
      $questions=Question::orderBy('questionnum')->get();
      foreach($alldepts AS $dept)
      {
        $courseids=Course::where('dept',$dept)->pluck('id')->toArray();
        $all[$dept]=$this->getCourseData($courseids);
      };

      return view('displays.alldepts',
        ['all'=>$all,
        'depts'=>$alldepts,
        'questions'=>$questions]);
    }

    public function getLevel($level)
    {
      $alldepts=[$level];
      $all=[];
      $questions=Question::orderBy('questionnum')->get();
      foreach($alldepts AS $dept)
      {
        $low=round($dept,-3);
        $high=$low+999;
        $courseids=Course::whereBetween('number',[$low,$high])->pluck('id')->toArray();
        $all[$dept]=$this->getCourseData($courseids);
      };

      return view('displays.alldepts',
        ['all'=>$all,
        'depts'=>$alldepts,
        'questions'=>$questions]);
    }

    public function getAllLevels()
    {
      $alldepts=[1000,3000,5000];
      $all=[];
      $questions=Question::orderBy('questionnum')->get();
      foreach($alldepts AS $dept)
      {
        $low=round($dept,-3);
        $high=$low+999;
        $courseids=Course::whereBetween('number',[$low,$high])->pluck('id')->toArray();
        $all[$dept]=$this->getCourseData($courseids);
      };

      return view('displays.alldepts',
        ['all'=>$all,
        'depts'=>$alldepts,
        'questions'=>$questions]);
    }

    private function organizeScores($courseids)
    {
      $scores=[];
      $qids=Question::orderBy('questionnum')->get()->pluck('id','questionnum');
      $questions=Question::orderBy('questionnum')->get();
      foreach($qids AS $num=>$qid)
      {
        $scores[$num]=Score::where('question_id',$qid)
                      ->whereIn('course_id',$courseids)
                      ->get();
      };

      $avg=Score::whereIn('course_id',$courseids)
                    ->avg('score');
      $all=['scores'=>$scores, 'avg'=>$avg,'questions'=>$questions];
      return $all;
    }

    public function getClassInfo($course_id)
    {
      $course=Course::findOrFail($course_id);
      $ay=$course->semester->ay;
      $seasons=["fall"=>11,
          "winter"=>12,
          "spring"=>13];
      $season=$course->semester->season;
      $seasonnum=$seasons[$season];

      $nay=substr($ay,0,2);

      $nay+=2000;
      //dd($course->crn);
      $term_id=\DB::connection('mysql2')
          ->table('terms')
          ->select('id')
          ->where('season',$seasonnum)
          ->where('ay',$nay)
          ->first();

      $result=\DB::connection('mysql2')
        ->table('courses')
        ->select('id','enrollment')
        ->where('crn',$course->crn)
        ->where('term_id',$term_id->id)
        ->first();

      if ($result)
      {
        $classinfo=["id"=>$result->id, "enrollment"=>$result->enrollment];
      } else {
        $classinfo=["id"=>False, "enrollment"=>0];
      }
      return $classinfo;
    }
}
