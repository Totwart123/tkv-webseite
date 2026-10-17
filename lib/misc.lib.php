<?

function fuh ($count, $chunk, $show, $url) {
	if ($show)	
		echo '[ <a href="' . $url . '=0"><<</a> ';
	else
		echo '[ << ';

	if ($show>0)
		printf ('| <a href="%s=%d"><</a> ',
			$url,
			(($show-$chunk)>=0)?$show-$chunk:0);
	else
		echo '| < ';
		
	if ($show < $count - $chunk) 
		printf ('| <a href="%s=%d">></a> ',
			$url,
			$show+$chunk);
	else
		echo '| > ';

	if ($show == $count - $chunk)
		echo '| >> ]';
	else	
		printf ('| <a href="%s=%d">>></a> ]',
			$url,
			$count - $chunk);	

}


?>