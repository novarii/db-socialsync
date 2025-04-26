-- Load USERS
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/users.csv' INTO TABLE USERS
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (user_id,username,email,password,role,created_at);


-- Load COMMUNITIES
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/communities.csv' INTO TABLE COMMUNITIES
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (community_id, community_name, creator_user_id, created_at);

-- Load POSTS
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/posts.csv' INTO TABLE POSTS
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (post_id, author_user_id, community_id, post_content, created_at);

-- Load COMMENTS
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/comments.csv' INTO TABLE COMMENTS
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (comment_id, author_user_id, post_id, comment_content, created_at);

-- Load VOTES for posts
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/votes_posts.csv' INTO TABLE VOTES
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (vote_id, user_id, post_id, @dummy, vote_type, created_at);

-- Load VOTES for comments
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/votes_comments.csv' INTO TABLE VOTES
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (vote_id, user_id, @dummy, comment_id, vote_type, created_at);

-- Load SUBSCRIBES_TO
LOAD DATA LOCAL INFILE '/home/ebozoglu/csv_data/subscribes_to.csv' INTO TABLE SUBSCRIBES_TO
  FIELDS TERMINATED BY ',' 
  ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  IGNORE 1 LINES
  (user_id, community_id, subscribed_at);