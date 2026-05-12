# Team3CPSC431
Football Database Project

# How to Run
1. Set config variables in config.php
2. Execute the ddl script in ddl_updates/ddl.sql
3. Execute the dml script in data_manipulation_file/DML.sql
4. Start LAMPP

# Email Server
Set the email and email app password environment variables in .httpd.conf
The email server uses PEAR NET_SMTP.

# Changes Made Post Project Presentations
- Implemented seasons and win/loss records
- Implemented adding, editing, and deleting games
- Implemented adding stats
- Password reset via email is working
- Implemented fine-grained access control: coaches can only edit stats of their players, games of teams they are the coach of, etc, and players can only edit their own personal details.