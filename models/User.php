<?php
class User{
    /*Attributes */

    const TABLE_NAME = "user";
    private $conn;

    private $id;
    private $name;
    private $password;
    /*Methods */
    function __construct($conn) {
        $this->conn =           $conn; ///< connection is mandatory at instatioation
        $this->id =             null;
        $this->name =           null;
        $this->password =       null;
    }

    public function read(bool $getId=true,
                         bool $getName=true,
                         bool $getPassword=true){
        /*Returns a list of all users from DB
        order of arguments: id, name, password
        all arguments must be boolean 
        */
        if(!$getId && !$getName && !$getPassword){
            return -1; ///< bad arguments
        }
        //create query
        $sql = "SELECT" . ($getId ? " id " : " ") . 
        ($getName ? ", name " : " ") . 
        ($getPassword ? ", password " : " ") .
         "FROM " . self::TABLE_NAME . 
         ";";

        $stmt = $this->conn->prepare($sql); ///<prepare statement
        //execute
        try{
           if($stmt->execute()){ ///<execute statement
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //pass data to object
                return $data;
            }
        }catch(PDOException $pdo_err){
            return 1;
        }
        return 2;
    }

    public function read_single_by_id_or_name(bool $getId=true,
                                              bool $getName=true,
                                              bool $getPassword=true,
                                              bool $useId=true){
        /*Returns a list of all users from DB
        order of arguments: id, name, password, useId
        ! useId:
            = false => return based on this user object current name
            = true  => return based on this user object current id                         
        */
        if(!$getId && !$getName && !$getPassword){
            return -1; ///< bad arguments
        }
        //create query
        $sql = "SELECT" . ($getId ? " id " : " ") . 
        ($getName ? ", name " : " ") . 
        ($getPassword ? ", password " : " ") .
         "FROM " . self::TABLE_NAME . 
         " WHERE ". ($useId ? "id = :user_id " : "name = :user_name ").
         "LIMIT 0, 1;"; 
        
        $stmt = $this->conn->prepare($sql); ///<prepare statement
        //validate data
        //bind data
        $useId ? $stmt->bindParam(':user_id', $this->id) : $stmt->bindParam(':user_name', $this->name);
        //execute
        try{
           if($stmt->execute()){ ///<execute statement
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                //Clean data
                //pass data to object
                $getId ? $this->id=$data["id"] : null;
                $getName? $this->name=$data["name"] : null;
                $getPassword ? $this->password=$data["password"] : null;
                return 0;
            }
        }catch(PDOException $pdo_err){
            return 1;
        }
        return 2;
    }

    public function create (){
        //create the sql command
        $sql = "INSERT INTO ".self::TABLE_NAME."(name, password) VALUES (:name, :password);";
        $stmt = $this->conn->prepare($sql); ///<prepare statement

        //validate data
        //bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':password', $this->password);

        try{
            if($stmt->execute()){///<execute statement
                return 0;
            } 

        }catch(PDOException $pdo_err){
            if($stmt->errorInfo()[1] == '1062'){ // duplicate key
                return 1;
            } else {
                return 2;
            }
        }
        return 3;
    }

    public function validatePassword($plainText){
        if($this->password==null){return -1;}///< bad value

        if(password_verify($plainText,$this->password)){
            return 0;
        } else { return 1;}     
    }

    public static function staticValidatePassword($plainText, $hash){
        if(password_verify($plainText,$hash)){
            return 0;
        } else { return 1;}     
    }

    /*Setters and getters */
    public function setId(string $id){$this->id=$id;}
    public function getId(){return $this->id;}

    public function setPassword(string $password){$this->password=password_hash($password, PASSWORD_BCRYPT);}
    public function getPassword(){return $this->password;}

    public function setName(string $name){$this->name=$name;}


    
    /*Static methods */
    public static function validate($payload){
        //$x = "[question1, question2]";
        //echo "string: " . strlen($x) . "\n";
        if(gettype($payload) != "string"){return 1;}///<bad type

        $len = strlen($payload);
        $payload = substr($payload,-$len+1); ///< delete first letter
        $payload = substr($payload,0,-1); ///< delete last letter
        
        $data = explode(",", $payload);

        //test every question 
        preg_match('/^\[({question:.*,type:.*,answersList:\[{.*}\]}(,)*){1,}\]$/mi',$payload);
    }


}