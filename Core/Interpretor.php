<?php

namespace Core;

/*including namespaces*/
/*included in main index.php file*/

/*using namespaces*/
use \Core\Config;
use \Core\Tokenizer;
use \Core\Parser;

//using Helper/helper_functions.php

/*
	interpretor class
*/
class Interpretor
{
	/******
	Attributes
	*******/

	//the name of the text file
	public $file;

	//the errors attribute
	public $errors = [];

	/*display images attribute for displaying images in frontend interface
	  used by foreach in main View template(index.php)
	*/
	public static $display_images = [];

	/*previously , we had declared the table_variables here . Create a new class to handle the table(SymbolTable)*/

	//the ast from parsing
	public static $AST;



	/******
	Methods
	******/

	/*
	gets the errors of this instance(class)
	*/
	public function getErrors()
	{	
		return $this->errors;
	}





	/**************************************************
		Interpretor main CORE methods
	***************************************************/

	/*
	analizes the ast given as param and dispatch(routes) to proper instructions for executing

	@param $ast the ast built by parser

	@return Boolean true if interpretations ends with success, false otherwise
	*/
	public static function Interpret($AST)
	{
		//assigning $ast param to class attribute
		static::$AST = $AST;

		//display the ast on screen (for testing)
		//static::displayAST();

		//start iterating over statements(instructions) and dispatch evry instruction to it's action(method)
		if( static::dispatchStatement() == false){
			return false;
		} else {
			return true;
		}

	}/*end Interpret method*/



	/*
		display the ast (dump)

		@return Void
	*/
	public static function displayAST()
	{
		echo "<pre>";
		print_r(static::$AST);
		echo "</pre>";
	}	



	/*
		This check what type of instruction is it passed in param and call the apropiate execution for it

		@param array $astNodes, optional, is an $AST or $instruction ,the current Node from AST to dispatch an action for it
		paramul va fi o structura de forma:
		array[
			0 =>[
				'statement_type' => TYPE_STMT,
				'params' => [
					'param1'=> 'valueOfParam1',
					...
				],
				'instruction_childs'=>[
					//another similar structure construct comes here...
				]

			]
		]

		@return mixed false if there was an error in interpretation
	*/
	public static function dispatchStatement(array $astNodes = [])
	{
		//if no another $instruction is provided as param, instead make $astNodes =  static::$AST
		//this functionality comes from nested instructions (such as repeat_STMT) and will be used there
		if (empty($astNodes)) {
			$astNodes = static::$AST;
		}

		//set to_return switching variable here to true, it will change value to false in the foreach
		$to_return = true;

		//$astNodes from parameters list
		foreach ($astNodes as $instruction) {

			$instruction_type = $instruction['statement_type'];

			//poate folosesti asa , dar nu vad de ce ai mai avea nevoie
			//static::executeStatement($instruction);

			/*
				In this block will be put more instructions to execute as soon as we add them in functionality
			*/
			if($instruction_type == "INCARCA_STMT") {
				$to_return = static::incarca_imagine($instruction['params']['name_of_variable'], $instruction['params']['source_string'], $instruction['line_no']);
			}
			else if($instruction_type == "DEFMAT_STMT"){
				$to_return = static::defineste_matrice($instruction);
			}
			else if($instruction_type == "FILTER_STMT"){
				$to_return = static::filtreaza_imagine($instruction);
			}
			else if($instruction_type == "DISPLAY_STMT"){
				$to_return = static::afiseaza_imagine($instruction);
			}
			else if($instruction_type == "REPEAT_STMT"){
				$to_return = static::repeta_instructiune($instruction);
			}
			else if($instruction_type == "EXTRACT_COMP_STMT"){//if instruction detected is Extract_component
				$to_return = static::extrage_componenta($instruction);
			}
			else if($instruction_type == "GRAYSCALE_STMT"){//if instruction detected is Luminanta
				$to_return = static::convertire_grayscale($instruction);
			}
			else if($instruction_type == "COMBINE_STMT"){//if instruction detected is Combina
				$to_return = static::combina_imagini($instruction);
			}
			else if($instruction_type == "BIN_OP_IMAGES_STMT"){//if instruction detected is Operatie_binara_imagini
				$to_return = static::operatie_binara_imagini($instruction);
			}
			else if($instruction_type == "BINARIZE_STMT"){//if instruction detected is Binarizeaza
				$to_return = static::binarizeaza_imagine($instruction);
			}
			else if($instruction_type == "ERODATE_STMT"){//if instruction detected is Erodeaza_imagine
				$to_return = static::erodeaza_imagine($instruction);
			}
			else if($instruction_type == "DILATE_STMT"){//if instruction detected is Dilateaza_imagine
				$to_return = static::dilateaza_imagine($instruction);
			}
			else if($instruction_type == "BLUR_STMT"){//if instruction detected is Dilateaza_imagine
				$to_return = static::blureaza_imagine($instruction);
			}


			if(isset($to_return) && $to_return == false){
				return false;
			}

		}

		//A TREBUIT PUS return true; LA FINAL IN TOATE FUNCTIILE DE EXECUTAT 
		//after iteration with foreach over $AST , if all instructions returned true , return true in this method
		if (isset($to_return) && $to_return === true) {
			return true;
		}

	}/*end dispatchStatement*/


	/**************************************************
		END Interpretor functions from parsing
	***************************************************/










	/**************************************************
		Interpretor functions from parsing

		Here are defined methods for Interpreter for
		direct executing
	***************************************************/


	public static function incarca_imagine($variable, $image_source, $line_no)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<br> Se executa Incarca Imagine";
		}
		

		//echo "se executa incarca";

		/*Start coding Incarca_imagine*/
		//remove Quotes("") from string image_source param
		$sanitized_image_source = trim($image_source,'"');

		@$new_image = imagecreatefromjpeg($sanitized_image_source);

		//echo $new_image;

		if ($new_image != false) {

			/*save to table variables as resource --NU FOLOSIM VARIANTA ASTA CA NU E BUNA -- sau poate e buna - de testat*/
			//static::setTableVar($variable, $new_image);
			/*or as image saved to disk*/
			$new_name_disk = helper_generate_unique_name() . '.jpeg';
			$image_disk = imagejpeg($new_image, IMAGES_DIRECTORY  . $new_name_disk );
			//static::setTableVar($variable, $new_name_disk);
			SymbolTable::set($variable , $new_name_disk, "RGB");

			//this method executed successfully
			return true;

		} else {
			//raise error
			Error::raise('Eroare (linia ' . $line_no . '): Imaginea Specificata in sir este invalida(format invalid sau sir dat gresit).');
			return false;
		}

	}/*end incarca_imagine()*/



	public static function defineste_matrice($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<br> Se executa defmat";
		}

		$variable = $instruction['params']['name_of_variable'];
		$array_string = $instruction['params']['array'];
		$line_no = $instruction['line_no'];

		/*Start coding Defmat*/
		$array_witout_paranthesses = trim(trim($array_string,"]"), '[');

		$exploded_by_semicolon = explode(";", $array_witout_paranthesses);
		array_pop($exploded_by_semicolon);//pop last elem(which is a empty elem created by explode func)

		$i=0;
		foreach($exploded_by_semicolon as $line){
			$exploded_by_comma = explode(",", $line);
			$converted_values_to_int_by_comma = array_map('floatval', $exploded_by_comma);
			$final_array[$i] = $converted_values_to_int_by_comma;
			$i++;
		}

		//obtained final array 
		//var_dump($final_array);
		//Verifica daca matricea $final_array NU e patratica si impara.
		//daca in matrice este lasata o virgula goala , la acea valoare se va pune zero.
		//eg. array_filtrare = [  0,0,; ] <- virgula fara numar dupa ea => pune zero( [0,0,0;] )
		if( !helper_checkIfMatrixSquareAndOdd($final_array) ){
			//raise error
			Error::raise('Eroare (linia '. $line_no .'): matricea data nu este patratica si nici impara. Verifica manualul pentru sintaxa unei matrici.');
			return false;
		}

		//Performing requested action (defineste o matrice), iar asta presupune salvarea array-ului in tabela de variable
		//save final array to table variables
		//static::setTableVar($variable, $final_array);
		SymbolTable::set($variable, $final_array, "ARRAY");

		//this method executed successfully
		return true;
	}



	public static function filtreaza_imagine($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<br> Se executa filtreaza_imagine";
		}

		$variable = $instruction['params']['name_of_variable'] ;
		$source_image_variable = $instruction['params']['source_img'] ;
		$source_array_variable = $instruction['params']['source_var_array'];
		$line_no = $instruction['line_no'];


		/*Start Coding Filtreaza_imagine*/
		//get Image resource(given resource or image on disk) Variable from table variables
		if( array_key_exists($source_image_variable, SymbolTable::getTable()) ){
			$table_var = SymbolTable::get($source_image_variable);
			$resource_image = $table_var['value'] ; //or directly SymbolTable::get($source_image_variable)['value']
		} else {
			//raise error
			Error::raise('Eroare (linia '.$line_no.'): variabila nedefinita data ca parametru');
			return false;
		}

		//get array for filtering from table variables
		if(array_key_exists($source_array_variable, SymbolTable::getTable())){
			$array_for_filter_function = SymbolTable::get($source_array_variable)['value'] ;
		} else {
			//raise error
			Error::raise('Eroare (linia X): variabila array nedefinita data ca parametru');
			return false;
		}


		//apply filter on image
		//php library function for now -- via imageconvolution
		//$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		//imageconvolution($res, $array_for_filter_function, 1, 127);


		//image convolution function built by me
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		$new_image_res = static::helper_imageconvolution($res, $array_for_filter_function);

		//save filtered_image(disk file) to table variables
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($variable, $new_name_disk, "RGB");

		//this method executed successfully
		return true;
	}



	public static function afiseaza_imagine($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<br> Se executa afiseaza_imagine";
		}


		$image_variable_to_display = $instruction['params']['image_var_to_display'];
		$line_no = $instruction['line_no'];

		/*Start Coding afiseaza_imagine*/
		//get var from table variables
		if( array_key_exists($image_variable_to_display, SymbolTable::getTable()) ){
			$to_display = SymbolTable::get($image_variable_to_display)['value'] ;//don't forget it that get method returns an array
		} else {
			//raise error
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.')(sau instructiunea nr. '.$line_no.'): variabila nedefinita data ca parametru');
			return false;
		}

		//check if what is given inside variable is a string(test if it is an image on disk)
		if(is_string($to_display)){
			if (@exif_imagetype(IMAGES_DIRECTORY . $to_display) == IMAGETYPE_JPEG) {

				//PREVIOUS VERSION
				//populate display_images array 
				//maybe do in future: static::addDisplayImageInFront('string');
				//static::$display_images[] = $to_display;

				//append into $display_images array the image and name of variable from our app
				static::$display_images[] = [
					'image'     => $to_display,
					'var_name'  => $image_variable_to_display
	 			]; 

			} else {
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.')(sau instructiunea nr. '.$line_no.'): variabila specificata NU este imagine sau NU este imagine de tipul jpeg');
				return false;
			}
		}
		else if(is_resource($to_display)){
			//save image  resource on disk and store it in display_images
			$new_name_disk = helper_generate_unique_name() . '.jpeg';
			$image_disk = imagejpeg($to_display , IMAGES_DIRECTORY  . $new_name_disk );
			static::$display_images[] = $new_name_disk;
		}
		else {
			Error::raise('Eroare Interpretare/Executare (linia X)(sau instructiunea nr. X): variabila specificata ca parametru NU poate fi afisata. Trebuie sa fie de tip imagine(jpeg) sau resursa(jpeg). Verificati daca exista variabila');
			return false;
		}


		//static::dumpTableVariables('dump');

		//this method executed successfully
		return true;
	}



	/*
	repeta instructiune
	@param $Instruction the whole node of repeat instruction
	*/
	public static function repeta_instructiune($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<br> Se executa instructiunea repeta ";
		}


		//define some variables to use local
		$nr_or_repeats = $instruction['params']['number_of_repeats'];
		$instruction_childs = $instruction['instruction_childs'];
		$line_no = $instruction['line_no'];


		//start executing this instruction
		if(!empty($instruction_childs)){
			//start loop
			$i = 0;
			while ($i < $nr_or_repeats) {
				/*executing inner instructions*/
				//echo "<br> $i   {se executa instructiuni inner..}";
				/*recursive call for nested instructions*/
				static::dispatchStatement($instruction_childs);

				//incrementing $i in current "repeta_instructiune" instruction
				$i++;
			}

		}

		//this method executed successfully
		return true;
	}/*end repeta_instructiune*/



	/*
	extrage_componenta instructiune de executat/interpretat
	*/
	public static function extrage_componenta($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa extrage_componenta";
		}


		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable     = $instruction['params']['name_of_variable'];
		$color_component_name = $instruction['params']['color_component_name'];
		$source_var_img       = $instruction['params']['source_var_img'];
		$line_no = $instruction['line_no'];

		//start executing this instruction

		//sanitize the string(remove double quotes "" from the string) - using a helper function
		$color_component = helper_sanitizeString($color_component_name);

		//get variable value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img)['value'];

		//Raise Errors Area
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu este imagine.');
			return false;
		}
		//raise error if component not in one of the following
		if ( ($color_component != "rosu")  && ($color_component != "verde") && ($color_component != "albastru") ) {
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.')(sau instructiunea nr. '.$line_no.'): Se asteapta una din componentele RGB. S-a dat ' . $color_component_name . ' ca paramentru.');
			return false;
		}


		//get variable type to set in SymbolTable
		$var_type = "R"; //suppose it is red
		switch ($color_component) {
			case 'rosu':      $var_type = "R";  break;
			case 'verde':     $var_type = "G";  break;
			case 'albastru':  $var_type = "B";  break;
			
			default:
				die("Niciun cod de culoare gasit in extrage_componenta");
				break;
		}


		//create new resource from variable value and perform execution of extract
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		$new_image_res = static::helper_extractComponentRGB($res, $color_component);


		//will result a Height X Width X 1(red sau g sau b ) care se va stoca in tabelul de variabile/tabela de simboluri
		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, $var_type);

		//static::dumpTableVariables();
		//SymbolTable::dumpTable();

		//this method executed successfully
		return true;
	}



	/*
	luminanta_imagine convertire_grayscale instructiune de executat/interpretat
	*/
	public static function convertire_grayscale($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa luminanta";
		}


		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable     = $instruction['params']['name_of_variable'];
		$source_var_img       = $instruction['params']['source_var_img'];
		$line_no = $instruction['line_no'];


		//start executing this instruction
		//getting vars from SymbolTable
		//get variable source value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img)['value'];

		//Raise Errors Area
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu este imagine.');
			return false;
		}


		//create new resource from variable value and perform execution of required action
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		//used function delivered by php (built-in)
		//sau cu functie constuita de mine: $new_image_res = static::helper_convertToGrayscale($res);
		imagefilter($res, IMG_FILTER_GRAYSCALE);
		$new_image_res = $res;

		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, "GRAYSCALE");

		//free up memory from created images and exiting execution of this method
		imagedestroy($res);

		//SymbolTable::dumpTable();
		//die;

		//this method executed successfully
		return true;
	}



	/*
	combina_imagini instructiune de executat/interpretat
	*/
	public static function combina_imagini($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa combina_imagini";
		}

		/*
		INFO:
		THis function use variable types(for image type detection) from SymbolTable 
		for building another image from that known type
		*/

		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable       = $instruction['params']['name_of_variable'];
		$source_var_img_r       = $instruction['params']['source_var_img_r']['value'];//source var for final red component(either given as string or number)
		$source_var_img_g       = $instruction['params']['source_var_img_g']['value'];//source variable for final green component
		$source_var_img_b       = $instruction['params']['source_var_img_b']['value'];//source variable for final blue component
		

		//start executing this instruction
		//getting type of source_var_images..(number or variables).. this could have been done in the Parsing stage via an array return with value and type of data
		//var_dump($source_var_img_r);
		//var_dump($type_img_r);
		$type_img_r = $instruction['params']['source_var_img_r']['type'];// variable or number//this got nothing to do with SymbolTable
		$type_img_g = $instruction['params']['source_var_img_g']['type'];
		$type_img_b = $instruction['params']['source_var_img_b']['type'];

		$line_no = $instruction['line_no'];


		//Raise Errors Area
		if ( $type_img_r == "variable" ) {//check if first param is variable
			$resource_image_r = SymbolTable::get($source_var_img_r)['value'];
			$resource_image_type_r = SymbolTable::get($source_var_img_r)['type'];//get type of var from SymbolTable

			if ($resource_image_r == NULL) {//check if what is given as param exist in table variables
				//test against null because i append ['value'] whereas the SymbolTable::get($source_var_img_r) returned false
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru RED(1) NU a fost definita.');
				return false;
			}
			if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image_r ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru RED(1) nu este imagine.');
				return false;
			}
			if ( ($resource_image_type_r != "R") && ($resource_image_type_r != "G") && ($resource_image_type_r != "B") && ($resource_image_type_r != "GRAYSCALE") ) {
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru RED(1) trebuie sa fie de tip R,G,B, sau GRAYSCALE');
				return false;
			}
		}

		if ( $type_img_g == "variable" ) {//check if second param is variable
			$resource_image_g = SymbolTable::get($source_var_img_g)['value'];
			$resource_image_type_g = SymbolTable::get($source_var_img_g)['type'];//get type of var from SymbolTable

			if ($resource_image_g == NULL) {//check if what is given as param exist in table variables
				//test against null because i append ['value'] whereas the SymbolTable::get($source_var_img_r) returned false
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru GREEN(2) NU a fost definita.');
				return false;
			}
			if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image_g ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru GREEN(2) nu este imagine.');
				return false;
			}
			if ( ($resource_image_type_g != "R") && ($resource_image_type_g != "G") && ($resource_image_type_g != "B") && ($resource_image_type_g != "GRAYSCALE") ) {
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru GREEN(2) trebuie sa fie de tip R,G,B, sau GRAYSCALE');
				return false;
			}

		}

		if ( $type_img_b == "variable" ) {//check if third param is variable
			$resource_image_b = SymbolTable::get($source_var_img_b)['value'];
			$resource_image_type_b = SymbolTable::get($source_var_img_b)['type'];//get type of var from SymbolTable

			if ($resource_image_b == NULL) {//check if what is given as param exist in table variables
				//test against null because i append ['value'] whereas the SymbolTable::get($source_var_img_r) returned false
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru BLUE(3) NU a fost definita.');
				return false;
			}
			if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image_b ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru BLUE(3) nu este imagine.');
				return false;
			}
			if ( ($resource_image_type_b != "R") && ($resource_image_type_b != "G") && ($resource_image_type_b != "B") && ($resource_image_type_b != "GRAYSCALE") ) {
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru BLUE(3) trebuie sa fie de tip R,G,B, sau GRAYSCALE');
				return false;
			}
		}

		if( $type_img_r == "number" && $type_img_g =="number" && $type_img_b =="number" ){ //if all parameters are numbers , raise error
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Toti parametrii dati sunt numere. Cel putin un parametru trebuie sa fie imagine');
			return false;
		}
		//END Raise Errors Area



		//create new RESOURCES OR NUMBERS from variable value and perform execution of required action
		//getting vars from SymbolTable

		//obtain param_red of method helper_combineImages
		if($type_img_r == "variable"){
			$resource_image = SymbolTable::get($source_var_img_r)['value'];
			$param_red = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		} else {
			//shorthand if conditions
			//will set autmatically $param_red to max 255 or min 0 se pixel value don't exceed this values(unexpected behaviour)
			$param_red = ($source_var_img_r > 255 ? 255 : ($source_var_img_r < 0 ? 0 : $source_var_img_r));
		}

		//obtain param_green
		if($type_img_g == "variable"){
			$resource_image = SymbolTable::get($source_var_img_g)['value'];
			$param_green = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		} else {
			$param_green = ($source_var_img_g > 255 ? 255 : ($source_var_img_g < 0 ? 0 : $source_var_img_g));
		}

		//obtain param_blue
		if($type_img_b == "variable"){
			$resource_image = SymbolTable::get($source_var_img_b)['value'];
			$param_blue = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		} else {
			$param_blue = ($source_var_img_b > 255 ? 255 : ($source_var_img_b < 0 ? 0 : $source_var_img_b));
		}
		
		//create new final resource via helper_combineImages built by me
		//return new resource if no errors , error message otherwise in here
		$new_image_res = static::helper_combineImages(
			[ 
				$param_red , 
				$param_green, 
				$param_blue
			] , 
			[
				SymbolTable::get($source_var_img_r)['type'] ,
				SymbolTable::get($source_var_img_g)['type'] ,
				SymbolTable::get($source_var_img_b)['type'] ,
			]);


		//Raise last error in this execution method
		if ( is_string($new_image_res) ) {//this means that combineImages function returned an error message instead of a resource
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): ' . $new_image_res );
			return false;
		}


		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, "RGB");

		//SymbolTable::dumpTable();
		//die;

		//this method executed successfully
		return true;
	}



	/*
	operatie_binara_imagini(suma de imagini) instructiune de executat/interpretat
	*/
	public static function operatie_binara_imagini($instruction) 
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa insumareImagini";
		}


		/*
		INFO:
		THis function use variable types(for image type detection) from SymbolTable 
		for building another image from that known type
		*/

		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable      = $instruction['params']['name_of_variable'];
		$source_var_img1       = $instruction['params']['source_var_img1'];
		$source_var_img2       = $instruction['params']['source_var_img2'];
		$source_var_img2_nr    = $instruction['params']['source_var_img2_nr'];
		
		$line_no = $instruction['line_no'];

		//start executing this instruction
		//getting vars from SymbolTable
		//get variable source value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img1)['value'];

		//Raise Errors Area
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img 1 is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca primul parametru nu este imagine.');
			return false;
		}
		//validate second images 
		if ($source_var_img2 != NULL) {//if variable supplied as second parameter 
			$resource_image2 = SymbolTable::get($source_var_img2)['value'];
			if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image2 ) == IMAGETYPE_JPEG ) { //check if source img 2 is actually an image
				Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca al doilea parametru nu este imagine.');
				return false;
			}
		}
		//END Raise Errors Area


		//get $param_res2 and type for each params to use in helper_binaryOperationImages
		//get $param_res2
		if ($source_var_img2 != NULL) {
			$resource_image2 = SymbolTable::get($source_var_img2)['value'];
			$param_res2 = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image2);
		} else {
			$param_res2 = (int)$source_var_img2_nr;
		}

		//get type of each params
		$type_param1 = SymbolTable::get($source_var_img1)['type'];
		$type_param2 = ($source_var_img2 != NULL ? SymbolTable::get($source_var_img2)['type'] : NULL ) ;//return null for number

		//create new resource from variable value and perform execution of required action
		//$res1 is always an image, not changing(i mean not become or variable or number like $param2 in instruction)
		$res1 = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		$new_image_res = static::helper_binaryOperationImages(
			[
				$res1,
				$param_res2,
			],
			[
				$type_param1,
				$type_param2,
			]);


		if (is_string($new_image_res)) {//if static::helper_binaryOperationImages returned a string , we got error
			Error::Raise("Eroare Interpretare/Executare (linia ".$line_no."): " . $new_image_res);
			return false;
		}
		//check if type of param 1 is BINARY_IMAGE and make $type_param1 RGB to return- cannot retrun Binary_Image type because it isn't 
		if($type_param1 == "BINARY_IMAGE"){
			$type_param1 = "RGB";
		}


		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, $type_param1);//always set type of var to type_param1 for this instruction

		//free up memory from created images and exiting execution of this method
		imagedestroy($res1);
		if (is_resource($param_res2)) { imagedestroy($param_res2); }

		//SymbolTable::dumpTable();
		//die;

		//this method executed successfully
		return true;
	}



	/*
	binarizeaza_imagine instructiune de executat/interpretat
	*/
	public static function binarizeaza_imagine($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa binarizeaza_imagine";
		}


		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable     = $instruction['params']['name_of_variable'];
		$source_var_img       = $instruction['params']['source_var_img'];
		$binarize_threshold   = $instruction['params']['binarize_threshold'];
		$line_no = $instruction['line_no'];

		//start executing this instruction

		//get variable value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img)['value'];

		//Raise Errors Area
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu este imagine.');
			return false;
		}
		//raise error if threshold(prag) is greater than 255 or smaller than 0
		if ( $binarize_threshold < 0 || $binarize_threshold > 255 ) {
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.')(sau instructiunea nr. '.$line_no.'): Pragul de binarizare e mai mic ca 0 sau mai mare ca 255');
			return false;
		}


		//create new resource from variable value and perform execution of extract
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		$new_image_res = static::helper_binarizeImage($res, $binarize_threshold);

		//will result a BINARY_IMAGE img type
		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, "BINARY_IMAGE");

		//static::dumpTableVariables();
		//this method executed successfully
		return true;
	}



	/*
	erodeaza_imagine instructiune de executat/interpretat
	*/
	public static function erodeaza_imagine($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa erodeaza_imagine";
		}


		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable     = $instruction['params']['name_of_variable'];
		$source_var_img       = $instruction['params']['source_var_img'];
		$source_var_array     = $instruction['params']['source_var_array'];
		$line_no = $instruction['line_no'];


		//start executing this instruction

		//get variable value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img)['value'];
		$source_array = SymbolTable::get($source_var_array)['value'];

		//Raise Errors Area
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu este imagine.');
			return false;
		}
		if ( SymbolTable::get($source_var_img)['type'] != "BINARY_IMAGE" ) {//check if image given as param is an img of type BINARY_IMAGE - REQUIRED
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila imagine data ca parametru trebuie sa fie imagine Binara (DOAR in nuante de alb/negru).');
			return false;
		}
		//check if 2nd param array is actually an array
		if (!is_array($source_array)) {
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila 2 data ca parametru nu este array.');
			return false;
		}
		//check if param array is square and odd
		//AutoChecked by defineste_matrice method..

		if (  helper_arrayContainsOtherValuesThan([0,1], $source_array)  ) {//check if param array contains only values of 0(zero) and 1(one)
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila 2 data ca parametru (matricea) trebuie sa contina doar valori de 0(zero) si 1(unu). S-au dat si alte valori in matrice.');
			return false;
		}

		//END RAISE ERRORS


		//create new resource from variable value and perform execution of extract
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		$new_image_res = static::helper_erodeImage($res, $source_array);

		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, "BINARY_IMAGE");

		//static::dumpTableVariables();

		//this method executed successfully
		return true;
	}



	/*
	dilateaza_imagine instructiune de executat/interpretat
	*/
	public static function dilateaza_imagine($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa dilateaza_imagine";
		}


		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable     = $instruction['params']['name_of_variable'];
		$source_var_img       = $instruction['params']['source_var_img'];
		$source_var_array     = $instruction['params']['source_var_array'];
		$line_no = $instruction['line_no'];


		//start executing this instruction

		//get variable value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img)['value'];
		$source_array = SymbolTable::get($source_var_array)['value'];

		//Raise Errors Area
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu este imagine.');
			return false;
		}
		if ( SymbolTable::get($source_var_img)['type'] != "BINARY_IMAGE" ) {//check if image given as param is an img of type BINARY_IMAGE - REQUIRED
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila imagine data ca parametru trebuie sa fie imagine Binara (DOAR in nuante de alb/negru).');
			return false;
		}
		//check if 2nd param array is actually an array
		if (!is_array($source_array)) {
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila 2 data ca parametru nu este array.');
			return false;
		}
		//check if param array is square and odd
		//AutoChecked by defineste_matrice method..

		//merge $source_array into one array to search in it if exists other values than 0 or 1
		//$merged = array_unique(call_user_func_array('array_merge', $source_array));
		//mai trebuie incercat aici (de ex , valorile 0.1 , o.2 tot le ia - trebuia sa dea eroare)
		//fac un helper arrayContainsOtherValueThan($value, $array) si fac cu foreach pe array daca $value != $array[$i] return true 
		/*if (  max($merged) >1 || min($merged) < 0  ) {//check if param array contains only values of 0(zero) and 1(one)
			Error::raise('Eroare Interpretare/Executare (linia X): Variabila 2 data ca parametru (matricea) trebuie sa contina doar valori de 0(zero) si 1(unu). S-au dat si alte valori in matrice.');
			return false;
		}*/

		if (  helper_arrayContainsOtherValuesThan([0,1], $source_array)  ) {//check if param array contains only values of 0(zero) and 1(one)
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila 2 data ca parametru (matricea) trebuie sa contina doar valori de 0(zero) si 1(unu). S-au dat si alte valori in matrice.');
			return false;
		}


		//END RAISE ERRORS


		//create new resource from variable value and perform execution of extract
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		$new_image_res = static::helper_dilateImage($res, $source_array);

		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, "BINARY_IMAGE");

		//static::dumpTableVariables();

		//this method executed successfully
		return true;
	}



	/*
	blureaza_imagine instructiune de executat/interpretat
	*/
	public static function blureaza_imagine($instruction)
	{
		//show execution message only if this constant set in Config.php is set to TRUE
		if (SHOW_EXECUTION_MESSAGES) {
			echo "<BR> Se executa blurare_imagine";
		}

		
		//BUILD EXECUTION
		//define some variables to use local
		$name_of_variable     = $instruction['params']['name_of_variable'];
		$source_var_img       = $instruction['params']['source_var_img'];
		$line_no = $instruction['line_no'];


		//start executing this instruction
		//getting vars from SymbolTable
		//get variable source value from SymbolTable
		$resource_image = SymbolTable::get($source_var_img)['value'];

		//Raise Errors Area
		if ( SymbolTable::get($source_var_img) == false ) { //check if variable given as param was actually defined in our app(SymbolTable)
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu exista.');
			return false;
		}
		if ( @!exif_imagetype(IMAGES_DIRECTORY . $resource_image ) == IMAGETYPE_JPEG ) { //check if source img is actually an image
			Error::raise('Eroare Interpretare/Executare (linia '.$line_no.'): Variabila data ca parametru nu este imagine.');
			return false;
		}


		//create new resource from variable value and perform execution of required action
		$res = imagecreatefromjpeg(IMAGES_DIRECTORY . $resource_image);
		//used function delivered by php (built-in)
		//put blur function inside a loop to achieve better effect
		for ($x=1; $x<=5; $x++){
		   imagefilter($res, IMG_FILTER_GAUSSIAN_BLUR);
		}
   		$new_image_res = $res;

		//save filtered_image(disk file) to SymbolTable
		$new_name_disk = helper_generate_unique_name() . '.jpeg';
		$image_disk = imagejpeg($new_image_res , IMAGES_DIRECTORY  . $new_name_disk );
		SymbolTable::set($name_of_variable, $new_name_disk, "RGB");

		//free up memory from created images and exiting execution of this method
		imagedestroy($res);

		// SymbolTable::dumpTable();
		// die;

		//this method executed successfully
		return true;
	}








	/********************
		Helper functions 
		for Interpretor 
		functions
	*********************/
	// public static function addDisplayImageInFront($image)
	// {

	// }


	/*
	creates an image convolution based on array given as param
	used in Interpretor/filtreaza function( for now )
	building a new image resource based on an image and filter the given param $image based on an array
	U can find this function written in tests/filtreaza_dupa_array3/filter_blabla.php

	@param $image the image resource to filter
	@param $filter_array the array that applies filter to image
		(must be odd[1,3X3,5X5] and suare at the same time)

	@return Image resource . The image filtered with given filter as param
	*/
	public static function helper_imageconvolution($image , $filter_array)
	{
		//get image(given as resource) width
		$width = imagesx($image);

		//get image height
		$height = imagesy($image);

		//define half of array_filter length variable($half_array_length)
		$N = (count($filter_array) - 1) /2;


		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);


		//convolution
		for ($c=0; $c < $height ; $c++) { //for pe inaltime
			for ($l=0; $l < $width ; $l++) { //for pe lungime
				
				//$suma_pixeli = 0;

				$suma_pixeli_r = 0;
				$suma_pixeli_g = 0;
				$suma_pixeli_b = 0;

				// //start loop on filter_array
				for ($la=0; $la <= 2*$N ; $la++) { //for pe linie array(la)
					for ($ca=0; $ca <= 2*$N ; $ca++) { //for pe coloana array(ca)
						
							//$suma_pixeli += 
							//	$filter_array[$la][$ca]  *  imagecolorat($image,  (($l-$N) + ($la)) , (($c - $N)+ ($ca))  );

							//getting each image color for pixel at position for array
							$rgb = @imagecolorat($image, $l -$N +$la, $c -$N +$ca);
							if ($rgb) {
								$r = ($rgb >> 16) & 0xFF;
								$g = ($rgb >> 8) & 0xFF;
								$b = $rgb & 0xFF;
							} else {
								$r = 0;
								$g = 0;
								$b = 0;
							}
							

							//setting suma pixeli nuances(r,g,b)
							$suma_pixeli_r += $filter_array[$la][$ca] * $r;
							if($suma_pixeli_r > 255) {  $suma_pixeli_r = 255;}
							if($suma_pixeli_r < 0)   { $suma_pixeli_r = 0;}

							$suma_pixeli_g += $filter_array[$la][$ca] * $g;
							if($suma_pixeli_g > 255) { $suma_pixeli_g = 255;}
							if($suma_pixeli_g < 0)   { $suma_pixeli_g = 0;}

							$suma_pixeli_b += $filter_array[$la][$ca] * $b;
							if($suma_pixeli_b > 255) { $suma_pixeli_b = 255;}
							if($suma_pixeli_b < 0)   { $suma_pixeli_b = 0;}


					}
				}

				//set current pixel after looping in filter_array
				//imagesetpixel($new_image_res, $c, $l, $suma_pixeli);
				imagesetpixel($new_image_res, $l, $c, imagecolorallocate($new_image_res, $suma_pixeli_r, $suma_pixeli_g, $suma_pixeli_b));

			}
		}


		//return final convoluted resource
		return $new_image_res;
	}



	/*
		extracts a component(R or G or B) from resource image given as param and builds a new image only with that component

		@param $image The resource image to extract the component from
		@param $color_component_to_extract the color component to extract

		@return Resource a new image resource with only extracted component
	*/
	public static function helper_extractComponentRGB($image, $color_component_to_extract)
	{
		//get image(given as resource) width
		$width = imagesx($image);

		//get image height
		$height = imagesy($image);

		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);

		//processing 
		for ($y=0; $y < $height ; $y++) { //for pe inaltime
			for ($x=0; $x < $width ; $x++) { //for pe lungime
							
				//getting each image color for pixel at position for array
				$rgb = @imagecolorat($image, $x, $y);

				// $r = ($rgb >> 16) & 0xFF; //got red component as int 0-255 range value
				// $g = ($rgb >> 8) & 0xFF;  //got green component as int 0-255 range value
				// $b = $rgb & 0xFF;         //got blue component as int 0-255 range value
				

				if ( $color_component_to_extract == "rosu") {
					$r = ($rgb >> 16) & 0xFF; 
					$g = 0; //set to zero as we are extracting only red  
					$b = 0; //set to zero as we are extracting only red  
				} else if ( $color_component_to_extract == "verde" ) {
					$r = 0; 
					$g = ($rgb >> 8) & 0xFF;
					$b = 0;
				} else if ($color_component_to_extract == "albastru"){
					$r = 0; 
					$g = 0;
					$b = $rgb & 0xFF;
				} else {
					//fatal error
					die("Eroare in functia extractComponent");
				}


				//imagesetpixel($new_image_res, $x, $y, $pixelColor);
				imagesetpixel($new_image_res, $x, $y, imagecolorallocate($new_image_res, $r, $g, $b));

			}
		}

		//return final processed resource
		return $new_image_res;
	}



	/*
		combine 3 images pixels(or 2 images and a number ,..) into a single one , getting red pixel from first param image , the green one from second param image or number , and the blue one from third param image resource or number
		
		I used an array as param because i don't know what i will put as parameter(first param may be a integer or a resource , second the same thing) -- use array when u conflict with this situation

		@param Array $params_array an array of 3 keys containing :Image resources or Numbers
		@param Array $type_images an array of 3 keys containing: the type of images ("R" or "G" or "B" or "Grayscale") retrieved from SymbolTable

		@return Mixed Resource image if succesfull , error message otherwise//for now - have to think 
	*/
	public static function helper_combineImages($params_array, $type_images)
	{
		if (count($params_array) > 3) {
			die("params_array Nu trebuie sa fie mai mare de 3 chei.");
		}
		//var_dump($params_array);
		//var_dump($type_images);

		//start building this method
		//check if all resources is the same width-height and obtain final width,height
		$width  = 0;
		$height = 0;

		if ( is_resource($params_array[0]) ) {//test image on Red pixel
			$img = $params_array[0];
			$w = imagesx($img);
			$h = imagesy($img);
			if ($width !=0 && $height != 0) {
				if ($width != $w && $height != $h) {
					//return error message to be put in RaiseError ;
					return "Imaginea data ca parametru RED nu este de aceeasi dimensiune cu celelalte imagini date ca parametri";
				}
			} else {
				$width = $w;
				$height =$h;
			}
		}
		if ( is_resource($params_array[1]) ) {//test image on Green pixel
			$img = $params_array[1];
			$w = imagesx($img);
			$h = imagesy($img);
			if ($width !=0 && $height != 0) {
				if ($width != $w && $height != $h) {
					//return error message to be put in RaiseError ;
					return "Imaginea data ca parametru GREEN nu este de aceeasi dimensiune cu celelalte imagini date ca parametri";
				}
			} else {
				$width = $w;
				$height =$h;
			}
		}
		if ( is_resource($params_array[2]) ) {//test image on Blue pixel
			$img = $params_array[2];
			$w = imagesx($img);
			$h = imagesy($img);
			if ($width !=0 && $height != 0) {
				if ($width != $w && $height != $h) {
					//return error message to be put in RaiseError ;
					return "Imaginea data ca parametru BLUE nu este de aceeasi dimensiune cu celelalte imagini date ca parametri";
				}
			} else {
				$width = $w;
				$height =$h;
			}
		}

		
		//start creating final image resource to return
		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);

		//processing 
		for ($y=0; $y < $height ; $y++) { //for pe inaltime
			for ($x=0; $x < $width ; $x++) { //for pe lungime
							
				//getting each image color for pixel at position for array
				//$rgb = @imagecolorat($image, $x, $y);
				// $r = ($rgb >> 16) & 0xFF; //got red component as int 0-255 range value
				// $g = ($rgb >> 8) & 0xFF;  //got green component as int 0-255 range value
				// $b = $rgb & 0xFF;         //got blue component as int 0-255 range value
				
				//initialize the color pixels into zero
				$r = 0;
				$g = 0;
				$b = 0;

				//get red pixel to set final
				if (is_resource($params_array[0])) {//set red pixel coresponing to the value of red in image resource
					$image = $params_array[0];
					$rgb = @imagecolorat($image, $x, $y);
					if ($type_images[0] == "R") {
						$r = ($rgb >> 16) & 0xFF;
					}elseif ($type_images[0] == "G") {
						$r = ($rgb >> 8) & 0xFF;
					}elseif ($type_images[0] == "B") {
						$r = $rgb & 0xFF;
					}elseif ($type_images[0] == "GRAYSCALE") {
						$r = $rgb & 0xFF;//just choose one of the 3 pixels
					}else{
						die("Nu este voie alt tip de imagine aici(in functia helper_combineImages)");
					}
				} else { //set red pixel after integer number
					$r = $params_array[0];
				}

				//get green pixel to set final
				if (is_resource($params_array[1])) {//set red pixel coresponing to the value of red in image resource
					$image = $params_array[1];
					$rgb = @imagecolorat($image, $x, $y);
					if ($type_images[1] == "R") {
						$g = ($rgb >> 16) & 0xFF;
					}elseif ($type_images[1] == "G") {
						$g = ($rgb >> 8) & 0xFF;
					}elseif ($type_images[1] == "B") {
						$g = $rgb & 0xFF;
					}elseif ($type_images[1] == "GRAYSCALE") {
						$g = $rgb & 0xFF;//just choose one of the 3 pixels
					}else{
						die("Nu este voie alt tip de imagine aici(in functia helper_combineImages)");
					}
				} else { //set red pixel after integer number
					$g = $params_array[1];
				}

				//get blue pixel to set final
				if (is_resource($params_array[2])) {//set red pixel coresponing to the value of red in image resource
					$image = $params_array[2];
					$rgb = @imagecolorat($image, $x, $y);
					if ($type_images[2] == "R") {
						$b = ($rgb >> 16) & 0xFF;
					}elseif ($type_images[2] == "G") {
						$b = ($rgb >> 8) & 0xFF;
					}elseif ($type_images[2] == "B") {
						$b = $rgb & 0xFF;
					}elseif ($type_images[2] == "GRAYSCALE") {
						$b = $rgb & 0xFF;//just choose one of the 3 pixels
					}else{
						die("Nu este voie alt tip de imagine aici(in functia helper_combineImages)");
					}
				} else { //set red pixel after integer number
					$b = $params_array[2];
				}


				//imagesetpixel($new_image_res, $x, $y, $pixelColor);
				imagesetpixel($new_image_res, $x, $y, imagecolorallocate($new_image_res, $r, $g, $b));

			}
		}

		//return final processed resource
		return $new_image_res;

	}/*end method helper_combineImages*/



	/*
		binary Operation(Addition) for 2 images or 1 image and a number . IF we have 1 image and 1 number , to each pixel(RGB) of final image we add the number to 1st param image 
		USED IN OPERATIE_BINARA_IMAGINI
		
		I used an array as param because i don't know what i will put as parameter(first param may be a integer or a resource , second the same thing) -- use array when u conflict with this situation

		@param Array $params_array an array of 2 keys containing :Image resources or Numbers
		@param Array $type_params an array of 2 keys containing: the type of images ("R" or "G" or "B" or "Grayscale") retrieved from SymbolTable or NULL for number type

		@return Mixed Resource image if succesfull , error message otherwise(string)
	*/
	public static function helper_binaryOperationImages($params_array, $type_params)
	{
		//set local vars
		$image = $params_array[0];//image1
		$image2 = $params_array[1];//can be a resource or integer
		$type_param1 = $type_params[0];
		$type_param2 = $type_params[1];

		//get image(given as resource) width
		$width = imagesx($image);

		//get image height
		$height = imagesy($image);

		//test if image2 is the same dimensions as image1
		if (is_resource($image2)) {
			if ($width != imagesx($image2) && $height != imagesy($image2)) {
				return "Imaginile date ca parametru au dimensiuni diferite. Alegeti o dimensiune egala.";
			}
		}

		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);

		//processing 
		for ($y=0; $y < $height ; $y++) { //for pe inaltime
			for ($x=0; $x < $width ; $x++) { //for pe lungime
							
				//getting each image color for pixel at position for array
				$rgb_image1 = @imagecolorat($image, $x, $y);
				// $r = ($rgb_image1 >> 16) & 0xFF; //got red component as int 0-255 range value
				// $g = ($rgb_image1 >> 8) & 0xFF;  //got green component as int 0-255 range value
				// $b = $rgb_image1 & 0xFF;         //got blue component as int 0-255 range value
				
				$rgb_image2 = (is_resource($image2) ? @imagecolorat($image2, $x, $y) : NULL);//pixel color otherwise NULL

				//initialize final pixel values
				$r = 0;
				$g = 0;
				$b = 0;


				if (is_int($image2)) {//$image2 is a number

					if ($type_param1 == "RGB") {//if first param type is RGB
						if($type_param2 == NULL){//second param type is NULL (a number)
							$r =(($rgb_image1 >> 16) & 0xFF) + $image2;//in this case $image2 is a number
							$g =(($rgb_image1 >> 8) & 0xFF)  + $image2;//in this case $image2 is a number
							$b =($rgb_image1 & 0xFF)         + $image2;//in this case $image2 is a number
						}
					}
					if ($type_param1 == "R") {//if first param type is R
						if($type_param2 == NULL){//second param type is NULL (a number)
							$r =(($rgb_image1 >> 16) & 0xFF) + $image2;
							$g =0;
							$b =0;
						}
					}
					if ($type_param1 == "G") {//if first param type is G
						if($type_param2 == NULL){//second param type is NULL (a number)
							$r =0;
							$g =(($rgb_image1 >> 8) & 0xFF)  + $image2;
							$b =0;
						}
					}
					if ($type_param1 == "B") {//if first param type is B
						if($type_param2 == NULL){//second param type is NULL (a number)
							$r =0;
							$g =0;
							$b =($rgb_image1 & 0xFF)         + $image2;
						}
					}
					if ($type_param1 == "GRAYSCALE") {//if first param type is GRAYSCALE
						if($type_param2 == NULL){//second param type is NULL (a number)
							$r =(($rgb_image1 >> 16) & 0xFF) + $image2;
							$g =(($rgb_image1 >> 16) & 0xFF) + $image2;
							$b =(($rgb_image1 >> 16) & 0xFF) + $image2;
						}
					}
					//To add on new image types...
					//cand apar tipuri de imagini noi in SymbolTable , adauga-le in continuare aici
					if ($type_param1 == "BINARY_IMAGE") {//if first param type is BINARY_IMAGE
						if($type_param2 == NULL){//second param type is NULL (a number)
							$r =(($rgb_image1 >> 16) & 0xFF) + $image2;
							$g =(($rgb_image1 >> 16) & 0xFF) + $image2;
							$b =(($rgb_image1 >> 16) & 0xFF) + $image2;
						}
					}

				} else {//$image2 is image resource

					//daca nu e bine aici , iau tot blocul asta si il copiez pentru $type_param1 = RGB , R , G, B , Grayscale FIX asa cum e el(blocul)
					if ($type_param1 == "RGB") {//if first param type is RGB
						if($type_param2 == "R"){//second param type is "R"
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  ;//original pixel color
							$b =($rgb_image1 & 0xFF)         ;//orig pixel color
						}
						if($type_param2 == "G"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)         ;
						}
						if($type_param2 == "B"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF) + ($rgb_image2 & 0xFF) ;
						}
						if($type_param2 == "GRAYSCALE"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 16) & 0xFF);
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 >> 16) & 0xFF) ;
						}
						if($type_param2 == "RGB"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 & 0xFF)) ;
						}
						//add here new image types as they are builded into interpreter
						//...
						if($type_param2 == "BINARY_IMAGE"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 16) & 0xFF);
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 >> 16) & 0xFF) ;
						}

					}
					/*end important bloc*/

					if ($type_param1 == "R") {//if first param type is R
						if($type_param2 == "R"){//second param type is "R"
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  ;//original pixel color
							$b =($rgb_image1 & 0xFF)         ;//orig pixel color
						}
						if($type_param2 == "G"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 8) & 0xFF);//pe componenta rosie adaug verde
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         ;
						}
						if($type_param2 == "B"){
							$r =(($rgb_image1 >> 16) & 0xFF) + ($rgb_image2 & 0xFF) ;//pe componenta rosie adaug albastru
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         ;
						}
						if($type_param2 == "GRAYSCALE" || $type_param2 == "BINARY_IMAGE" ){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)   ;
							$b =($rgb_image1 & 0xFF)          ;
						}
						if($type_param2 == "RGB"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         ;
						}
					}

					if ($type_param1 == "G") {//if first param type is G
						if($type_param2 == "R"){//second param type is "R"
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 16) & 0xFF);//original pixel color
							$b =($rgb_image1 & 0xFF)         ;//orig pixel color
						}
						if($type_param2 == "G"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)   + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)         ;
						}
						if($type_param2 == "B"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  + ($rgb_image2 & 0xFF) ;
							$b =($rgb_image1 & 0xFF)         ;
						}
						if($type_param2 == "GRAYSCALE" || $type_param2 == "BINARY_IMAGE"  ){
							$r =(($rgb_image1 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)    + (($rgb_image2 >> 16) & 0xFF);
							$b =($rgb_image1 & 0xFF)          ;
						}
						if($type_param2 == "RGB"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 16) & 0xFF);
							$b =($rgb_image1 & 0xFF)         ;
						}
					}

					if ($type_param1 == "B") {//if first param type is B
						if($type_param2 == "R"){//second param type is "R"
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;//original pixel color
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 >> 16) & 0xFF);//orig pixel color
						}
						if($type_param2 == "G"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)          + (($rgb_image2 >> 8) & 0xFF);
						}
						if($type_param2 == "B"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         + ($rgb_image2 & 0xFF);
						}
						if($type_param2 == "GRAYSCALE" || $type_param2 == "BINARY_IMAGE" ){
							$r =(($rgb_image1 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)    ;
							$b =($rgb_image1 & 0xFF)          + (($rgb_image2 >> 16) & 0xFF);
						}
						if($type_param2 == "RGB"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 >> 16) & 0xFF);
						}
					}

					if ($type_param1 == "GRAYSCALE") {//if first param type is GRAYSCALE
						if($type_param2 == "R"){//second param type is "R"
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  ;//original pixel color
							$b =($rgb_image1 & 0xFF)         ;//orig pixel color
						}
						if($type_param2 == "G"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)          ;
						}
						if($type_param2 == "B"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         + ($rgb_image2 & 0xFF);
						}
						if($type_param2 == "GRAYSCALE" || $type_param2 == "BINARY_IMAGE" ){
							$r =(($rgb_image1 >> 16) & 0xFF)  + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)   + (($rgb_image2 >> 16) & 0xFF) ;
							$b =($rgb_image1 & 0xFF)          + (($rgb_image2 >> 16) & 0xFF);
						}
						if($type_param2 == "RGB"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 8) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 >> 8) & 0xFF);
						}
					}

					if ($type_param1 == "BINARY_IMAGE") {//if first param type is BINARY_IMAGE
						if($type_param2 == "R"){//second param type is "R"
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  ;//original pixel color
							$b =($rgb_image1 & 0xFF)         ;//orig pixel color
						}
						if($type_param2 == "G"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)          ;
						}
						if($type_param2 == "B"){
							$r =(($rgb_image1 >> 16) & 0xFF) ;
							$g =(($rgb_image1 >> 8) & 0xFF)  ;
							$b =($rgb_image1 & 0xFF)         + ($rgb_image2 & 0xFF);
						}
						if($type_param2 == "GRAYSCALE" || $type_param2 == "BINARY_IMAGE" ){
							$r =(($rgb_image1 >> 16) & 0xFF)  + (($rgb_image2 >> 16) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)   + (($rgb_image2 >> 16) & 0xFF) ;
							$b =($rgb_image1 & 0xFF)          + (($rgb_image2 >> 16) & 0xFF);
						}
						if($type_param2 == "RGB"){
							$r =(($rgb_image1 >> 16) & 0xFF) + (($rgb_image2 >> 8) & 0xFF);
							$g =(($rgb_image1 >> 8) & 0xFF)  + (($rgb_image2 >> 8) & 0xFF);
							$b =($rgb_image1 & 0xFF)         + (($rgb_image2 >> 8) & 0xFF);
						}
					}


				}/*END ELSE $image2 is image resource*/


				//correct if pixels exceeded max-min ranges
				if ($r > 255) { $r = 255;}
				if ($g > 255) { $g = 255;}
				if ($b > 255) { $b = 255;}
				if ($r < 0) { $r = 0;}
				if ($g < 0) { $g = 0;}
				if ($b < 0) { $b = 0;}

				//imagesetpixel($new_image_res, $x, $y, $pixelColor);
				imagesetpixel($new_image_res, $x, $y, imagecolorallocate($new_image_res, $r, $g, $b));

			}
		}

		//return final processed resource
		return $new_image_res;
	}



	/*
		binarizes an image given as param via a given threshold (if average median of pixels in current pixel is smaller than threshold , give pixel value 0(black) , otherwise 255(white))

		@param Resource $image , the image resource to binarize
		@param Int $threshold , the threshold to binarize by

		@return Resource the resource binarized image
	*/
	public static function helper_binarizeImage($image, $threshold)
	{
		//get image(given as resource) width
		$width = imagesx($image);

		//get image height
		$height = imagesy($image);

		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);

		//processing 
		for ($y=0; $y < $height ; $y++) { //for pe inaltime
			for ($x=0; $x < $width ; $x++) { //for pe lungime
							
				//getting each image color for pixel at position for array
				$rgb = @imagecolorat($image, $x, $y);

				//get individual pixel values
				$r = ($rgb >> 16) & 0xFF; //got red component as int 0-255 range value
				$g = ($rgb >> 8) & 0xFF;  //got green component as int 0-255 range value
				$b = $rgb & 0xFF;         //got blue component as int 0-255 range value
				

				//the value to binarize by is the grayscale pixel instead of average of the three pixel values(R G B)
				$grayscale_pixel = 0.29 * $r + 0.6 *$g + 0.11 *$b; 
				//(($r + $g + $b)/3)
				if ( $grayscale_pixel < $threshold ) {//if average median of pixel smaller than threshold
					$r=0;
					$g=0;
					$b=0;
				} else {
					$r=255;
					$g=255;
					$b=255;
				}

				//imagesetpixel($new_image_res, $x, $y, $pixelColor);
				imagesetpixel($new_image_res, $x, $y, imagecolorallocate($new_image_res, $r, $g, $b));
			}
		}

		//return final processed resource
		return $new_image_res;
	}



	/*
	Creates an eroded image(binary images) based on array given as param
	Building a new BINARY image resource based on an image and filter the given param $image based on an array

	For each value of the structuring element that is set to ON(1) , compare to the current pixel value array size( if structuring element is 3x3 , compare to current pixel 3x3 neighborhood) . If pixels set to ONfrom STRucturing element aligns perfectly over pixels from current pixel , this is a FIT(leave pixel as is(to on) ). other wise , it is a HIT , turn off the pixel

	About erosion:
	https://homepages.inf.ed.ac.uk/rbf/HIPR2/erode.htm

	@param $image the binary image resource to erode
	@param $filter_array the array that applies filter to image
		(must be odd[1,3X3,5X5] and square at the same time)

	@return Image resource . The image eroded with given filter as param
	*/
	public static function helper_erodeImage($image , $filter_array)
	{
		//get image(given as resource) width
		$width = imagesx($image);

		//get image height
		$height = imagesy($image);

		//define half of array_filter length variable($half_array_length)
		//half of filter_array( half of kernel)
		$N = (count($filter_array) - 1) /2;


		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);


		//erosion
		for ($c=0; $c < $height ; $c++) { //for pe inaltime
			for ($l=0; $l < $width ; $l++) { //for pe lungime
				
				//reinitialize pixel colors(to full black)
				//final values to set in final image
				$r = 0;
				$g = 0;
				$b = 0;

				//declare the array of FIT and HIT to count them
				$array_of_hit_fit = [];

				// //start loop on filter_array
				for ($la=0; $la <= 2*$N ; $la++) { //for pe linie array(la)
					for ($ca=0; $ca <= 2*$N ; $ca++) { //for pe coloana array(ca)
						

							//getting each image color for pixel at position for array
							$rgb = @imagecolorat($image, $l -$N +$la, $c -$N +$ca);
							//$r = ($rgb >> 16) & 0xFF;
							//$g = ($rgb >> 8) & 0xFF;
							//$b = $rgb & 0xFF;
							if ($rgb) {

								if ( $filter_array[$la][$ca] == 1 ) {//if pixel of kernel is ON
									//if underneath current pixel filter array is a pixel with on, then FIT
									if ( (($rgb >> 16) & 0xFF) > 127 ) {//pixel underneath is on(we compare just one component from pixel [this case RED component] , because all pixels are equal)
										//suppose that there's a FIT(entire kernel is embraced)
										$array_of_hit_fit[] = 1;
									} else {
										//there's a HIT(parts of kernel are embraced)
										$array_of_hit_fit[] = 0;
									}

								}


							} else { //pixel is on Boundary, leave pixel as it was(ON or OFF(0 or 255) )

								$rgbOI = @imagecolorat($image, $l, $c);//$rgbOriginalImage

								$r = ($rgbOI >> 16) & 0xFF;
								$g = ($rgbOI >> 8) & 0xFF;
								$b =  $rgbOI & 0xFF;
							}

							
					}
				}

				//set current pixel values
				if (!empty($array_of_hit_fit)) {
					if (in_array(0, $array_of_hit_fit)) {//we have a HIT for current array of pixels with kernel
						$r = 0;
						$g = 0;
						$b = 0; 
					} else { // we have a fit
						$r = 255;
						$g = 255;
						$b = 255;
					}
				}
				//empty the $array_of_hit_fit
				$array_of_hit_fit = [];


				//set current pixel after looping in filter_array
				//imagesetpixel($new_image_res, $c, $l, $suma_pixeli);
				imagesetpixel($new_image_res, $l, $c, imagecolorallocate($new_image_res, $r, $g, $b));

			}
		}


		//return final convoluted resource
		return $new_image_res;
	}



	/*
	Creates an dilated image(binary images)(white colored pixels are being grown-dilated-expanded) based on array given as param
	Building a new BINARY image resource based on an image and filter the given param $image based on an array

	DESCRIPTION:
	if at least one pixel in the structuring element ( that is ON ) coincides with a foreground(white) pixel in the image underneath, the the input pixel(pixelul curent din imagine) is set to foreground value(white). if all the coresponding pixels in the image are background , however,  the input pixel is left at the background value.
	GATA!!! AM FACUT-O. FUNCTIONEAZA PERFECT CA UNSA!!!

	About morfological dilation:
	https://homepages.inf.ed.ac.uk/rbf/HIPR2/dilate.htm

	@param $image the binary image resource to dilate
	@param $filter_array the array(kernel) that applies filter to image
		(must be odd[1,3X3,5X5] and square at the same time)

	@return Image resource . The image dilated with given filter as param
	*/
	public static function helper_dilateImage($image , $filter_array)
	{
		//get image(given as resource) width
		$width = imagesx($image);

		//get image height
		$height = imagesy($image);

		//define half of array_filter length variable($half_array_length)
		//half of filter_array( half of kernel)
		$N = (count($filter_array) - 1) /2;


		//create a new image resource
		$new_image_res = imagecreatetruecolor($width, $height);


		//dilation
		for ($c=0; $c < $height ; $c++) { //for pe inaltime
			for ($l=0; $l < $width ; $l++) { //for pe lungime
				

				//initialize current pixel values
				$r = 0;
				$g = 0;
				$b = 0;
				
				//declare the array of FIT and HIT to count them
				$array_coincides = [];


				//start loop on filter_array
				for ($la=0; $la <= 2*$N ; $la++) { //for pe linie array(la)
					for ($ca=0; $ca <= 2*$N ; $ca++) { //for pe coloana array(ca)
						

							//getting each image color for pixel at position for array
							$rgb = @imagecolorat($image, $l -$N +$la, $c -$N +$ca);
							//$r = ($rgb >> 16) & 0xFF;
							//$g = ($rgb >> 8) & 0xFF;
							//$b =  $rgb & 0xFF;
							if ($rgb) {

								if ( $filter_array[$la][$ca] == 1 ) {//if pixel of kernel is ON
									//if underneath current pixel filter array is a pixel with on
									if ( (($rgb >> 16) & 0xFF) > 127 ) {//pixel underneath is on(we compare just one component from pixel [this case RED component] , because all pixels are equal)

										//pixels coincides , set current pixel to white
										$array_coincides[] = 1;//set 1 for coincides

									} else {

										//the pixels don't coincides
										$array_coincides[]  = 0;

									}


								} else if ( $filter_array[$la][$ca] == 0 ) {//pixel kernel OFF
									if ( (($rgb >> 16) & 0xFF) < 127 ) {//pixel underneath OFF
										//now counting all black pixels underneath kernel
										//pixel coincides
										$array_coincides[] = 0;
									}
								}


							} else { //pixel is on Boundary, leave pixel as it was(ON or OFF(0 or 255) )
								$rgbOI = @imagecolorat($image, $l , $c);

								$r = ($rgbOI >> 16) & 0xFF;
								$g = ($rgbOI >> 8) & 0xFF;
								$b =  $rgbOI & 0xFF;
							}

							
					}
				}


				//set current pixel values
				if (!empty($array_coincides)) {
					if ( in_array(1, $array_coincides) ) {//we have a match , set current pixel to foreground color(white)
						$r = 255;
						$g = 255;
						$b = 255; 
					} else if (  (count($filter_array) * count($filter_array)) == count($array_coincides)) { //we don't have any match AND ALL PIXELS underneath kernel are BLACK
						//set current pixel to black
						$r = 0;
						$g = 0;
						$b = 0;
						//FUNCTIONEAZA PERFECT
					}
				}

				//set current pixel 
				imagesetpixel($new_image_res, $l, $c, imagecolorallocate($new_image_res, $r, $g, $b));



			}
		}


		//return final image resource
		return $new_image_res;
	}






	/********************
		END Helper functions Interpretor
	*********************/






	/**************************************************
		END Interpretor functions from parsing
	***************************************************/



}/*end class interpretor*/
