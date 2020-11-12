<?php
namespace Core;


/*
    The Tokenizer/Lexer Object class
    called Tokenizer before(i renamed it to Lexer)
    php ver 5.5.0
*/
class Lexer
{
    private $_patterns = array();
    private $_length = 0;
    private $_tokens = array();
    private $_delimeter = '';//no delimiter
    private $_last_error = '';
    
    public function __construct($delimeter = "#")
    {
        $this->_delimeter = $delimeter;
    }


    /** 
    * Add a regular expression to the Tokenizer
    *
    * @param string $name name of the token
    * @param string $pattern the regular expression to match
    */
    public function add($name, $pattern)
    {
        $this->_patterns[$this->_length]['name'] = $name;
        $this->_patterns[$this->_length]['regex'] = $pattern;
        $this->_length++;
    }


    /** 
    * Tokenizes a reference to an input string, 
    * removing matches from the beginning of the string
     * 
     * @param string &$input the input string to tokenize
     * @param number &$linie_curenta_token the current line of current token
     * 
     *@return boolean|string returns the matched token on success, boolean false on failure
    */
    public function tokenize(&$input, &$linie_curenta_token)
    {
        //variable for detecting if we have an invalid token
        $nr_of_errors = 0;
        

        for($i = 0; $i < $this->_length; $i++)
        {
            //if we have a hit into out array of defined patterns.SUCCESS, we found an token
            //store token into the tokens array
            if(@preg_match($this->_patterns[$i]['regex'], $input, $matches))
            {


//nowdoc syntax for COMMENT TOKEN regex(pun aici fara tab pt ca sublime text nu recunoaste sintaxa nowdoc,momentan)                
$comment = <<<'SCRIPT'
/^((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/   
SCRIPT
;
$eol_regex  = "/^(\r?\n)/";
$singleline_comment = "/^\/\/.*[\r\n]?/";


                //if current token is eol(end of line)
                if ($this->_patterns[$i]['regex'] == $eol_regex) {
                    //increment the line for token counter
                    $linie_curenta_token++;
                }


                //if current regex == COMMENT , don't add it to $this->_tokens array(omit it)
                //or current regex == EOL(end of line) , don't add it to $this->_tokens
                //or current regex == SingleLine_COMMENT , don't add it to $this->_tokens
                if ( ($this->_patterns[$i]['regex'] != $comment)  && ($this->_patterns[$i]['regex'] != $eol_regex) && ($this->_patterns[$i]['regex'] != $singleline_comment) ) {

                    $this->_tokens[] = array(
                                            'name' => $this->_patterns[$i]['name'],
                                            'token' => $matches[0],
                                            'linie' => $linie_curenta_token
                                            );

                }
                          

                //remove last found token from the $input string
                //we use preg_quote to escape any regular expression characters in the matched input

                /*added_by_me*/
                //quote regular expression characters from $matchs[0] of preg_match defined in if
                $quote_regular_expression_characters = $this->_delimeter."^".preg_quote($matches[0],  $this->_delimeter).$this->_delimeter ;
                //replace the new input with that was previously found with nothing(practically, remove the previously found string from the input string to be tokenized)
                $preg_replace_input = preg_replace($quote_regular_expression_characters,  "", $input);
                //finally , trim whitespace from final resulted new input string
                $final_trim = trim($preg_replace_input ," ");
                //new input becomes old input without the previous detected string to be tokenized
                $input = $final_trim;
                /*end_added_by_me*/

                //$input = trim( preg_replace($this->_delimeter."^".preg_quote($matches[0],  $this->_delimeter).$this->_delimeter,  "",   $input));

                if($input == ''){//test if last found $input is empty,if it is, break(ca sa nu mai apara eroare de token invalid la sfarsit , chiar daca nu avem nici un token invalid)
                    break;
                }
                

                //MERGE FUNCTIA COMBINA PERFECT , CA UNSA . 
                //INSA , ACUM TREBUIE SA MA UIT PRIN LEXER , DEOARECE , CAND AJUNGE LA UN TOKEN DE TIP NUMBER CU VALOAREA 0 , SE OPRESTE FARA A MAI CONTINUA.. DAR DACA TOKENUL NUMBER ARE ORICARE ALTA VALOARE DECAT 0 , RULAREA CONTINUA ..
                //INVESTIGHEAZA SITUATIA
                //INVESTIGAT , SOLUTIA E DATA MAI JOS
                //return the value of matches[0] as a formatted string (because , if it happens for $matches[0] to be 0 , return 0 means return false, and tokenizing ends)
                //The return value is just for displaying into the tokenize_input method some text(current token that is tokenized)
                //or , simply , return true;
                //INAINTE , era doar //return $matches[0];
                return " ".$matches[0];

            }
            elseif(preg_match($this->_patterns[$i]['regex'], $input, $matches) === false)
            {//eroare de scriere gresita a unui token
                    $this->_last_error = 'Eroare de scriere a paternului la  $_patterns['.$i.']';
                    return false;
            }
            elseif(! preg_match($this->_patterns[$i]['regex'], $input, $matches) )
            {//eroare daca ce e dat ca input nu e token valid
                    
                    $nr_of_errors++;

                    if($nr_of_errors == $this->_length)
                    {
                        //inseamna ca ce e dat ca parametru in text nu e token valid
                        //echo "Eroare Lexicala .Invalid TOKEN. Aplicatia se va opri din executie.";
                        //ADDED current line in token array (5 december 2017)
                        //Mai verifica .. poate trebuie scos -- $linie_curenta_token din stringul de mai jos
                        $this->_last_error = 
                            'Eroare Lexicala: Invalid TOKEN pe linia ' . $linie_curenta_token . '. Aplicatia se va opri din executie.
                             <br> Verificati daca:
                             <br> -S-au inchis toate comentariile
                             <br> -Toate instructiunile sunt valide.
                             <br> -Sa nu existe nici un token invalid in codul sursa.
                            ';
                    }
            }
        }
        return false;

    }/*end tokenize method*/



    /*
        get an attribute from class    
    */
    public function __get($item)
    {
        switch($item){
            case 'tokens':
                return $this->_tokens;
            case 'last_error':
                return $this->_last_error;
        }
    }



    /*
    Tokenize the input text param to on associative array of tokens($token_name , $value)

    @param $input_text The text input from front to be tokenized

    @return Mixed The tokenized Array if success , last_error String if  there is any error
    */
    public static function tokenize_input($input_text)
    {
        //tokenize $text param
        $tokenizer = new Lexer();

        //nowdoc syntax so we don't have to escape quote marks, forwardslashes or forwardreferences
//e pusa fara indentare pt ca nu merge validarea sintaxei in sublime text cu ea indentata
//a string is composed from : " urmata de orice de ori cate ori urmat de "
$strings = <<<'SCRIPT'
/^("|')(\\?.)*?\1/     
SCRIPT
;

//a comment is composed from this: /* urmata de orice sau enter (de ori cate ori) urmat de */
/*original comment*/
// $comment = <<<'SCRIPT'
// /^\/\*.*\*\//     
// SCRIPT
// ;  

/*multiline comment - don't work for now*/
// $comment = <<<'SCRIPT'
// /^\/\*(.|[\r\n])*\*\//     
// SCRIPT
// ;

/*multiline comment - THIS ONE WORKS -BY ME - FAIL TO CATCH GROUP (/ urmat de *)- ia doar / ori * */
// $comment = <<<'SCRIPT'
// /^\/\*([^\*\/]|[\r\n\t])*\*\//     
// SCRIPT
// ;

//VER 2 multiline comm: target all pana la ultimul * / intalnit in stringul sursa
//THIS FORM MATCHES all characters until it meets * /(the last * / in the source code , not next * /)
// $comment = <<<'SCRIPT'
// /^\/\*(.|[\r\n\t])*\*\//     
// SCRIPT
// ;

//source: https://blog.ostermiller.org/find-comment
$comment = <<<'SCRIPT'
/^((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/   
SCRIPT
;





        //DECLARE ALL THE TOKENS IN HERE
        //ALSO , THIS TOKENS WILL BE LOADED IN CONFIG php 

        /*Fixed function tokens*/
        // ex: nume token INCARCA_F (incarca_functie)
        //$tokenizer->add("T_EOL", "/^[\r\n]/");
        $tokenizer->add("T_EOL", "/^(\r?\n)/");

        $tokenizer->add("INCARCA_F", "/^incarca( |[\r\n])/");
        $tokenizer->add("FILTER_F", "/^filtreaza( |[\r\n]|\t)/");
        $tokenizer->add("DISPLAY_F", "/^afiseaza( |[\r\n]|\t)/");
        $tokenizer->add("REPETA_F", "/^repeta( |[\r\n]|\t)/");
        $tokenizer->add("EXTRAGE_COMP_F", "/^extrage_componenta( |[\r\n]|\t)/");
        $tokenizer->add("GRAYSCALE_F", "/^luminanta( |[\r\n]|\t)/");
        $tokenizer->add("COMBINE_F", "/^combina( |[\r\n]|\t)/");
        $tokenizer->add("BINARIZE_F", "/^binarizeaza( |[\r\n]|\t)/");
        $tokenizer->add("ERODATE_F", "/^erodeaza( |[\r\n]|\t)/");
        $tokenizer->add("DILATE_F", "/^dilateaza( |[\r\n]|\t)/");
        $tokenizer->add("BLUR_F", "/^blureaza( |[\r\n]|\t)/");

        /*General tokens*/
        $tokenizer->add("NUMBER", "/^[0-9]+/");
        $tokenizer->add("STRING", $strings);
        $tokenizer->add("VARIABLE", "/^[a-zA-Z][a-zA-Z0-9_]*/");
        $tokenizer->add("EQUALS", "/^=/");
        $tokenizer->add("OPEN_BRACE", "/^{/");
        $tokenizer->add("CLOSE_BRACE", "/^}/");
        $tokenizer->add("OPEN_BRACKET", "/^\(/");
        $tokenizer->add("CLOSE_BRACKET", "/^\)/");
        $tokenizer->add("COMMENT", $comment);
        $tokenizer->add("SINGLELINE_COMMENT", "/^\/\/.*[\r\n]?/");
        //by m
        /*$tokenizer->add("MULTIDIMENSIONAL_ARRAY", "/^\[ *( *[ ?(-?)\d, ?]* *; *,?)+ *\]/");*/ /*old code for multidim array*/
        $tokenizer->add("MULTIDIMENSIONAL_ARRAY", "/^\[( |[\r\n])*( *[ ?(-?)\d, ?]* *;( |[\r\n])*,?)+ *\]/");//now supporting drop on multiple lines
        $tokenizer->add("BINARY_OPERATOR", "/^\+/");//for now , binary_operator is plus , but can be + or - or * or /



        // //tokenize the input and echo the result
        // while($result = $tokenizer->tokenize($input_text))
        // {
        //     //daca tot inputul e valid , va afisa resultatele pana la final , in afara de ultimul token (care , mi-l elemina break-ul din primul if din for Din Tokeniser->tokenise)
        //     //echo $result . "<br>";
        // }
        // // show last error
        // echo $tokenizer->last_error;

        // //show tokens
        // echo "<pre>";
        // print_r($tokenizer->tokens);
        // echo "</pre>";

        $linie_curenta_token = 1;
        // tokenize the input and echo the result
        while($result = $tokenizer->tokenize($input_text, $linie_curenta_token))
        {//while there is something into $result, continue the loop(continue to tokenize)
            
            // echo "<pre>";
            // print_r($result);
            // echo "</pre>";
        }

        //return
        if($tokenizer->last_error) { //if we have any errors
            //return the error
            return $tokenizer->last_error;
        } else {
            //return tokens array
            return $tokenizer->tokens;
        }



    }

} /*end of class Tokenizer*/





