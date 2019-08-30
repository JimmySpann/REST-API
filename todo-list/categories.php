<?php
//BEGIN :: CATEGORIES/TAGS
        
        //Get Categories/Tags
        if ($_GET['url'] == "categories/get") {
            $postBody = file_get_contents("php://input");
            $postBody = json_decode($postBody);
    
            $token = $postBody->token;
    
            if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                    $user_id = $db->query('SELECT id FROM users WHERE token=:token', array(':token'=>sha1($token)));
                    $user_id = $user_id[0]['id'];
                    $data = $db->query('SELECT * FROM tasks_categories_tags WHERE user_id=:id ORDER BY name', array(':id'=>$user_id));
                    $id = $db->query('SELECT task_cat_main_id FROM users WHERE token=:token', array(':token'=>sha1($token)))[0]['task_cat_main_id'];
                    echo '{ "categories": '.json_encode($data).', "main_category_id": "'.$id.'" }';
                    http_response_code(200);
            } else {
                    echo '{ "status": "Token invalid" }';
                    http_response_code(400);
            }
    
    //Create Categories/Tags
    } else if ($_GET['url'] == "categories/create") {
            $postBody = file_get_contents("php://input");
            $postBody = json_decode($postBody);
    
            $token         = $postBody->token;
            $name          = $postBody->name;
            $type          = $postBody->type;
            $parent        = $postBody->parent;
            $location      = $postBody->location;
            
    
            if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                    $user_id = $db->query('SELECT id FROM users WHERE token=:token', array(':token'=>sha1($token)));
                    $user_id = $user_id[0]['id'];
                    //If Category doesn't already exist
                    if($db->query('SELECT id FROM tasks_categories_tags 
                                   WHERE user_id=:user_id AND name=:name', 
                                   array(':user_id'=>$user_id, ':name'=>$name)) == null){
                            $db->query('INSERT INTO tasks_categories_tags VALUES (null, :user_id, :name, :type, :location, :parent, null)', array(':user_id'=>$user_id, 
                            ':name'=>$name, ':type'=>$type, ':location'=>$location, ':parent'=>$parent));
                            $id = $db->query('SELECT MAX(id) as id FROM tasks_categories_tags WHERE user_id=:user_id', array(':user_id'=>$user_id))[0]['id'];
                            echo '{ "status": "Success", "message": "New category added", "id": "'.$id.'" }';
                            http_response_code(200);
                    } else {
                            echo '{ "status": "Failed", "message": "Category already exists" }';
                            http_response_code(200);
                    }
    
            } else {
                    echo '{ "status": "Token invalid" }';
                    http_response_code(400);
            }
    
    //Update Categories/Tags   
    } else if ($_GET['url'] == "categories/update-categories") {
            $postBody = file_get_contents("php://input");
            $postBody = json_decode($postBody);
    
            $token         = $postBody->token;
            $id            = $postBody->id;
            $name          = $postBody->name;    
            $type          = $postBody->type;    
            $location      = $postBody->location;
            $parent        = $postBody->parent;
    
            if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                    if($db->query('SELECT id FROM tasks_categories_tags WHERE id=:id', array(':id'=>$id))){
                            $db->query('UPDATE tasks_categories_tags SET name=:name, type=:type, location=:location, parent=:parent
                            WHERE id = :id', array(':id'=>$id, //Later add user IDs in WHERE to add security
                            ':name'=>$name, ':type'=>$type, ':location'=>$location, ':parent'=>$parent));
                            echo '{ "status": "Success", "message": "Category has been updated." }';
                            http_response_code(200);
                    } else {
                            echo '{ "status": "Failed", "message": "Error: Invalid ID." }';
                    }
    
           } else {
            echo '{ "status": "Failed", "message": "Error: Invalid token." }';
                //   http_response_code(400);
            }

    //Update Main Category   
    } else if ($_GET['url'] == "categories/update-main-category") {
            $postBody = file_get_contents("php://input");
            $postBody = json_decode($postBody);
    
            $token         = $postBody->token;
            $main_id       = $postBody->main_id;    

            if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                    if($db->query('SELECT id FROM tasks_categories_tags WHERE id=:main_id', array(':main_id'=>$main_id))){
                            $db->query('UPDATE users SET task_cat_main_id=:main_id
                            WHERE token=:token', array(':token'=>sha1($token), ':main_id'=>$main_id));
                            echo '{ "status": "Success", "message": "New Main Category Saved" }';
                            http_response_code(200);
                    } else {
                            echo '{ "status": "Failed", "message": "Error: Invalid ID." }';
                    }
    
           } else {
            echo '{ "status": "Failed", "message": "Error: Invalid token." }';
                //   http_response_code(400);
            }
    
    //Delete Categories/Tags      
    } else if ($_GET['url'] == "categories/delete") {
    
            $body = file_get_contents("php://input");
            $body = json_decode($body);
    
            $token = $body->token;
            $id    = $body->id;
    
            if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                    if($db->query('SELECT id FROM tasks_categories_tags WHERE id=:id', array(':id'=>$id))){
                            $db->query('DELETE FROM tasks_categories_tags WHERE id=:id', array(':id'=>$id)); //Later add user IDs in WHERE to add security
                            echo '{ "status": "Success", "message": "Category was deleted successfully." }';
                            http_response_code(200);
                    } else {
                            echo '{ "status": "Failed", "message": "Error: Invalid id." }';
                    }
    
            } else {
                    echo '{ "status": "Failed", "message": "Error: Invalid token."}';
                    //http_response_code(400);
            }
        }
    //END   :: CATEGORIES                
