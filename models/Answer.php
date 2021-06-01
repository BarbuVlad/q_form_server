<?php
declare(strict_types=1);
//use Slim\App;

class Answer{
    /*Attributes */

    const TABLE_NAME = "answers";
    private $conn;

    private $id;
    private $id_user;
    private $id_test;
    private $date;
    private $answers;
    private $results;
    /*Methods */
    function __construct($conn) {
        $this->conn =           $conn; ///< connection is mandatory at instatioation
        $this->id =             null;
        $this->id_user =        null;
        $this->id_test =        null;
        $this->date =           null;
        $this->answers =        null;
        $this->results =        null;
    }

    public function read(bool $getId=true,
                         bool $getId_user=true,
                         bool $getId_test=true,
                         bool $getDate=true,
                         bool $getAnswers=true,
                         bool $getResults=true){
        /*Returns a list of all answers from DB
        order of arguments: id, creator_id, created_date, payload; 
        all arguments must be boolean 
        */
        if(!$getId && !$getId_user && !$getId_test && !$getDate && !$getAnswers && !$getResults){
            return -1; ///< bad arguments
        }
        //create query
        $sql = "SELECT" . ($getId ? " id " : " ") . 
        ($getId_user ? ", id_user " : " ") . 
        ($getId_test ? ", id_test " : " ") . 
        ($getDate ? ", date " : " ") .
        ($getAnswers ? ", answers " : " ") .
        ($getResults ? ", results " : " ") .
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
                                      bool $getId_user=true,
                                      bool $getId_test=true,
                                      bool $getDate=true,
                                      bool $getAnswers=true,
                                      bool $getResults=true){
        /*Returns a list of all answers from DB
        order of arguments: id, creator_id, created_date, payload; 
        all arguments must be boolean 
        */
        if(!$getId && !$getId_user && !$getId_test && !$getDate && !$getAnswers && !$getResults){
            return -1; ///< bad arguments
        }
        //create query
        $sql = "SELECT" . ($getId ? " id " : " ") . 
        ($getId_user ? ", id_user " : " ") . 
        ($getId_test ? ", id_test " : " ") . 
        ($getDate ? ", date " : " ") .
        ($getAnswers ? ", answers " : " ") .
        ($getResults ? ", results " : " ") .
         "FROM " . self::TABLE_NAME . 
         " WHERE ". "id = :answer_id " .
         "LIMIT 0, 1;"; 
        
        $stmt = $this->conn->prepare($sql); ///<prepare statement
        //validate data
        //bind data
        $stmt->bindParam(':answer_id', $this->id);
        //execute
        try{
           if($stmt->execute()){ ///<execute statement
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                //Clean data
                //pass data to object
                $getId ?        $this->id =$data["id"]           : null;
                $getId_user ?   $this->id_user =$data["id_user"] : null;
                $getId_test ?   $this->id_test =$data["id_test"] : null;
                $getDate ?      $this->date =$data["date"]       : null;
                $getAnswers ?   $this->answers =$data["answers"] : null;
                $getResults ?   $this->results =$data["results"] : null;
                return 0;
            }
        }catch(PDOException $pdo_err){
            return 1;
        }
        return 2;
    }

    public function read_all_by_test_user_id(bool $getId=true,
                                            bool $getId_user=true,
                                            bool $getId_test=true,
                                            bool $getDate=true,
                                            bool $getAnswers=true,
                                            bool $getResults=true,
                                            bool $useUserId=true,
                                            bool $useTestId=true){
    /*
    *Returns a list of all answers from DB based on test id and/or user id
    * Pay attention to the arguments: $useUserId and $useTestId, true by default;
    * Those are condition arguments
    *   - if both are true            => returns all answers for that test of that user
    *   - if only $useUserId is true  => returns all answers for that user
    *   - if only $useTestId is true  => returns all answers for that test
    *
    * The other arguments are selection arguments (what attributes to return from DB for an answer) 
    */
    if(!$getId && !$getId_user && !$getId_test && !$getDate && !$getAnswers && !$getResults){
        return -1; ///< bad arguments
    }
    //create query
    $sql = "SELECT" . ($getId ? " id " : " ") . 
    ($getId_user ? ", id_user " : " ") . 
    ($getId_test ? ", id_test " : " ") . 
    ($getDate ? ", date " : " ") .
    ($getAnswers ? ", answers " : " ") .
    ($getResults ? ", results " : " ") .
     "FROM " . self::TABLE_NAME . 
     " WHERE ".
     ($useUserId && $useTestId ? "id_user=:id_user AND id_test=:id_test" : "") .
     ($useUserId && !$useTestId ? "id_user=:id_user" : "") .
     (!$useUserId && $useTestId ? "id_test=:id_test" : "") .
     ";"; 
     $stmt = $this->conn->prepare($sql); ///<prepare statement
     //validate data
     //bind data
     $useUserId ? $stmt->bindParam(':id_user', $this->id_user) : null;
     $useTestId ? $stmt->bindParam(':id_test', $this->id_test) : null;
     //execute
     try{
        if($stmt->execute()){ ///<execute statement
             $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
             //Clean data
             //pass data to object

             return $data;
         }
     }catch(PDOException $pdo_err){
         return 1;
     }
     return 2;
    }

    public function create (){
        //create the sql command
        $sql = "INSERT INTO ".self::TABLE_NAME."(id_user, id_test, date, answers) VALUES (:id_user, :id_test, CURRENT_DATE, :answers);";
        $stmt = $this->conn->prepare($sql); ///<prepare statement

        //validate data
        //bind data
        $stmt->bindParam(':id_user', $this->id_user);
        $stmt->bindParam(':id_test', $this->id_test);
        $stmt->bindParam(':answers', $this->answers);
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

    public function setId_test(string $id_test){$this->id_test=$id_test;}
    public function getId_test(){return $this->id_test;}

    public function setId_user(string $id_user){$this->id_user=$id_user;}
    public function getId_user(){return $this->id_user;}

    public function getAnswers() {return $this->answers;}
    public function setAnswers($answers) {$this->answers=json_encode($answers);}

    public function getResults(){return $this->results;}
    public function setResults($results){$this->results = json_encode($results);}

    public function getDate(){return $this->date;}
    
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


             /*
             $getId      ?   $this->id =$data["id"]           : null;
             $getId_user ?   $this->id_user =$data["id_user"] : null;
             $getId_test ?   $this->id_test =$data["id_test"] : null;
             $getDate    ?   $this->date =$data["date"]       : null;
             $getAnswers ?   $this->answers =$data["answers"] : null;
             $getResults ?   $this->results =$data["results"] : null;*/