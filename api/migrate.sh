$NEWDB = "ultimate.new.db";

sqlite3 $NEWDB < create.sql;
sqlite3 $1 "SELECT distinct game_id, date('now') FROM moves" | sqlite3 $NEWDB ".import /dev/stdin game";
sqlite3 $1 "SELECT * FROM moves" | sqlite3 $NEWDB ".import /dev/stdin moves";
sqlite3 $1 "SELECT * from result" | sqlite3 $NEWDB ".import /dev/stdin result";

sqlite3 $NEWDB "insert or ignore into game select distinct game_id, date('now') from moves where game_id not in (select game_id from game)"; 

