CREATE TABLE moves(
	game_id number, 
	player text,
	timestamp integer, 
	move text,
	constraint moves_fk foreign key(game_id) references game(game_id),
	unique(game_id, timestamp)
);

CREATE TABLE result(
	game_id number,
 	winner text,
	score integer,
	constraint result_fk foreign key(game_id) references game(game_id),
	unique(game_id)
);

CREATE TABLE game(
	game_id primary key,
	datetime text
);
