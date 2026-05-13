# Team3CPSC431
Football Database Project

## How to Run
1. Set configuration variables in `config.php`
2. Execute the ddl script in `ddl_updates/ddl.sql`
3. Execute the dml script in `data_manipulation_file/DML.sql`
4. Start LAMPP

## Email Server
- Set the email user, email host, and email app password environment variables in `.httpd.conf` (or hardcode them if testing).
- The email server uses PEAR NET_SMTP, which needs to be installed:
- `sudo /opt/lampp/bin/pear install Mail`
- `sudo /opt/lampp/bin/pear install Net_SMTP`

## Changes Made Post Project Presentations
- Implemented seasons
- Implemented adding, editing, and deleting games
- Implemented automatic handling of win/loss/tie records on modifications to games; this uses database transactions to prevent out-of-sync data
- Implemented adding stats
- Implemented in-app support for creating players and coaches
- Implemented updating player and coach profiles
- Modified database constraints and cascade behaviors
- Extended database exception handling to correctly categorize errors by their type (authorization violation, foreign key error, constraint check error, uniqueness violation, etc.) and produce more specific and understable error messages
- Password reset via email is working and configurable through `config.php`
- Implemented fine-grained access control: coaches can only edit stats of their players, games of teams they are the coach of, etc., and players can only edit their own personal details
- Revised all diagrams: use case, requirements, ERD, implementation, security, and deployment