<?php 

    class DbOperations{

        private $con; 

        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

        //PARTIE USER
        public function createUser($email, $password, $name){
           if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO vivi_users (email, password, name) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $email, $password, $name);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           }
           return USER_EXISTS; 
        }

        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM vivi_users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, name FROM vivi_users;");
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['name'] = $name;
                array_push($users, $user);
            }             
            return $users; 
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, name FROM vivi_users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['name'] = $name;
            return $user; 
        }

        public function updateUser($email, $name, $id){
            $stmt = $this->con->prepare("UPDATE vivi_users SET email = ?, name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $email, $name, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE vivi_users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss",$hash_password, $email);

                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;

            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

        public function deleteUser($id){
            $stmt = $this->con->prepare("DELETE FROM vivi_users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM vivi_users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
        // ------------------------------------------------------------------------------ //

        // PARTIE PRONOS
        public function createPronos($equipe1, $equipe2, $cote1, $cote2, $matchNull){
                $stmt = $this->con->prepare("INSERT INTO vivi_pronos (equipe1, equipe2, cote1, cote2, matchNull) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssss', $equipe1, $equipe2, $cote1, $cote2, $matchNull);
                if($stmt->execute()){
                    return PRONOS_CREATED;
                }
            return PRONOS_FAILURE;
        }

        public function getAllPronos(){
            $stmt = $this->con->prepare("SELECT id, equipe1,equipe2, cote1, cote2, matchNull FROM vivi_pronos;");
            $stmt->execute();
            $stmt->bind_result($id, $equipe1, $equipe2, $cote1, $cote2, $matchNull);
            $pronos = array();
            while($stmt->fetch()){
                $prono = array();
                $prono['id'] = $id;
                $prono['equipe1']= $equipe1;
                $prono['equipe2'] = $equipe2;
                $prono['cote1'] = $cote1;
                $prono['cote2'] = $cote1;
                $prono['matchNull'] = $matchNull;
                array_push($pronos, $prono);
            }
            return $pronos;
        }
    }