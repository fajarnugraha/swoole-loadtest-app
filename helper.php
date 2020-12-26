<?php
function swsCounterResponse($ok) {
	static $max_column = 100;
	static $i=0; static $error_detail=0; static $pos_detail=0; static $pos_summary=0;
	if (!$i) echo "#".number_format($i)."\n";

	if ($ok) {
		echo "-";
	} else {
		echo "!";
		$error_detail = true;
	}

	$pos_detail++;
	if (++$i % $max_column === 0) {
		echo "\r";
		for ($j=0; $j<$max_column; $j++) {
			echo " ";
		}
		if ($pos_summary == $max_column) {
			//echo "X"; sleep(1); die();
			echo "\r";
			$pos_summary=0;
		} else {

			echo "\033[F";
		}
		if ($pos_summary) echo "\033[".$pos_summary."C";
		if ($error_detail) echo "!";
		else echo ".";
		echo " #".number_format($i)."\n";
		$error_detail=0; $pos_detail=0; $pos_summary++;
	}
}
