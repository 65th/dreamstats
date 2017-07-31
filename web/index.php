<?php

$dbconn = pg_connect(getenv('DATABASE_URL')) or die (".!. " . pg_last_error());

$query = "SELECT * FROM test_table";
$result = pg_query($query);

$rows = [];
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
	$rows[] = $line;
}
echo "<pre>"; print_r($rows); echo "</pre>";

pg_free_result($result);

pg_close($dbconn);

print_r(parse_url(getenv('DATABASE_URL')));