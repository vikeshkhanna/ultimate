CREATE TABLE moves(
	game_id number, 
	player text,
	timestamp integer, 
	move text,
	constraint moves_fk foreign key(game_id) references game(game_id)
);

CREATE TABLE result(
	game_id number,
 	winner text,
	constraint result_fk foreign key(game_id) references game(game_id)
);

create index if not exists result_winner on result(winner);

CREATE TABLE game(
	game_id number primary key, 
	datetime text
);

create index if not exists result_winner on result(winner);
