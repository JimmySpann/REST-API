<?php


//BEGIN :: TASKS

        //GET TASKS
        if ($_GET['url'] == "tasks/get") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);
        
                $token = $postBody->token;
                $category = $postBody->category;
                
                if(isAuthenicated($db, $token)){
                        $user_id = $db->query('SELECT id FROM users WHERE token=:token', array(':token'=>sha1($token)));
                        $user_id = $user_id[0]['id'];

                        if($category == "*"){
                                $tasks = $db->query('SELECT * FROM tasks WHERE user_id=:id ORDER BY date_created', array(':id'=>$user_id));
                        } else {
                                $tasks = $db->query('SELECT * FROM tasks 
                                         WHERE user_id=:id AND categories=:category ORDER BY date_created', 
                                         array(':id'=>$user_id, ':category'=>$category));
                        }
                        $sections = $db->query('SELECT * FROM tasks_categories_tags 
                                    WHERE user_id=:id AND parent=:category 
                                    ORDER BY id', 
                                    array(':id'=>$user_id, ':category'=>$category));

                        echo '{"tasks": '.json_encode($tasks).', "sections": '.json_encode($sections).'}';
                        http_response_code(200);
                } else {
                        echo '{ "status": "Token invalid" }';
                        http_response_code(400);
                }
        //CREATE TASK
        } else if ($_GET['url'] == "tasks/create") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);
        
                $token         = $postBody->token;
                $task          = $postBody->task;
                $tags          = $postBody->tags;
                $description   = $postBody->description;
                $categories    = $postBody->categories;
                $sections      = $postBody->sections;
                $date_reminder = $postBody->date_reminder;
                $is_subtask    = $postBody->is_subtask;
                
                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                        $user_id = $db->query('SELECT id FROM users WHERE token=:token', array(':token'=>sha1($token)));
                        $user_id = $user_id[0]['id'];
                        $db->query('INSERT INTO tasks VALUES (null, :user_id, :task, :tags, :description, :categories, 
                                    :sections, DEFAULT, null, :date_reminder, DEFAULT, :is_subtask)', 
                                    array(':user_id'=>$user_id, ':task'=>$task, ':tags'=>$tags, ':description'=>$description,
                                    ':categories'=>$categories, ':sections'=>$sections, ':date_reminder'=>$date_reminder, ':is_subtask'=>$is_subtask));
                        $id = $db->query('SELECT MAX(id) as id FROM tasks WHERE user_id=:user_id', array(':user_id'=>$user_id))[0]['id'];
                        echo '{ "status": "Success", "message": "New task added", "id": "'.$id.'" }';
                        http_response_code(200);
                } else {
                        echo '{ "status": "Token invalid" }';
                        http_response_code(400);
                }
        
        //UPDATE TASK      
        } else if ($_GET['url'] == "tasks/update") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);
        
                $token         = $postBody->token;
                $task_id       = $postBody->id;
                $task          = $postBody->task;
                $tags          = $postBody->tags;
                $description   = $postBody->description;
                $categories    = $postBody->categories;
                $sections      = $postBody->sections;
                $date_reminder = $postBody->date_reminder;
                $is_completed  = $postBody->is_completed;
                
                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                        if($db->query('SELECT id FROM tasks WHERE id=:id', array(':id'=>$task_id))){
                                $db->query('UPDATE tasks SET task=:task, tags=:tags, description=:description, categories=:categories, sections=:sections, date_reminder=:date_reminder, is_completed=:is_completed
                                WHERE id = :task_id', array(':task_id'=>$task_id, //Later add user IDs in WHERE to add security
                                ':task'=>$task, ':tags'=>$tags, ':description'=>$description, ':categories'=>$categories, ':sections'=>$sections, ':date_reminder'=>$date_reminder, ':is_completed'=>$is_completed));
        
                                echo '{ "status": "Success", "message": "Task has been updated." }';
                                http_response_code(200);
                        } else {
                                echo '{ "status": "Failed", "message": "Error: Invalid task ID." }';
                        }
        
               } else {
                echo '{ "status": "Failed", "message": "Error: Invalid token." }';
                    //   http_response_code(400);
                }
        
        //DELETE TASK       
        } else if ($_GET['url'] == "tasks/delete") {
        
                $body = file_get_contents("php://input");
                $body = json_decode($body);
        
                $token = $body->token;
                $task_id = $body->id;
        
                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                        if($db->query('SELECT id FROM tasks WHERE id=:id', array(':id'=>$task_id))){
                                $db->query('DELETE FROM tasks WHERE id=:task_id', array(':task_id'=>$task_id)); //Later add user IDs in WHERE to add security
                                echo '{ "status": "Success", "message": "Task was deleted successfully." }';
                                http_response_code(200);
                        } else {
                                echo '{ "status": "Failed", "message": "Error: Invalid task id." }';
                        }
        
                } else {
                        echo '{ "status": "Failed", "message": "Error: Invalid token."}';
                        //http_response_code(400);
                }

        } else if ($_GET['url'] == "tasks/clear-completed") {
        
                $body = file_get_contents("php://input");
                $body = json_decode($body);
        
                $token = $body->token;
                $category_id = $body->category_id;
        
                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                        if($category_id=="*"){
                                $user_id = $db->query('SELECT id FROM users WHERE token=:token', array(':token'=>sha1($token)));
                                $user_id = $user_id[0]['id'];
                                $db->query('DELETE FROM tasks WHERE user_id=:user_id AND is_completed=1', array(':user_id'=>$user_id));
                                echo '{ "status": "Success", "message": "Cleared completed tasks." }';
                        } else if($db->query('SELECT id FROM tasks_categories_tags WHERE id=:category_id', array(':category_id'=>$category_id))){
                                $db->query('DELETE FROM tasks WHERE categories=:category_id AND is_completed=1', array(':category_id'=>$category_id));
                                echo '{ "status": "Success", "message": "Cleared completed tasks." }';
                                http_response_code(200);
                        } else {
                                echo '{ "status": "Failed", "message": "Error: Invalid id." }';
                        }
        
                } else {
                        echo '{ "status": "Failed", "message": "Error: Invalid token."}';
                        //http_response_code(400);
                }
        }
        
        //END   :: TASKS                