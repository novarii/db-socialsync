# SocialSync

A University-based Social Media Platform for the University of Rochester community.

## Overview

SocialSync is a dedicated social media platform designed specifically for the University of Rochester community. It allows users to create profiles, join communities, share posts, comment, and interact with fellow university members in a secure environment.

## Accessing the Website

- **URL**: http://betaweb.csug.rochester.edu/~ebozoglu/
- **Access Requirements**: Must be connected to University of Rochester VPN or using a CSUG computer

## Project Structure

### Root Directory (`/home/ebozoglu`)

- `create.sql`: Database schema creation script
- `load.sql`: Script to load data from CSV files into database
- `csv_data/`: Directory containing all CSV files with test data
- `public_html/`: Web directory accessible via browser

### Web Directory (`/home/ebozoglu/public_html`)

All files in this directory are accessible through the web URL.

#### Core Files

- `index.html`: Login page
- `signup.html`: Registration page
- `home.php`: Main feed page after login
- `profile.php`: User profile page
- `communities.php`: List of all communities
- `community.php`: Single community page with posts

#### Authentication

- `login.php`: Handles user login
- `register.php`: Processes new user registration
- `logout.php`: Ends user session

#### Content Management

- `create_post.php`: Creates new posts
- `create_community.php`: Creates new communities
- `add_comment.php`: Adds comments to posts
- `post.php`: Single post view with comments
- `vote.php`: Handles voting on posts and comments
- `subscribe.php`: Manages community subscriptions

#### Moderation

- `admin.php`: Administrator control panel
- `update_role.php`: Changes user roles
- `delete_post.php`: Removes posts
- `delete_comment.php`: Removes comments

#### Configuration and Styling

- `config/`: Contains database configuration (`db.php`)
- `styles/`: CSS styling files (`main.css`, `auth.css`)

## Database Schema

The application uses a MariaDB database with the following tables:

- **USERS**: User accounts and credentials
- **COMMUNITIES**: Community information
- **POSTS**: User posts in communities
- **COMMENTS**: Comments on posts
- **VOTES**: Upvotes/downvotes on posts and comments
- **SUBSCRIBES_TO**: Community membership relations

## Features

- User authentication and registration
- Community creation and management
- Post creation and commenting
- Post and comment voting system
- Community subscription functionality
- User profile management
- Administrative moderation tools

## Future Development

- Saving posts functionality will be implemented in a future update. Currently, the save button is present but non-functional.

## Installation

To set up a local development environment:

1. Clone the repository
2. Import the database schema using `create.sql`
3. Load test data using `load.sql` and the CSV files in `csv_data/`
4. Configure your database connection in `config/db.php`
5. Point your web server to the `public_html` directory

## Contributors

- Original development by ebozoglu

## License

[Your license information here]