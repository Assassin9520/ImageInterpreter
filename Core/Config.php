<?php

namespace Core;

/*
config file
*/

define("DS" , DIRECTORY_SEPARATOR);

/***************************
App BACKEND Configuration
***************************/

/*
directory of images to upload in 
eg. D:\Php_xampp\htdocs\ImageInterpreter\Public\images\
*/
define("IMAGES_DIRECTORY",  dirname(__DIR__) . DS . 'public' . DS . 'images' . DS);

/*
the shorthand folder where images to display in our div are located
*/
define("IMAGES_SHORTHAND_DISPLAY",  'public/images/');


/***************************
App FRONTEND Configurations
***************************/

/*
the name of the folder of the application
*/
define("CURRENT_APP_FOLDER_NAME", "imageinterpreter");  


/*show aside(sidebar)*/
//attr 

/*show execution/interpretation messages right before start of HTML
  eg. Se executa afiseaza_imagine...
  eg. Se executa repeta...

  @values Boolean true shows the messages , false hides them
*/
const SHOW_EXECUTION_MESSAGES = false;  


//si alte idei cand mai imi vin
//...


/***************************
END App FRONTEND Configs
***************************/



/*Token names constants*/
/*
	define some constants for the token names defined in Tokenizer::tokenize_input function
	This is some constants for the form of tokens to be used in parser(Instructiune() function )

	WARNING!
	Everytime you add a new token , make sure you add it's regex in TOkenizer::tokenize_input method
	Otherwise , it will not be recognized

	THIS TOKENS ARE BEING USED IN THE PARSE STAGE OF INTERPRETER
*/
const T_VAR = 'VARIABLE';
const T_NUM = 'NUMBER';
const T_STR = 'STRING';
const T_EQ = 'EQUALS';

const T_OACO = 'OPEN_BRACE';//acolada deschisa
const T_CACO = 'CLOSE_BRACE';//acolada inchisa
const T_OBRC = 'OPEN_BRACKET';//paranteza rotunda deschisa
const T_CBRC = 'CLOSE_BRACKET';//paranteza rotunda inchisa
const T_COMM = 'COMMENT'; //comentariu multilinie

const T_ARRAY = 'MULTIDIMENSIONAL_ARRAY';
const T_BIN_OP = 'BINARY_OPERATOR';//+ , - , *, /(for now it is only plus)

const T_LOAD_STMT = 'INCARCA_F';
const T_FILTER_STMT = 'FILTER_F';
const T_SHOW_STMT = 'DISPLAY_F';
const T_COMB_STMT = 'COMBINE_F';
const T_REPEAT_STMT = 'REPETA_F';
const T_EXTRACT_COMP_STMT = 'EXTRAGE_COMP_F';
const T_GRAYSCALE_STMT = 'GRAYSCALE_F';
const T_COMBINE_STMT = 'COMBINE_F';
const T_BINARIZE_STMT = 'BINARIZE_F';
const T_ERODATE_STMT = 'ERODATE_F';
const T_DILATE_STMT = 'DILATE_F';
const T_BLUR_STMT = 'BLUR_F';

?>
