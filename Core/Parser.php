<?php

namespace Core;

//no need to include config.php here , it was included in interpretor.php , which already includes this file(parser.php)
//include("Config.php");




/*
	The Parser class
	This accepts a token as param in __construct
	Analizes Tokens and executes code based on some arrangements

	php ver 5.5.0
*/
class Parser
{
	/**************
	class Attributes
	***************/

	/*token attr - will host the tokens from the tokenizer*/
	public static $tokens;

	/*current index in the tokens array(current_tokens_index)*/
	public static $ti = 0;//$ti = token_index


	/*================*/
	/*   Class Methods
	/*================*/


	/*
	parse method , will resolve the main parsing
	Will Build the ABSTRACT SYNTAX TREE ( AST ) , which , therefore 
	, will be given to the interpretor to interpret

	@param Array $tokens, the $tokens array from the Tokenizer

	@return the AST(abstract syntax tree) for further interpreting
	*/
	public static function parse($tokens)
	{

		//set tokens var of this class
		static::$tokens = $tokens;

		//call main method of Our Context-Free Grammar and away we go with parsing
		return static::ExpresiePrincipala();

	}




	/*************************************************

		THE GRAMMAR

		VER 1.0 :
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza   (operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>


		VER 2.0 :
		Changes: (adaugata instructiunea repeta)
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta   (operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>


		VER 3.0 :
		Changes: (adaugata instructiunea extrage_componenta red|green|blue )
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta | ExtrageComponenta  (operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>
			ExtrageComponenta  -> <VARIABLE> <EQ> <EXTRAGE_COMPONENTA>  (<RED> | <GREEN> | <BLUE>)  <VARIABLE>  
										//unde red , green sau blue sunt tokeni de tip STRING


		VER 4.0 :
		Changes: (adaugate instructiunile luminanta(grayscale) si combina(3 imagini - cate o componenta din fiecare) )
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta | ExtrageComponenta | Luminanta | Combina  (operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>
			ExtrageComponenta  -> <VARIABLE> <EQ> <EXTRAGE_COMPONENTA>  (<RED> | <GREEN> | <BLUE>)  <VARIABLE>  
										//unde red , green sau blue sunt tokeni de tip STRING
			Luminanta          -> <VARIABLE> <EQ> <LUMINANTA> <VARIABLE>
			Combina            -> <VARIABLE> <EQ> <COMBINA> (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)


		VER 5.0 :
		Changes: (adaugata instructiunea InsumareImagini)
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta | ExtrageComponenta | Luminanta | Combina | InsumareImagini  
										//(operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>
			ExtrageComponenta  -> <VARIABLE> <EQ> <EXTRAGE_COMPONENTA>  (<RED> | <GREEN> | <BLUE>)  <VARIABLE>  
										//unde red , green sau blue sunt tokeni de tip STRING
			Luminanta          -> <VARIABLE> <EQ> <LUMINANTA> <VARIABLE>
			Combina            -> <VARIABLE> <EQ> <COMBINA> (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)
			InsumareImagini    -> <VARIABLE> <EQ> <VARIABLE> <BINARY_OPERATOR> (<VARIABLE>|<NUMBER>)
										//binary operator este plus


		VER 6.0 :
		Changes: (adaugata instructiunea Binarizeaza)
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta | ExtrageComponenta | Luminanta | Combina | InsumareImagini | Binarizeaza  
										//(operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>
			ExtrageComponenta  -> <VARIABLE> <EQ> <EXTRAGE_COMPONENTA>  (<RED> | <GREEN> | <BLUE>)  <VARIABLE>  
										//unde red , green sau blue sunt tokeni de tip STRING
			Luminanta          -> <VARIABLE> <EQ> <LUMINANTA> <VARIABLE>
			Combina            -> <VARIABLE> <EQ> <COMBINA> (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)
			InsumareImagini    -> <VARIABLE> <EQ> <VARIABLE> <BINARY_OPERATOR> (<VARIABLE>|<NUMBER>)
										//binary operator este plus
			Binarizeaza        -> <VARIABLE> <EQ> <BINARIZEAZA> <VARIABLE> <NUMBER>


		VER 7.0 :
		Changes: (adaugata instructiunea Erodeaza si Dilateaza pentru imagini binare)
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta | ExtrageComponenta | Luminanta | Combina | InsumareImagini | Binarizeaza | Erodeaza | Dilateaza 
										//(operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>
			ExtrageComponenta  -> <VARIABLE> <EQ> <EXTRAGE_COMPONENTA>  (<RED> | <GREEN> | <BLUE>)  <VARIABLE>  
										//unde red , green sau blue sunt tokeni de tip STRING
			Luminanta          -> <VARIABLE> <EQ> <LUMINANTA> <VARIABLE>
			Combina            -> <VARIABLE> <EQ> <COMBINA> (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)
			InsumareImagini    -> <VARIABLE> <EQ> <VARIABLE> <BINARY_OPERATOR> (<VARIABLE>|<NUMBER>)
										//binary operator este plus
			Binarizeaza        -> <VARIABLE> <EQ> <BINARIZEAZA> <VARIABLE> <NUMBER>
			Erodeaza           -> <VARIABLE> <EQ> <ERODEAZA> <VARIABLE> <NUMBER>
			Dilateaza          -> <VARIABLE> <EQ> <DILATEAZA> <VARIABLE> <NUMBER>


		VER 8.0 :
		Changes: (adaugata instructiunea Blureaza pe orice imagine)
		Structura gramaticii(pana acum) :
			ExpresiePrincipala -> (Instructiune) * EOF =(expresiePrincipala produce productia Instructiune de ori cate ori urmata de EndOfFile)
			Instructiune       -> Incarca | DefinesteMatrice | Filtreaza | Afiseaza | Repeta | ExtrageComponenta | Luminanta | Combina | InsumareImagini | Binarizeaza | Erodeaza | Dilateaza 
										//(operatori neterminali)
			Incarca            -> <VARIABLE> <EQ> <INCARCA> <STRING>    (operatori terminali [Tokens])
			DefinesteMatrice   -> <VARIABLE> <EQ> <ARRAY>
			Filtreaza          -> <VARIABLE> <EQ> <FILTREAZA> <VARIABLE> <VARIABLE>
			Afiseaza           -> <DISPLAY> <VARIABLE>
			Repeta             -> <REPETA_F> <NUMBER> <OPEN_BRACE> {Instructiune} <CLOSE_BRACE>
			ExtrageComponenta  -> <VARIABLE> <EQ> <EXTRAGE_COMPONENTA>  (<RED> | <GREEN> | <BLUE>)  <VARIABLE>  
										//unde red , green sau blue sunt tokeni de tip STRING
			Luminanta          -> <VARIABLE> <EQ> <LUMINANTA> <VARIABLE>
			Combina            -> <VARIABLE> <EQ> <COMBINA> (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)  (<VARIABLE>|<NUMBER>)
			InsumareImagini    -> <VARIABLE> <EQ> <VARIABLE> <BINARY_OPERATOR> (<VARIABLE>|<NUMBER>)
										//binary operator este plus
			Binarizeaza        -> <VARIABLE> <EQ> <BINARIZEAZA> <VARIABLE> <NUMBER>
			Erodeaza           -> <VARIABLE> <EQ> <ERODEAZA> <VARIABLE> <NUMBER>
			Dilateaza          -> <VARIABLE> <EQ> <DILATEAZA> <VARIABLE> <NUMBER>
			Blureaza           -> <VARIABLE> <EQ> <BLUREAZA> <VARIABLE>



	**************************************************/


	/*
	main program grammar
	*/
	public static function ExpresiePrincipala()
	{
		$ast = [];

		while ( ! static::endOfTokens() ) { //cat timp nu s-au parcurs toti tokenii din Tokens
			//Ruleaza metoda Instructiune();
			$ast[] = static::Instructiune();

			//if we have any false key in array , return false(this is FOR ERRORS-- INCEARCA PE VIITOR ALTA METODA DE AFISARE A ERORILOR)
			//Testul asta apare si in functia gramaticii Repeta , si in oricare alta instructiune nested
			// if ( is_int(helper_recursive_array_search(false, $ast)) ) {
			// 	return false;
			// }

			/*OR return false with the following test:*/

			if(Error::occur()){//if any error occured from parsing
			    return false;
			}

		}

		if (static::endOfTokens()) {//daca s-a ajuns la sfarsitul tokens-ului si os sa mai adaug && Errors::getErrors() is empty
			//echo "Parsing finished. Everything ok with your code.  <br>";
		}

		return $ast;
	}/*end of method ExpresiePrincipala*/



	/*Instructiune method of Grammar*/
	public static function Instructiune()
	{
		/*Some Testing*/
		// static::displayTokens();
		// die();


		//switch after next token
		switch (static::TokenUrmator()) {

			case 'VARIABLE':
				//if current token name is VARIABLE , we have to look deeper to know what to do
				switch (static::TokenUrmator(3)) {//switch token urmator cu 3 pozitii(0,1,2)
					case 'INCARCA_F':
						//if token is INCARCA_F , call Function incarca
						return static::Incarca();
						break;

					case 'MULTIDIMENSIONAL_ARRAY':
						//if token is this token , call this function
						return static::DefinesteMatrice();
						break;

					case 'FILTER_F':
						//if token is this token , call this function
						return static::Filtreaza();
						break;

					case T_EXTRACT_COMP_STMT:
						//if token is this token , call this function
						return static::ExtrageComponenta();
						break;

					case T_GRAYSCALE_STMT:
						//if token is this token , call this function
						return static::Luminanta();
						break;

					case T_COMBINE_STMT:
						//if token is this token , call this function
						return static::Combina();
						break;

					case T_BINARIZE_STMT:
						//if token is this token , call this function
						return static::Binarizeaza();
						break;

					case T_ERODATE_STMT:
						//if token is this token , call this function
						return static::Erodeaza();
						break;

					case T_DILATE_STMT:
						//if token is this token , call this function
						return static::Dilateaza();
						break;

					case T_BLUR_STMT:
						//if token is this token , call this function
						return static::Blureaza();
						break;

					default:
						//if none of the above tokens were met at this position , throw an error
						// Error::raise('Parse Error (linia X): eroare de sintaxa 2. Tip necunoscut de instructiune. ');
						// return false;

						//die("Eroare de sintaxa 2 ");
						break;
				}/*end switch after 3 positions*/

				switch (static::TokenUrmator(4)) {
					case 'BINARY_OPERATOR':
						return static::InsumareBinaraImagini();
						break;
					
					default:
						//Error::raise() se va pune la finalul ultimului switch din switch-ul anterior(SWITCH in SWITCH)!!!
						//Se observa ca am mutat ridicarea erorii in acest switch fata de cel de sus

						//get current line from tokens variable to set error line no.
						$current_line = static::$tokens[static::$ti]['linie'];

						//throw error
						Error::raise('Parse Error (linia ' . $current_line . '): eroare de sintaxa 2. Tip necunoscut de instructiune. ');
						return false;
						break;
				}
				break;

			case 'DISPLAY_F':
				return static::Afiseaza();
				break;	
			
			//building Repeta instruction
			case T_REPEAT_STMT:
				return static::Repeta();
				break;	
				
			default:
				//get current line from tokens variable to set error line no.
				$current_line = static::$tokens[static::$ti]['linie'];
				//set an error 
				return Error::raise('Parse Error (linia ' . $current_line . '): eroare de sintaxa 1. Tip necunoscut de instructiune.');
				//die("Eroare de sintaxa 1 ");
				//return false;
				break;
		}/*end main switch*/

	}/*end of method instructiune*/



	/*Incarca method of Grammar*/
	public static function Incarca()
	{
		$node = [];
		$node['statement_type'] = 'INCARCA_STMT';

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$node['line_no'] = $current_line;


		//get current token and assign it to a variable
		$variable_name_incarca = static::currentToken();

		//consume the tokens and assign variables for procedding next to Interpret the command
		$tok = static::EatToken('VARIABLE'); 
		$node['params']['name_of_variable']  =  $tok['token'] ;
		
		static::EatToken('EQUALS');
		static::EatToken('INCARCA_F');

		$source_name = static::currentToken();//current token now is STRING

		$tok = static::EatToken('STRING');
		$node['params']['source_string'] = $tok['token']; 

		//here , i will insert into AST this function for interpreting
		/*  //adaugaNod(Valoare , Parinte , Copii[daca sunt] , Parametrii[daca sunt] )
			$ast->adaugaNod('Incarca_Imag', 'Root', [],
							['name_of_variable' => $variable_name_incarca ,
							 'source' => $source_name
							])
		*/

		//die("Se executa functie Incarca");
		return $node;
	}



	/*DefinesteMatrice method of Grammar*/
	public static function DefinesteMatrice()
	{
		$ret = [];

		$ret['statement_type'] = "DEFMAT_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		$tok = static::EatToken('MULTIDIMENSIONAL_ARRAY');
		$ret['params']['array'] = $tok['token'];

		return $ret;
	}



	/*FiltreazaImagine method of Grammar*/
	public static function Filtreaza()
	{
		$ret = [];

		$ret['statement_type'] = "FILTER_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;

		$tok = static::EatToken('VARIABLE');
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken('FILTER_F');

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['source_img'] = $tok['token'];

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['source_var_array'] = $tok['token'];

		return $ret;
	}



	/*AfiseazaImagine in div alaturat method of Grammar*/
	public static function Afiseaza()
	{
		$ret = [];

		$ret['statement_type'] = "DISPLAY_STMT";

        //get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;

		static::EatToken('DISPLAY_F');

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['image_var_to_display'] = $tok['token'];

		return $ret;
	}



	/*Repeta method of grammar*/
	public static function Repeta()
	{
		$node =[];

		$node['statement_type'] = "REPEAT_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$node['line_no'] = $current_line;


		static::EatToken(T_REPEAT_STMT);

		$tok = static::EatToken(T_NUM);
		$node['params']['number_of_repeats'] = $tok['token'];

		static::EatToken(T_OACO);


		$node['instruction_childs'] = [];
		//cat timp token-ul urmator nu este acolada inchisa , executa instructiune()
		while ( static::TokenUrmator() != T_CACO ) {
			//add instruction node to $node['instruction_childs'] array
			$node['instruction_childs'][] = static::Instructiune();

			/*FOR ERRORS with return false , incearca alta metoda pe viitor*/
			/*Pentru fiecare instructiune nested , trebuie sa fac testul asta*/
			if ( is_int(helper_recursive_array_search(false, $node['instruction_childs'])) ) {
				return false;
			}
		}

		static::EatToken(T_CACO);

		//finally , return the node to main AST;
		return $node;
	}



	/*ExtrageComponenta method of Grammar*/
	public static function ExtrageComponenta()
	{
		$ret = [];

		$ret['statement_type'] = "EXTRACT_COMP_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken(T_EXTRACT_COMP_STMT);

		//validare culoare la interpretare
		//...
		$tok = static::EatToken('STRING');
		$ret['params']['color_component_name'] = $tok['token'];

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['source_var_img'] = $tok['token'];

		return $ret;
	}



	/*Luminanta/MonoCromare(MonoChrome_image) method of Grammar*/
	public static function Luminanta()
	{
		$ret = [];

		$ret['statement_type'] = "GRAYSCALE_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken(T_GRAYSCALE_STMT);

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['source_var_img'] = $tok['token'];

		return $ret;
	}



	/*Combina method of Grammar*/
	public static function Combina()
	{
		$ret = [];

		$ret['statement_type'] = "COMBINE_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;

		$tok = static::EatToken('VARIABLE');
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken(T_COMBINE_STMT);

		//get next token type (number or variable)
		if (static::TokenUrmator() == "VARIABLE") {
			$tok = static::EatToken('VARIABLE'); 
			$ret['params']['source_var_img_r'] = [ 'value' => $tok['token'] , 'type'=>'variable' ]; //variabila sursa a imaginii pentru componenta de pus in red a imaginii finale
			//where 'source_var_img_r' can be an image variable or a number
		} else {
			//must use else here , AND NOT else if(static::TokenUrmator() == "NUMBER") because if another token that variable or number will be used here , will say that "Tip necunoscut de instructiune" , where it must say that "Nu s-a putut consuma tokenul..."
			$tok = static::EatToken("NUMBER"); 
			$ret['params']['source_var_img_r'] = [ 'value' => intval($tok['token']) , 'type'=>'number' ];
		}


		if (static::TokenUrmator() == "VARIABLE") {
			$tok = static::EatToken('VARIABLE'); 
			//$ret['params']['source_var_img_g'] = $tok['token']; //variabila sursa a imaginii pentru componenta green
			$ret['params']['source_var_img_g'] = [ 'value' => $tok['token'] , 'type'=>'variable' ]; //variabila sursa a imaginii pentru componenta green
		} else {
			$tok = static::EatToken("NUMBER"); 
			$ret['params']['source_var_img_g'] = [ 'value' => (int)$tok['token'] , 'type'=>'number' ];
		}


		if (static::TokenUrmator() == "VARIABLE") {
			$tok = static::EatToken('VARIABLE'); 
			$ret['params']['source_var_img_b'] = [ 'value' => $tok['token'] , 'type'=>'variable' ];  //variabila sursa a imaginii pentru componenta blue
		} else {
			$tok = static::EatToken("NUMBER"); 
			$ret['params']['source_var_img_b'] = [ 'value' => (int)$tok['token'] , 'type'=>'number' ];
		}


		return $ret;
	}



	/*InsumareBinaraImagini method of Grammar
	  eg. of form: img_suma = imagine_1 + 127
	  			   img_suma = img + img_r
	  			   ...
	*/
	public static function InsumareBinaraImagini()
	{
		$ret = [];

		$ret['statement_type'] = "BIN_OP_IMAGES_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['source_var_img1'] = $tok['token'];

		static::EatToken(T_BIN_OP);

		//check what type next token is and concomitent set the tokens
		if (static::TokenUrmator() == "VARIABLE") {
			$tok = static::EatToken('VARIABLE'); 
			//concomitent setting
			$ret['params']['source_var_img2']   = $tok['token'];
			$ret['params']['source_var_img2_nr'] = NULL;
		} else { 
			//must use else here , AND NOT else if(static::TokenUrmator() == "NUMBER") because if another token that variable or number will be used here , will say that "Tip necunoscut de instructiune" , where it must say that "Nu s-a putut consuma tokenul..."
			$tok = static::EatToken("NUMBER"); 
			$ret['params']['source_var_img2'] = NULL;
			$ret['params']['source_var_img2_nr'] = $tok['token'];
		}


		return $ret;
	}



	/*Binarizeaza method of Grammar*/
	public static function Binarizeaza()
	{
		$ret = [];

		$ret['statement_type'] = "BINARIZE_STMT";

        //get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$ret['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$ret['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken(T_BINARIZE_STMT);

		$tok = static::EatToken('VARIABLE'); 
		$ret['params']['source_var_img'] = $tok['token'];

		$tok = static::EatToken('NUMBER'); 
		$ret['params']['binarize_threshold'] = (int) $tok['token'];//prag_binarizare

		return $ret;
	}



	/*ErodeazaImagine(Binara) -  method of Grammar
	  Eroziune -operatii morfologice pe imagini
	*/
	public static function Erodeaza()
	{
		$node = [];

		$node['statement_type'] = "ERODATE_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$node['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$node['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken('ERODATE_F');

		$tok = static::EatToken('VARIABLE'); 
		$node['params']['source_var_img'] = $tok['token'];

		$tok = static::EatToken('VARIABLE'); 
		$node['params']['source_var_array'] = $tok['token'];

		return $node;
	}



	/*DilateazaImagine(Binara) method of Grammar*/
	public static function Dilateaza()
	{
		$node = [];

		$node['statement_type'] = "DILATE_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$node['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$node['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken('DILATE_F');

		$tok = static::EatToken('VARIABLE'); 
		$node['params']['source_var_img'] = $tok['token'];

		$tok = static::EatToken('VARIABLE'); 
		$node['params']['source_var_array'] = $tok['token'];

		return $node;
	}



	/*BlureazaImagine method of Grammar*/
	public static function Blureaza()
	{
		$node = [];

		$node['statement_type'] = "BLUR_STMT";

		//get current line from tokens variable to set error line no. and forward to Interpeter/Execution stage
		$current_line = static::$tokens[static::$ti]['linie'];
		$node['line_no'] = $current_line;


		$tok = static::EatToken('VARIABLE');
		$node['params']['name_of_variable'] = $tok['token'];

		static::EatToken('EQUALS');

		static::EatToken('BLUR_F');

		$tok = static::EatToken('VARIABLE'); 
		$node['params']['source_var_img'] = $tok['token'];

		return $node;
	}





	/*************************
	helper functions for grammar
	**************************/

	/*
	this function preview the next token(se uita ce token urmeaza in array-ul de token-uri)

	@param Position , the position to look ahead in the tokens array , if want so (defaults to zero)

	@return mixed The token name if found(aka VARIABLE, STRING, etc..) , false if token not found

	eg. if i want to look after 2 positions , i will call static::TokenUrmator(2);
	*/
	public static function TokenUrmator($position = 1)
	{
		if(!empty(static::$tokens[static::$ti + ($position - 1)]) ){ //if token array not empty at given position
			//return token name
			return static::$tokens[static::$ti + ($position - 1)]['name'] ; 
		} else {
			return false;
		}
		
	}



	/*
	mananca(eats) - consuma token-ul current daca token-ul dat ca parametru e egal cu token-ul curent din array-ul tokens ( avanseaza index-ul token-urilor al clasei(Parser) )
	Must suppress last token in the tokens array (suppress with @),it will give a notice undefined offset in array(pentru ca nu mai gaseste urmatorul token , deaia da notice)

	@param String $token , token-ul parametru cu care se compara token-ul curent din array-ul tokens

	@return mixed , STILL CURRENT(not next) token if comparison is true , syntax error otherwise
	*/
	public static function EatToken($token)
	{
		if ( static::$tokens[static::$ti]['name'] == $token ) {//if current token name equals token param
			//increment current tokens index
			static::$ti++;
			//return STILL CURRENT token 
			return @static::$tokens[static::$ti - 1];
		} else {
			//set an error 
			//Eroare();//will put into Core class Errors an error

			//instead of dying , raise an Error and check for this error in parsing stage(where you parse every token)
			//die("Eroare Fatala. Nu s-a putut consuma token-ul.Token-ul dat ca parametru nu se potriveste cu token-ul curent. Eroare de sintaxa!!");
			$line_error = ( isset(static::$tokens[static::$ti]['linie']) ? static::$tokens[static::$ti]['linie'] : null );
			
			Error::raise("Eroare Parsare Fatala(linia " . $line_error . "). Nu s-a putut consuma token-ul.Token-ul dat ca parametru nu se potriveste cu token-ul curent. Eroare de sintaxa!!");
			return false;
		}
	}



	/*
	gets current token based on token_index

	@return currentToken based on token_index class attribute
	*/
	public static function currentToken()
	{
		return static::$tokens[static::$ti];
	}


	/*************************************************
		END The Grammar
	**************************************************/



	/***
	helper functions
	***/


	/*
	display the tokens in another method

	@return echoes the tokens as preformatted text
	*/
	public static function displayTokens()
	{
		echo "<pre>";
		print_r(static::$tokens);
		echo "</pre>";
	}


	/*
	check if class variable tokens_index (aka $ti) is at the end of the class variable $tokens

	@return boolean True if tokens_index meet the end of tokens array , False otherwise
	*/
	public static function endOfTokens()
	{
		if(static::$ti > (count(static::$tokens) -1) ) {//token_index >nr_of_tokens(0..25 => 26 tokens)
			return true;
		}

		return false;	
	}


}/*end of class Parser*/













?>