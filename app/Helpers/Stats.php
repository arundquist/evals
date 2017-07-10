<?php
namespace App\Helpers;
class Stats
{
  public static function avg($scores)
	{
    return number_format($scores->avg('score'),2);

  }

  public static function avgBin($bins)
  {
    $avg=0;
    foreach ($bins AS $score=>$count)
    {
      $avg+=$score*$count;
    };
    $avg=$avg/array_sum($bins);
    return number_format($avg,2);
  }

  public static function bins($scores)
  {
    $arr=$scores->pluck('score')->toArray();

    $acv=array_count_values($arr);
    for ($i=1; $i <=7 ; $i++) {
      $acv=array_add($acv,$i,0);
    };
    ksort($acv);
    return $acv;
  }

  public static function augmentBins($bins)
  {
    $acv=$bins;
    for ($i=1; $i <=7 ; $i++) {
      $acv=array_add($acv,$i,0);
    };
    ksort($acv);
    return $acv;
  }

  public static function numComments($scores)
  {
    $numcomments=0;
    foreach($scores AS $s)
    {
      if(!(is_null($s->comment)))
      {
        $numcomments++;
      }
    };
    return $numcomments;
  }

  public static function numGenComments($course)
  {
    $numcomments=0;
    foreach($course->gencomments AS $s)
    {
      if(!(is_null($s->comment)))
      {
        $numcomments++;
      }
    };
    return $numcomments;
  }

  public static function percentile($mean, $list)
  {
    $i=0;
    $size=count($list);
    while ($list[$i] < $mean) {
      $i++;
    }
    return number_format($i/$size*100,0);
  }


}
