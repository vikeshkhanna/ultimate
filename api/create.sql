CREATE TABLE moves(
	game_id number, 
	player text,
	timestamp integer, 
	move text,
);

CREATE TABLE result(
	game_id number,
 	winner text,
);

CREATE TABLE game(
	game_id,
	datetime text,
	score integer
);
