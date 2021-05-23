<?php
class Test{
    /*Attributes */

    /*Methods */

    /*Setters and getters */
    
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