-- Create the USERS table
CREATE TABLE USERS (
    user_id CHAR(36) PRIMARY KEY, -- had to store as char since MariaDB does not have support for UUID
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'regular_user' 
        CHECK (role IN ('regular_user', 'moderator', 'administrator')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create the COMMUNITIES table
CREATE TABLE COMMUNITIES (
    community_id CHAR(36) PRIMARY KEY,
    community_name VARCHAR(100) NOT NULL UNIQUE,
    creator_user_id CHAR(36),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_user_id) REFERENCES USERS(user_id) ON DELETE SET NULL
);

-- Create the POSTS table
CREATE TABLE POSTS (
    post_id CHAR(36) PRIMARY KEY,
    author_user_id CHAR(36) NOT NULL,
    community_id CHAR(36) NOT NULL,
    post_content TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES COMMUNITIES(community_id) ON DELETE CASCADE
);

-- Create the COMMENTS table
CREATE TABLE COMMENTS (
    comment_id CHAR(36) PRIMARY KEY,
    author_user_id CHAR(36) NOT NULL,
    post_id CHAR(36) NOT NULL,
    comment_content TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES POSTS(post_id) ON DELETE CASCADE
);

-- Create the VOTES table
CREATE TABLE VOTES (
    vote_id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    post_id CHAR(36),
    comment_id CHAR(36),
    vote_type SMALLINT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES POSTS(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES COMMENTS(comment_id) ON DELETE CASCADE,
    CONSTRAINT check_vote_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR
        (post_id IS NULL AND comment_id IS NOT NULL)
    )
);

-- Create the SUBSCRIBES_TO table
CREATE TABLE SUBSCRIBES_TO (
    user_id CHAR(36) NOT NULL,
    community_id CHAR(36) NOT NULL,
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, community_id),
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES COMMUNITIES(community_id) ON DELETE CASCADE
);