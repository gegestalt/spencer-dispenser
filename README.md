Usage : 

    Show all the messages on the db: 
    ---------------------------------
    - sqlite3 database/chat-app.db, also nore that I have seperated the test db and production db to avoid confusion, tests are done on in memory. so the test date is not shown when the db active.

            SELECT 
                messages.id AS message_id,
                messages.content AS message_content,
                messages.created_at AS message_timestamp,
                users.username AS sender_username,
                groups.name AS group_name
            FROM 
                messages
            JOIN 
                users ON messages.user_id = users.id
            JOIN 
                groups ON messages.group_id = groups.id
            ORDER BY 
                messages.created_at DESC;
        
    -----------------------------------
    Send message to a group: 

            POST: http://localhost:8000/groups/<group_id>/messages
            {
                "user_id":<user_id>,
                "content": "<message_content.>"
            }
    See  messages: 
            GET: http://localhost:8000/groups/<group_id>/messages

    See all groups:
            GET: http://localhost:8000/groups

        (will fetch all the groups from db.)
    Users can see the messages within any group, but they need to join to the group they want to send message to.
    Create a new user:
            POST: http:://localhost:8000/users
            {
                "username":"<username>"
            }
        will return 201 Created with,
            {
                "success": true,
                "user_id": "<user_id>",
                "username": "<username>"
            }
            on success; 
            
    Join to a group:
            POST: http://localhost:8000/groups/<group_id>/join
            {
                "user_id":"user_id"
            } 
        Depending on the users status of memebership server will return 200 on succsess or 400 if the user is already joined 
    ----------------------------------------
    - seed.php script is used for populating the db with some dummy data, dummy data is loaded with seed.php to the application to provide a base for testing relations and functionality. In order to avoid any kind of confusion which may occur by executing the script twice, I have added     clearDatabase($db); such that the dummy data wont be loaded twice and cause errors. At the end of a succsessfull execution the seed.php will load  db with 15 users 7 groups and random repetitive messages. 


    Start the server: 
        php -S localhost:8000 -t public   

    ---------------------------------------
    Tests:
    - I have tested both the database integration and functionality in my constructed tests thats why I have not used createMock for the assignment sake. Functionalities tested in the tests are applied to the general behavior  of the project. 