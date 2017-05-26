<?php
namespace App\Helpers;
class Sparkflex
{
  public static function sparkflex($vals)
	{

		//$vals=[4,5,32,2,0,6,7];
		$max=max($vals);
		if ($max==0)
			return;
		$h=25;
		$w=35;
		$c=count($vals);
		$d=$w/$c;
		$ret= "<svg width='$w' height='$h'>";
		$ret.= "<polyline points=\"0,$h ";
		foreach ($vals AS $key=>$val)
		{
			$x=($key-1)/$c*$w;
			$y=($max-$val)*$h/$max;
			$val=$val*10;
			$x2=$x+$d;
			$ret.= "$x,$y $x2,$y ";
		};
		$ret.= $w.','.$h.'" style="fill:red;stroke:red;stroke-width:1" /></svg>';
		echo $ret;
  }
}
