import copy
import sys

X='X'
O='O'
Z='-'
N=3
R=1
MAX_DEPTH=2
DEBUG=False

def get_opponent(player):
	if player==X:
		return O
	else:
		return X

def read_board(N):
	board = [[0 for i in range(N)] for j in range(N)]

	for i in range(N):
		s = raw_input()

		for j, char in enumerate(s):
			board[i][j] = s[j]
	
	return board

def is_finished(board):
	if get_winner(board)!=None:
		return True

	for i in range(3):
		for j in range(3):
			if board[i][j]==Z:
				return False

	return True

# Return if player is winner
def is_winner(board, player):
	# check by row
	for i in range(N):
		found = True

		for j in range(N):
			if board[i][j]!=player:
				found = False
				break

		if found:
			return True
	
	# check by column
	for i in range(N):
		found = True

		for j in range(N):
			if board[j][i]!=player:
				found = False
				break

		if found:
			return True
	
	found = True

	# check diagnol
	for i in range(N):
		if board[i][i]!=player:
			found = False
			break

	if found:
		return True

	found = True

	# check diagnol
	for i in range(N):
		if board[i][N-i-1]!=player:
			found = False
			break

	if found:
		return True
	
	return False

# Return the winner or None
def get_winner(board):
	# Return board winners
	Xwin = is_winner(board, X)
	Owin = is_winner(board, O)

	# In the rare there are two winners on the board, consider no winner
	if Xwin and not Owin:
		return (X)
	elif Owin and not Xwin:
		return (O)
	elif Xwin and Owin:
		return (X, O)
	else:
		return None

def get_u_winner(uboard, r, c):
	if (r,c) in outliers:
		return outliers[(r,c)]

	board = get_board(uboard, r, c)
	return get_winner(board)

def get_winners(uboard):
	winners = [[None for i in range(N)] for j in range(N)]

	for i in range(N):
		for j in range(N):
			winners[i][j] = get_u_winner(uboard, i, j)
	
	return winners

def get_super_winner_inner(winners):
	# check by row
	for i in range(N):
		first = winners[i][0]
		found = True

		for j in range(N):
			if winners[i][j]!=first or winners[i][j]==None:
				found = False
				break

		if found:
			return first
	
	# check by column
	for i in range(N):
		first = winners[0][i]
		found = True

		for j in range(N):
			if winners[j][i]==None or winners[j][i]!=first:
				found = False
				break

		if found:
			return first
	
	found = True
	first = winners[0][0]

	# check diagnol
	for i in range(N):
		if winners[i][i]==None or winners[i][i]!=first:
			found = False
			break

	found = True
	first = winners[0][N-1]

	# check diagnol
	for i in range(N):
		if winners[i][N-i-1]==None or winners[i][N-i-1]!=first:
			found = False
			break

	if found:
		return first
	
	return None

# Return the winner of the uboard or None
def get_super_winner(uboard):
	winners = get_winners(uboard)
	return get_super_winner_inner(winners)
	
# Get one specific board from the ultimate board
def get_board(uboard, r, c):
	r1, r2 = r*3, (r+1)*3
	c1, c2 = c*3, (c+1)*3
	board = [[0 for i in range(3)] for j in range(3)]

	for i, row in enumerate(uboard):
		for j, pos in enumerate(row):
			if (i>=r1 and i<r2) and (j>=c1 and j<c2):
				board[i%3][j%3] = uboard[i][j]

	return board

def uset(uboard, r, c, i, j, val):
	uboard[3*r+i][3*c+j] = val

def local_reward(board, player):
	new_board = copy.deepcopy(board)

	for i in range(N):
		for j in range(N):
			if new_board[i][j]==Z:
				original = new_board[i][j]
				new_board[i][j]=player
				if is_winner(new_board, player):
					return 1
				new_board[i][j] = original

	return 0

def board_stats(board, player):
	l1 = is_winner(board, player)+0
	l2 = is_winner(board, player)+0
	return 2*R*l1 + R*l2

def stats(uboard, player):
	R = 1
	total = 0
	winners = get_winners(uboard)
	win_cnt = 0

	for i in range(N):
		for j in range(N):
			board = get_board(uboard, i, j)
			total += board_stats(board, player)

	if get_super_winner(uboard)==player:
		total += 100*R
	
	for i in range(N):
		for j in range(N):
			original = winners[i][j]
			board = get_board(uboard, i, j)
			advantage = local_reward(board, player)

			if original == None:
				winners[i][j] = player
				total += (advantage+1)*25*R*((get_super_winner_inner(winners)==player)+0)
				winners[i][j] = original
	
	return total

def pretty_print(uboard):
	for i in range(N*N):
		for j in range(N*N):
			print(uboard[i][j] + " "),
		
			if (j+1)%3==0:
				print("|"),
		
		print("")

		if (i+1)%3==0:
			print("".join(["===" for k in range(N*N)]))
		print("")

def fx(uboard, player):
	opponent = get_opponent(player)
	s1 = stats(uboard, player)
	s2 = stats(uboard, opponent)

	if DEBUG:
		pretty_print(uboard)
		print "Stats", player, s1, s2

	return s1-s2

def play(uboard, r, c, player, fplayer, depth=0):
	board = get_board(uboard, r, c)
	opponent = get_opponent(player)
	new_uboard = copy.deepcopy(uboard)
	best_move = ()
	best_reward = -1000

	if player!=fplayer:
		best_reward = 1000

	if DEBUG:
		print "Beginning", player, r, c, depth

	if get_super_winner(new_uboard)!=None or depth==MAX_DEPTH:
		return (best_move, fx(new_uboard, fplayer))


	rcache = {}

	if min(r,c)==-1 or is_finished(board):
		center = (1,1,1,1)

		for rp in range(N):
			for cp in range(N):
				for i in range(N):
						for j in range(N):
							board = get_board(new_uboard, rp, cp)
							original = board[i][j]

							if original==Z:
								if DEBUG:
									print "Placing %s at"%player, rp, cp, i, j
								uset(new_uboard, rp, cp, i, j, player)
								move, reward = play(new_uboard, i, j, opponent, fplayer, depth+1)
								rcache[(rp,cp,i,j)]=reward

								if (player==fplayer and reward>=best_reward) or (fplayer!=player and reward<=best_reward):
										if DEBUG:
											print "Updating reward", player, rp, cp, i, j, reward
										best_move = (rp, cp, i, j)
										best_reward = reward

							uset(new_uboard, rp, cp, i, j, original)

		if center in rcache and rcache[center]==best_reward:
			best_move = center
	else:
		center = (1,1)
		for i in range(N):
			for j in range(N):
					original = board[i][j]

					if original==Z:
							if DEBUG:
								print "Placing %s at"%player, r, c, i, j

							uset(new_uboard, r, c, i, j, player)
							state, reward = play(new_uboard, i, j, opponent, fplayer, depth+1)
							rcache[(i,j)] = reward

							if (player==fplayer and reward>=best_reward) or (player!=fplayer and reward<=best_reward):
								if DEBUG:
									print "Updating reward", player, r, c, i, j, reward

								best_move = (r, c, i, j)
								best_reward = reward

							uset(new_uboard, r, c, i, j, original)

		if center in rcache and rcache[center]==best_reward:
			best_move = (r,c,1,1)
	
	return (best_move, best_reward)
	
N=3
outliers = {}

def analyze(uboard):
	for r in range(N):
		for c in range(N):
			board = get_board(uboard, r, c)
			winner = get_winner(board)

			if winner!=None:
				if len(winner)==1:
					outliers[(r,c)]=winner[0]
				else:
					outliers[(r,c)]=None

def main():
	player = str(raw_input())
	r,c = tuple(map(int, raw_input().split()))
	uboard = read_board(9)	
	analyze(uboard)

	if DEBUG:
		print("Original board")
		pretty_print(uboard)

	move, reward = play(uboard, r, c, player, player)
	nr, nc, ni, nj = move
	print nr, nc, ni, nj

if __name__=="__main__":
	if len(sys.argv)>1:
		DEBUG=True
	main()

