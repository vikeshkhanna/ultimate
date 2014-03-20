<!DOCTYPE html>
<html>
	<head>
		<title>Ultimate Tic Tac Toe</title>
			<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
			<link rel="stylesheet" href="css/main.css">
			<script src="http://d3js.org/d3.v3.min.js"></script>
			<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
			<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
			<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
			<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
			<script src="js/blockui.js"></script>
			<script src="js/uuid.js"></script>
			<script src="js/chart.js"></script>

			<style>
		</style>

			<script type="text/javascript">
				var X='X';
				var O='O';
				var Z='-';
				var signs = [X, O, Z];

				// current_state maintains current state of the game. 
				// player: Next player to play
				// r, c: Co-ordinates of next board to play on
				// winners : {winner, cells} r x c, maintains the winner of boards and the cells that lead to win. May have Z for tied boards
				var current_state = {};

				var timestamp=0;
				var game_id=0;	
				var uboard_container = "#uboard-container";

				function get_board_id(r, c)
				{
					return "r"+r+"c"+c;
				}

				function get_opponent(player)
				{
					return (player===X?O:X);
				}

				function get_cell_id(r,c,i,j)
				{
					return get_board_id(r,c)+"i"+i+"j"+j;
				}

				function is_board_playable(r, c)
				{
					if(current_state.r===-1 || (current_state.r===r && current_state.c===c)){
						return true;
					}

					return false;
				}

				function is_super_winner(player)
				{
						winners = current_state.winners;

						for(var i=0; i<3; i++){
							if(winners[i][0].winner===player && winners[i][1].winner===player && winners[i][2].winner===player){
								return true;
							}

							if(winners[0][i].winner===player && winners[1][i].winner==player && winners[2][i].winner===player){
								return true;
							}
						}

						if(winners[0][0].winner===player && winners[1][1].winner===player && winners[2][2].winner===player){
							return true;
						}

						if(winners[0][2].winner===player && winners[1][1].winner===player && winners[2][0].winner===player){
							return true;
						}

					return false;
				}

				function deserialize(data)
				{
					var list = data.split("\n");
					player = list[0];
					pos = list[1].split(" ");
					r = parseInt(pos[0]);
					c = parseInt(pos[1]);

					list = list.slice(2);
					data = new Array(3);

					for(var i=0; i<3;i++)
					{
						data[i] = new Array(3);
						for(var j=0;j<3;j++)
						{
							data[i][j] = new Array(3);
							for(var k=0;k<3; k++)
							{
								data[i][j][k] = new Array(3);
							}
						}
					}

					for(var i=0;i<list.length;i++)
					{
						for(var j=0;j<list[i].length;j++)
						{
							br = Math.floor(i/3);
							bc = Math.floor(j/3);
							bri = i%3;
							brj = j%3;
							data[br][bc][bri][brj] = list[i][j];
						}
					}

					return {r:r, c:c, data:data};
				}

				function serialize(state)
				{
					data = state.data;
					lst = [state.player, state.r + " " + state.c];

					for(var r=0; r<9; r++)
					{
						str = "";
						
						for(var c=0; c<9; c++)
						{
							str += data[Math.floor(r/3)][Math.floor(c/3)][r%3][c%3];
						}

						lst.push(str);
					}		

					return lst.join("\n");
				}

				function is_tied(r, c)
				{
					data = current_state.data[r][c];
					for(var i=0; i<3; i++)
					{
						cache = {}

						for(var j=0; j<4;j++)
						{
							cache[j] = {}

							for(var k=0; k<signs.length;k++)
							{
								cache[j][signs[k]]=0;
							}
						}

						for(var j=0;j<3;j++)
						{
							cache[0][data[i][j]]+=1;
							cache[1][data[j][i]]+=1;
							cache[2][data[j][j]]+=1;
							cache[3][data[j][3-j-1]]+=1;
						}

						for (var j=0;j<4;j++)
						{
							// No tie if either no X, or no O and at least one empty space -- To ensure we dont consider a win as tie
							if((cache[j][X]===0 || cache[j][O]===0) && cache[j][Z]!=0)
							{
								return false;
							}
						}
					}

					return true;
				}

				function is_super_tied()
				{
					data = current_state.winners;

					for(var i=0; i<3; i++)
					{
						cache = {}

						for(var j=0; j<4;j++)
						{
							cache[j] = {}

							for(var k=0; k<signs.length;k++)
							{
								cache[j][signs[k]]=0;
							}
						}
						
						for(var j=0;j<3;j++)
						{
							cache[0][data[i][j].winner]+=1;
							cache[1][data[j][i].winner]+=1;
							cache[2][data[j][j].winner]+=1;
							cache[3][data[j][3-j-1].winner]+=1;
						}

						// Not tied only if either no X or no Y and no board is tied
						for (var j=0;j<4;j++)
						{
							if((cache[j][X]===0 || cache[j][O]===0) && cache[j][Z]===0){
								return false;
							}
						}
					}

					return true;
				}

				// Returns true if all the cells have been occupied
				function is_full(r, c)
				{
					data = current_state.data[r][c];

					for(var i=0;i<3;i++)
					{
						for(var j=0;j<3;j++)
						{
							if(data[i][j]===Z) 
								return false;
						}
					}

					return true;
				}

				// Returns true only if board is either won by a player or full. Not in case of ties. See winner!=Z check. 
				function is_finished(r, c)
				{
					data = current_state.data;

					if(current_state.winners[r][c].winner!=null && current_state.winners[r][c].winner!=Z){
						return true;
					}
			
					return is_full(r,c);
				}

				function make_board(udata)
				{
						r = udata.r;
						c = udata.c;

						data = [];

						for(var i=0;i<udata.data.length;i++)
						{
							data[i] = []

							for(var j=0;j<udata.data[i].length;j++)
							{
								data[i][j] = {}
								data[i][j].value = udata.data[i][j];
								data[i][j].i = i;
								data[i][j].j = j;
								data[i][j].r = r;
								data[i][j].c = c;
							}
						}

						var table = document.createElement("table");
						table = d3.select(table)
							.attr("class", function(){ 
										var classes = "board ";
										if(is_board_playable(r, c)){
												classes += "playable-board";
											}
										return classes;
									})
							.attr("id", get_board_id(r,c));
						
						var thead = table.append("thead"),
								tbody = table.append("tbody");

							// Create a row for each object in the data
							var rows = tbody.selectAll("tr")
								.data(data)
								.enter()
								.append("tr");

							// Create a cell in each row for each column
							var cells = rows.selectAll("td")
							.data(function(row) {
										return row;
								})
							.enter()
							.append("td")
							.html(function(d) {
											if(d.value==Z){
												return " ";
											}
											return d.value;
										})
							.attr("id", function(d){
											return get_cell_id(r,c,d.i,d.j);
										})
							.attr("class", function(d){
										var val = current_state.data[d.r][d.c][d.i][d.j];

										if(val===Z && current_state.player===X && is_board_playable(d.r, d.c)){
												return "playable-cell playable-cell-"+current_state.player;
											}
		
										obj = current_state.winners[d.r][d.c];

										if(obj!=null && obj.winner!=null){
											classes = "win ";	
											cells = obj.cells;
											classes = "win win-"+obj.winner;
	
											for(var i=0;i<cells.length;i++){
												if(cells[i][0]===d.i && cells[i][1]===d.j)
												{
													return classes;
												}
											}
										}
										return "hold hold-"+val;	
									})
							.on("click", function(d){
									if(d.value===Z && current_state.player===X && is_board_playable(d.r, d.c)){
										// This will always be X
										move = {player: current_state.player, r:d.r, c:d.c, i:d.i, j:d.j};
										evaluate(move);
										restart(uboard_container);
										ai();
									}
								});

						return table;
				}

				function highlight_fade(selector, duration, original)
				{
					var restore = (original === undefined ? $(selector).css('background-color') : original);
					var delay = (duration === undefined ? 1200 : duration);
					$(selector).css('background-color', 'yellow');
					$(selector).animate({backgroundColor:restore}, {duration:delay});
				}
	
				function ai()
				{
					// Send request to AI to make the next move
					$.ajax({
								url: "api/move.php",
								type: "GET",
								data: {"state" : serialize(current_state)}, 
								dataType:"json",
								success: function(response){
									p = response["move"].split(" ");
									move = {player: O, r:parseInt(p[0]), c:parseInt(p[1]), i:parseInt(p[2]), j:parseInt(p[3])};
									evaluate(move);
									restart(uboard_container);

									// After restart animate the td
									var selector = "#" + get_cell_id(move.r, move.c, move.i, move.j);
									highlight_fade(selector);
								}
							});				
				}

				function upload_move(move)
				{
					var m = move;
					move_str = m.r + " " + m.c + " " + m.i + " " + m.j;

					// Record the move for this game id and timestamp
					$.ajax({
								url: "api/set_move.php", 
								type: "POST",
								data : {"game_id":game_id, "player":m.player, "move":move_str, "timestamp":timestamp},
								dataType: "json",
								success: function(response) { 
									console.log("Move uploaded successfully");
									console.log(response);
								}
						});
				}

				function upload_result(winner)
				{
					// Record result for this game
					$.ajax({
								url: "api/set_result.php", 
								type: "POST",
								data : {"game_id":game_id, "winner":winner, "score":timestamp},
								dataType: "json",
								success: function(response) { 
									console.log("Result uploaded successfully");
									console.log(response);
								}
						});
					}

				function evaluate(move)
				{
					m = move;
					timestamp+=1;

					console.log("Evaluating")
					console.log(m);

					data = current_state.data;
					board = data[m.r][m.c];
					board[m.i][m.j]=m.player;
					
					if(current_state.winners[m.r][m.c].winner===null){
						for(var i=0; i<3; i++){
							if(board[i][0]===m.player && board[i][1]===m.player && board[i][2]===m.player){
								current_state.winners[m.r][m.c] = { winner:m.player, cells: [[i,0],[i,1],[i,2]]};
								break;
							}

							if(board[0][i]===m.player && board[1][i]===m.player && board[2][i]===m.player){
								current_state.winners[m.r][m.c] = { winner:m.player, cells: [[0,i],[1,i],[2,i]]};
								break;
							}
						}

						if(board[0][0]===m.player && board[1][1]===m.player && board[2][2]===m.player){
							current_state.winners[m.r][m.c] = { winner:m.player, cells: [[0,0],[1,1],[2,2]]};
						}

						if(board[0][2]===m.player && board[1][1]===m.player && board[2][0]===m.player){
							current_state.winners[m.r][m.c] = { winner:m.player, cells: [[0,2],[1,1],[2,0]]};
						}
					}

					// If the winners have still not been assigned, check if board is tied or full and assign Z as the winner
					if(current_state.winners[m.r][m.c].winner===null)
					{
						// If board is tied, assign Z as the winner
						if(is_tied(m.r, m.c)){
							current_state.winners[m.r][m.c] = {winner:Z, cells: []}
						}
					}

					if(is_super_winner(X)){
						$(uboard_container).block({message:"You have won!"});
						upload_result(X);
					}
					else if(is_super_winner(O)){
						$(uboard_container).block({message:"You lost!"});
						upload_result(O);
					}
					else if(is_super_tied()){
						$(uboard_container).block({message:"You have tied the match!"});
						upload_result(Z);
					}

					$("#stats-user-score").html(timestamp);
					highlight_fade("#stats-user-score", 1200, "#F7F7F7");

					current_state.r = is_finished(m.i, m.j) ? -1 : m.i;
					current_state.c = is_finished(m.i, m.j) ? -1 : m.j;
					current_state.player = get_opponent(m.player);

					upload_move(m);
				}

				function tabulate(data)
				{
						udata = Array(data.length);

						for(var i=0; i<data.length; i++)
						{
							udata[i] = Array(data[i].length);

							for(var j=0; j<data[i].length; j++)
							{
								udata[i][j] = {}
								udata[i][j].r = i;
								udata[i][j].c = j;
								udata[i][j].data = data[i][j];
							}
						}

						var uboard = document.createElement("table");
						uboard = d3.select(uboard).attr("class", "uboard")
						ubody = uboard.append("tbody")

						var urows = ubody.selectAll("tr")
							.data(udata)
							.enter()
							.append("tr")

						var ucells = urows.selectAll("td")
								.data(function(row) {
									return row;
								})
							.enter()
							.append("td")
							.append(function(d){
									var board = make_board(d);
									return board[0][0];
							});

							return uboard;
					}	

					function init()
					{
						// Generate a new game id and set timestamp to 0
						game_id = uuid.v1();
						timestamp = 0;
						$("#stats-user-score").html(timestamp);

						var sample = ["X",
											"-1 -1",
											"---------",
											"---------", 
											"---------",
											"---------",
											"---------",
											"---------",
											"---------",
											"---------",
											"---------"].join("\n");

						current_state = deserialize(sample);
						current_state.player = X;
						current_state.winners = []
								
						for(var i=0; i<3; i++)
						{
							current_state.winners[i] = [];
							for(var j=0; j<3; j++)
							{
								current_state.winners[i][j] = {winner:null, cells:[]};
							}
						}

						restart(uboard_container);
						$(uboard_container).unblock();
					}

					function restart(selector)
					{
						d3.select(".uboard").remove();	
						var uboard = tabulate(current_state.data);
						d3.select(selector).append(function(d){ return uboard[0][0]; });
					}

					$(document).ready(function(){
						init();

						$.ajax({
								url: "api/stats.php",
								type: "GET",
								dataType:"json",
								success: function(response){
										var total = parseInt(response["total"])
										var O_win = parseInt(response[O]);
										var X_win = parseInt(response[X]);
										var ties = parseInt(response[Z]);
										var human_ratio = parseFloat(X_win*100.0/(ties+ O_win + X_win)).toFixed(2);

										$("#stats-total").html(total);
										$("#stats-X").html(X_win);
										$("#stats-O").html(O_win);
										$("#stats-ties").html(ties);
										$("#stats-human-ratio").html(human_ratio);

										var labelSize = '14';
										var labelColor = 'black';

										var data = [
												{
													value: ties,
													color:"lightgray",
													label:"Ties",
													labelColor: labelColor,
													labelFontSize: labelSize
												},
												{
													value : X_win,
													color: "lightcoral",
													label:"Human Won",
													labelColor: labelColor,
													labelFontSize: labelSize
												},
												{
													value : O_win,
													color: "lightblue",
													label:"Bot Won",
													labelColor: labelColor,
													labelFontSize: labelSize
												}
											];

									var ctx1 = document.getElementById("chart-container").getContext("2d");
									var ctx2 = document.getElementById("small-chart-container").getContext("2d");
									new Chart(ctx1).Pie(data);
									new Chart(ctx2).Pie(data);
								}
							});


							$.ajax({
								url: "api/score.php",
								type: "GET",
								dataType:"json",
								success: function(response){
									$("#stats-best-score").html(response["best"]);
									$("#stats-avgX-score").html(parseFloat(response["avg_X"]).toFixed(2));
									$("#stats-avgO-score").html(parseFloat(response["avg_O"]).toFixed(2));
								}
							});

						 	
						});

					</script>
		</head>

		<body>
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=423906327755939";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>

		<div class="container">
			<h1 class="page-header">Ultimate Tic-Tac Toe</h1>
			<div id="outer-uboard-container" class="row">
					<div id="uboard-container" class="col-xs-7">
					</div>
					<div class="col-xs-3">
						<h2>Options</h2>
						<ul class="list-unstyled">
							<li><a href="#" onclick="init();">Restart Game</a></li>
							<li><a href="#rules">Rules</a></li>
							<li><a href="#data">Data</a></li>
						</ul>
						
						<ul class="list-inline">
						<li><a href="https://twitter.com/share" class="twitter-share-button" data-via="vikeshkhanna">Tweet</a></li>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

<li><div class="fb-like" data-href="http://vikeshkhanna.webfactional.com/ultimate/" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div></li>
						</ul>
						<p class="lead" style="margin-top:25px">Play Ultimate. Beat the bot. </p>
						<canvas id="small-chart-container" width="200" height="200"></canvas>
						
						<ul class="list-unstyled" style="margin-top:25px">
							<li><strong>Your Score (Moves): </strong><span id="stats-user-score" class="lead">0</span></i>
							<li><strong>Best Score : </strong><span id="stats-best-score" class="lead"></span></i>
							<li><strong>Average moves to win (X) : </strong><span id="stats-avgX-score" class="lead"></span></i>
							<li><strong>Average moves to win (Bot) : </strong><span id="stats-avgO-score" class="lead"></span></i>
						</ul>
					</div>
			</div>

			<div id="rules">
				<h1 class="page-header">Rules</h1>
				<p>Like the original Tic-Tac-Toe, Player 1 is represented by X and Player 2 is represented by O. To start the game, Player 1 places an X on any one of the 81 empty squares, and then players alternate turns. However, after the initial move, players must play the board that mirrors the square from the previous player. If the next move is to a board that has already been won, then that player may choose an open square on any board for that turn. You win boards as usual, but you win the game when you win three boards together (across rows, columns or diagnols). <br><br> For instance, if the first move by X is the first image, player O is forced to play in the top-right corner as shown in the right image. </p>
				<p>
					<div class="row">
						<img class="col-md-5" src="http://mathwithbaddrawings.files.wordpress.com/2013/06/3-first-move.jpg" />
						<img class="col-md-5" src="http://mathwithbaddrawings.files.wordpress.com/2013/06/4-second-move.jpg" />
					</div>
				</p>
	
				<p>
					Read more about the game <a href="http://mathwithbaddrawings.com/2013/06/16/ultimate-tic-tac-toe/" target="_blank">here.</a>
				</p>
			</div>
			<div id="data">
				<h1 class="page-header">Data</h1>
				<canvas id="chart-container" width="400" height="400"></canvas>
				<ul class="list-unstyled">
					<li><strong>Total games played : </strong><span id="stats-total" class="lead"></span></li>
					<li><strong>Humans won : </strong><span  id="stats-X" class="lead"></span></li>
					<li><strong>Bots won: </strong><span id="stats-O" class="lead"></span></li>
					<li><strong>Ties: </strong><span id="stats-ties" class="lead"></span></li>
					<li><strong>Humans win <span id="stats-human-ratio" class="lead"></span> % of the games</strong></li>
				</ul>
			</div>
		</div>
			<!--Footer begins-->
			<div class="footer">
				<div class="container">
					Built by <a href="http://stanford.edu/~vikesh">Vikesh</a>
				</div>
			</div>
			<!--Footer ends-->
		</body>
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-49142461-1', 'webfactional.com');
		  ga('send', 'pageview');
		</script>
</html>
