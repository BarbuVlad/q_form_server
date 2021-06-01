<?php
declare(strict_types=1);
//use Slim\App;

class Test{
    /*Attributes */

    const TABLE_NAME = "test";
    private $conn;

    private $id;
    private $creator_id;
    private $created_date;
    private $payload;
    /*Methods */
    function __construct($conn) {
        $this->conn =           $conn; ///< connection is mandatory at instatioation
        $this->id =             null;
        $this->creator_id =     null;
        $this->created_date =   null;
        $this->payload =        null;
    }

    public function read(bool $getId=true,
                         bool $getCreator_id=true,
                         bool $getCreated_date=true,
                         bool $getPayload=true){
        /*Returns a list of all tests from DB
        order of arguments: id, creator_id, created_date, payload; 
        all arguments must be boolean 
        */
        if(!$getId && !$getCreator_id && !$getCreated_date && !$getPayload){
            return -1; ///< bad arguments
        }
        //create query
        $sql = "SELECT" . ($getId ? " id " : " ") . 
        ($getCreator_id ? ", creator_id " : " ") . 
        ($getCreated_date ? ", created_date " : " ") .
        ($getPayload ? ", payload " : " ") .
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

    public function read_single_by_id(bool $getId=true,
                                      bool $getCreator_id=true,
                                      bool $getCreated_date=true,
                                      bool $getPayload=true){
        /*Returns this test from DB based on id
        order of arguments: id, creator_id, created_date, payload; 
        all arguments must be boolean 
        */
        if(!$getId && !$getCreator_id && !$getCreated_date && !$getPayload){
            return -1; ///< bad arguments
        }
        //create query
        $sql = "SELECT" . ($getId ? " id " : " ") . 
        ($getCreator_id ? ", creator_id " : " ") . 
        ($getCreated_date ? ", created_date " : " ") .
        ($getPayload ? " payload " : " ") .
         "FROM " . self::TABLE_NAME . 
         " WHERE id = :test_id LIMIT 0, 1;";

        $stmt = $this->conn->prepare($sql); ///<prepare statement
        //validate data
        //bind data
        $stmt->bindParam(':test_id', $this->id);
        //execute
        try{
           if($stmt->execute()){ ///<execute statement
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                //Clean data
                //$getPayload ? $data["payload"] = str_replace(",\"corect\":true", "", $data["payload"]) : null;///< anti-theft measure
                //pass data to object
                $getId ? $this->id=$data["id"] : null;
                $getCreator_id ? $this->creator_id=$data["creator_id"] : null;
                $getCreated_date ? $this->created_date=$data["created_date"] : null;
                $getPayload ? $this->payload=$data["payload"] : null;
                return 0;
            }
        }catch(PDOException $pdo_err){
            return 1;
        }
        return 2;
    }

    public function create (){
        //create the sql command
        $sql = "INSERT INTO ".self::TABLE_NAME."(creator_id, created_date, payload) VALUES (:creator_id, CURRENT_DATE, :payload);";
        $stmt = $this->conn->prepare($sql); ///<prepare statement

        //validate data
        //bind data
        $stmt->bindParam(':creator_id', $this->creator_id);
        $stmt->bindParam(':payload', $this->payload);

        try{
            if($stmt->execute()){///<execute statement
                return 0;
            } 

        }catch(PDOException $pdo_err){
            return 1;
        }
        return 2;
    }

    /*Setters and getters */
    public function setId(string $id){$this->id=$id;}
    public function getId(){return $this->id;}

    public function getPayload() {return $this->payload;}
    public function setPayload($payload) {$this->payload=json_encode($payload);}

    public function getCreator_id(){return $this->creator_id;}
    public function setCreator_id($creator_id){$this->creator_id = $creator_id;}

    public function getCreated_date(){return $this->created_date;}
    
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