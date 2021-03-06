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

    function random_color_part() {
      return str_pad( dechex( mt_rand( 200, 255 ) ), 2, '0', STR_PAD_LEFT);
    }

    function random_color() {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }


    public function showInstructor($instructor_id)
    {
      $this->authorize('approved');
      $alldata=$this->getInstructorData($instructor_id);

      return view('displays.instructor2',$alldata);

    }

    public function getInstructorData($instructor_id)
    {
      $instructor=Instructor::findOrFail($instructor_id);
      $courses=$instructor->courses()->with('scores')->with('gencomments')->get();
      //$courseids=$courses->pluck('id')->toArray();
      $allbins=[];
      $avgs=[];
      $gencomments=[];
      $comments=[];
      $evalcounts=[];
      $classinfo=[];
      $deletes=[];
      $colors=[];
      foreach ($courses AS $course) {
        $colors[$course->semester->ay]=$this->random_color();
      }
      $questions=Question::orderBy('questionnum')->get()->keyBy('id');

      // grab means
      $means=[];
      foreach ($questions AS $q) {
        //$means[$q->id]=\DB::Select("select avg(score) a from scores where question_id=$q->id group by course_id having count(score)>=10 order by avg(score)");
        $means[$q->id]=Score::select(\DB::raw("avg(score) as a"))
                      ->where("question_id",$q->id)
                      ->groupBy("course_id")
                      ->havingRaw("count(score)>=10")
                      ->orderBy(\DB::raw("avg(score)"))
                      ->pluck("a")
                      ->toArray();
      }
      //dd($means[10]);
      $means["all"]=Score::select(\DB::raw("avg(score) as a"))
                    ->groupBy("course_id")
                    ->havingRaw("count(score)>=100")
                    ->orderBy(\DB::raw("avg(score)"))
                    ->pluck("a")
                    ->toArray();
      //dd($means);
      foreach ($courses AS $course)
      {
        $all=$course->scores()
                  ->select('question_id','score', \DB::raw("count('score') AS c"))
                  ->where('instructor_id',$instructor_id)
                  ->groupBy('question_id','score')
                  ->orderBy('score')
                  ->get();
        if (!($all->first()))
          {
            $deletes[]=$course->id;
            continue;
          }
        $avgs[$course->id]=$course->scores()->where('instructor_id',$instructor_id)->avg('score');
        $gencomments[$course->id]=$course->gencomments()->where('instructor_id',$instructor_id)->get();
        $classinfo[$course->id]=$this->getClassInfo($course->id);
        //$score_ids=$course->scores()->pluck('id')->toArray();
        $comms=\DB::Select("select question_id, score, comment from comments c
                          left join scores s on s.id=c.score_id where s.course_id=$course->id and s.instructor_id=$instructor_id");
        foreach ($comms AS $comm)
        {
          $comments[$course->id][$comm->question_id][$comm->score][]=$comm->comment;
        }

        foreach ($all AS $single)
        {
          $allbins[$course->id][$single->question_id][$single->score]=$single->c;
        }

        $evalcounts[$course->id]=array_sum($allbins[$course->id][$single->question_id]);
        //dd([$allbins[$course->id][$single->question_id],$single->question_id]);
      };
      foreach($deletes AS $id)
      {
        $courses=$courses->except($id);
      }

      return ['allbins'=>$allbins,
        'evalcounts'=>$evalcounts,
        'comments'=>$comments,
        'gencomments'=>$gencomments,
        'courses'=>$courses,
        'classinfo'=>$classinfo,
        'instructor'=>$instructor,
        'questions'=>$questions,
        'avgs'=>$avgs,
        'colors'=>$colors,
        'means'=>$means];
    }

    public function getInstructorSummary($instructor_id)
    {
      $this->authorize('approved');
      $instructor=Instructor::findOrFail($instructor_id);
      $courses=$instructor->courses()->with('scores')->get();
      $avgs=[];
      $classinfo=[];
      $evalcounts=[];
      $means=[];
      $ayavgs=[];
      $colors=[];
      foreach ($courses AS $course) {
        $colors[$course->semester->ay]=$this->random_color();
      }

      $means["all"]=Score::select(\DB::raw("avg(score) as a"))
                    ->groupBy("course_id")
                    ->havingRaw("count(score)>=100")
                    ->orderBy(\DB::raw("avg(score)"))
                    ->pluck("a")
                    ->toArray();
      //dd($courses);
      $allays=[];
      foreach ($courses AS $course)
      {
        $ay=$course->semester->ay;
        $allays[]=$ay;
        $avgs[$course->id]=$course->scores()->where('instructor_id',$instructor_id)->avg('score');
        $classinfo[$course->id]=$this->getClassInfo($course->id);
        $count=$course->scores()->where('instructor_id',$instructor_id)->count('score');
        $count=$count/10;

        $evalcounts[$course->id]=$count;
        if ($classinfo[$course->id]['enrollment']!=0)
        {
          $ayavgs[$ay][]=$avgs[$course->id];
        };
      };
      //dd($ayavgs);
      //dd($allays);
      return view('displays.summary',['avgs'=>$avgs,
              'means'=>$means,
              'classinfo'=>$classinfo,
              'evalcounts'=>$evalcounts,
              'ayavgs'=>$ayavgs,
              'instructor'=>$instructor,
              'colors'=>$colors,
              'courses'=>$courses]);

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

    public function getAllWithSameCourse($dept, $num)
    {
      $alldepts=["$dept $num"];
      $all=[];
      $questions=Question::orderBy('questionnum')->get();
      $courseids=Course::where('dept',$dept)->where('number',$num)->pluck('id')->toArray();
      $all["$dept $num"]=$this->getCourseData($courseids);


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

    public function getAll()
    {
      $alldepts=["all"];
      $all=[];
      $questions=Question::orderBy('questionnum')->get();
      $all=\DB::table('scores')
                ->select('course_id','question_id', 'score', \DB::raw("count('score') AS c"))
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
      //$avg=Score::all()->avg('score');
      $avg=\DB::table('scores')
          ->select(\DB::raw("avg(score) AS a"))
          ->first()->a;
      $all["all"]=['bins'=>$allbins,'avg'=>$avg];

      return view('displays.alldepts',
        ['all'=>$all,
        'depts'=>$alldepts,
        'questions'=>$questions]);
    }

    public function getAllLevels()
    {
      $alldepts=[1000,3000,5000,6000,7000,8000];
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
        ->select('id','enrollment','title','credits')
        ->where('crn',$course->crn)
        ->where('term_id',$term_id->id)
        ->first();

      if ($result)
      {
        $classinfo=["id"=>$result->id, "enrollment"=>$result->enrollment,"title"=>$result->title,"credits"=>$result->credits];
      } else {
        $classinfo=["id"=>False, "enrollment"=>0,"title"=>'',"credits"=>''];
      }
      return $classinfo;
    }

    public function getHashedId($id)
    {
      $id=Instructor::where(\DB::raw("md5(id)"),$id)->first();
      // //return redirect("hashinstructor/$id->id");
      $alldata=$this->getInstructorData($id->id);
      //
      return view('displays.instructor2',$alldata);

    }
}
