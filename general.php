<?php
//LOGIN
        if ($_GET['url'] == "login"){
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $username = $postBody->username;
                $password = $postBody->password;
                
                if ($db->query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))){
                        if (password_verify($password, password_hash($db->query('SELECT password FROM users WHERE username=:username', array(':username'=>$username))[0]['password'], PASSWORD_DEFAULT))){

                                $cstrong = True;
                                $token = bin2hex(openssl_random_pseudo_bytes(15, $cstrong));
                                $user_id = $db->query('SELECT id FROM users WHERE username=:username', array('username'=>$username))[0]['id'];
                                $db->query('UPDATE users SET token=:token WHERE id=:user_id', array(':token'=>sha1($token), ':user_id'=>$user_id));
                                echo '{ "token": "'.$token.'" }';
                        }else{
                                echo '{ "status": "Username or password is incorrect." }';
                                //  http_response_code(401);
                        }
                }else{
                        echo '{ "status": "Username or password is incorrect." }';
                        //http_response_code(401);
                }

        //AUTHENTICATE
        } else if ($_GET['url'] == "auth"){
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $token = $postBody->token;
                
                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                       echo '{ "status": "Success" }';
                       http_response_code(200);
               } else {
                echo '{ "status": "Failure" }';
                    //   http_response_code(400);
                }

        //CREATE USER
        } else if ($_GET['url'] == "user/create"){
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $first_name = $postBody->first_name;
                $middle_name = $postBody->middle_name;
                $last_name = $postBody->last_name;
                $full_name = $first_name." ".$middle_name." ".$last_name;
                $username = $postBody->username;
                $password = $postBody->password;
                $email = $postBody->email;
   
                if (($db->query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))) == null){
                        $db->query('INSERT INTO users VALUES (null, :first_name, :middle_name, :last_name, :full_name, :username, :password, :email, null, null, "open", null)', array(':first_name'=>$first_name, ':middle_name'=>$middle_name, ':last_name'=>$last_name, ':full_name'=>$full_name, ':username'=>$username, ':password'=>$password, ':email'=>$email));
                             echo '{ "status": "Account was created successfully." }';
                }else{
                        echo '{ "status": "Username already exists." }';
                        http_response_code(401);

                }

        //Get Users
        } else if ($_GET['url'] == "users/get-users") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);
        
                $token = $postBody->token;
                
                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                        $data = $db->query('SELECT id, first_name FROM users');
                        echo json_encode($data);
                        http_response_code(200);
                } else {
                        echo '{ "status": "Token invalid" }';
                        http_response_code(400);
                }

        //SEARCH CLIENT        
        } else if ($_GET['url'] == "client/search"){
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $token = $postBody->token;
                $user_group = $db->query('SELECT group_assigned FROM users WHERE token=:token', array(':token'=>sha1($token)));
                $user_group = $user_group[0]["group_assigned"];
                

                if($db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)))){
                        $data = $db->query('SELECT id, full_name, parent_full_name FROM clients WHERE group_assigned=:user_group ORDER BY full_name', array(':user_group'=>$user_group));
                        echo json_encode($data);
                        http_response_code(200);
                } else {
                echo '{ "status": "Failure" }';
                    //   http_response_code(400);
                }
            } else {
                echo "Invalid Directory.";
                http_response_code(401);
        }