<?php
if(!isset($argv[1]) || !isset($argv[2])) {
	fputs(STDERR, '引数が足りません。'."\n");
	exit ;
}
// file path
$file_name = $argv[1];

// table name
$table_name = $argv[2];

// ファイルを開く
if(!file_exists($file_name)){
	fputs(STDERR, '指定のファイルが存在しません。'."\n");
	exit ;
}

$fp = fopen($file_name, 'r');

$lineno = 0;
$header = [];
$body = [];
$type = [];

while($line = fgetcsv($fp)){
//	var_dump($line);
//	echo "<br />";
	if($lineno == 0) {
		$header = $line;
//		print_r($line);
	} else if($lineno == 1) {
		// body 1行目で　型チェックを行う
		$body[$lineno] = $line;
		$columnno = 0;
		foreach($line as $value) {
			if(preg_match('/-*[0-9]+$/', $value)) {
				/** 整数(integer) */
				$type[$columnno] = "integer";
//				return ;
			} else if(preg_match('/^[0-9].+$/', $value)) {
				/** 少数(double) */
				$type[$columnno] = "double";
//				return ;
			} else {
				$type[$columnno] = "VARCHAR(255)";
			}
			$columnno++;
		}

		/**
		CREATE TABLE IF NOT EXISTS h28(
		市区町丁 VARCHAR( 100 ),
		総合計 numeric(11),
		凶悪犯計 numeric(11),
		凶悪犯強盗 numeric(11),
		);

		 */


	} else {
//		print_r($line);
		$body[$lineno] = $line;
	}
	$lineno ++;
}

// create table
$create_table_sql = "CREATE TABLE IF NOT EXISTS ".$table_name."("."\n";
for($i = 0;$i < count($header); $i++) {
	if($i) {
		$create_table_sql .= ",";
	}
	$create_table_sql .= preg_replace('/^\xEF\xBB\xBF/','',$header[$i])." ".$type[$i]."\n";
}
$create_table_sql .= ");";
echo $create_table_sql."\n";

// insert

//$insert_sql = 'INSERT into h28('."\n".implode(',',$header)."\n".' ) '."\n".'VALUES('."\n".implode(',',$body[1])."\n".');';
foreach($body as $item)
	{
	$insert_sql = 'INSERT into ".$table_name."('."\n";
	for($i = 0;$i < count($header); $i++) {
		if($i) {
			$insert_sql .= ",";
		}
		$insert_sql .= preg_replace('/^\xEF\xBB\xBF/','',$header[$i]);
	}
	$insert_sql .= "\n)".'VALUES('."\n";
	for ($i = 0; $i < count($item); $i++)
	{
		if ($i)
		{
			$insert_sql .= ",";
		}
		if ($type[$i] == "VARCHAR(255)")
		{
			$insert_sql .= "'" . $item[$i] . "'";
		} else
		{
			$insert_sql .= $item[$i];
		}
	}
	$insert_sql .= "\n" . ');';
	echo $insert_sql."\n";
}
fclose($fp);
?>
