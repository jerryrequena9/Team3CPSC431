## Changes Made Post Project Presentations
- Implemented seasons
- Implemented adding, editing, and deleting games
- Implemented automatic handling of win/loss/tie records on modifications to games; this uses database transactions to prevent out-of-sync data
- Implemented adding stats
- Implemented in-app support for creating players and coaches
- Implemented updating player and coach profiles
- Modified database constraints and cascade behaviors
- Extended database exception handling to correctly categorize errors by their type (authorization violation, foreign key error, constraint check error, uniqueness violation, etc.) and produce more specific and understable error messages
- Password reset via email is working and configurable through config.php
- Implemented fine-grained access control: coaches can only edit stats of their players, games of teams they are the coach of, etc., and players can only edit their own personal details
- Revised all diagrams: use case, requirements, ERD, implementation, security, and deployment
- Use case view: higher level, removed use cases such as login, logout, view team
- Requirement view: added a diagram depicting each role and the actions they have access to, removed functional requirements and added requirements about scalability, maintainability, and usability.
- ERD: added a ‘ties’ field to seasons and removed ‘champion’
- Implementation: reorganized our code files into folders
- Security view: for integrity, we explained how the use of database transactions protects data integrity
- Deployment view: depicted that multiple clients can connect to our server at the same time, specified the use of PEAR SMTP for emailing and resetting passwords

