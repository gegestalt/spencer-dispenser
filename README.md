Show all the messages on the db: 
---------------------------------
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
