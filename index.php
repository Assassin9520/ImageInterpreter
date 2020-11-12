<?php

//phpinfo(); die;

ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);



//get directory name for this file(index.php)
$nd = dirname(__FILE__);//name directory(nd)
//echo $nd;



//include Helper files
include($nd . "/Helper/helper_functions.php");

//include Core files
include($nd . "/Core/Config.php");
include($nd . '/Core/Lexer.php');
include($nd . '/Core/Parser.php');

include($nd . "/Core/SymbolTable.php");
include($nd . "/Core/Interpretor.php");
include($nd . "/Core/Error.php");
include($nd . "/Core/App.php");

use \Core\Interpretor;
use \Core\Error;
use \Core\App;


//Change Maximum execution time of 30 seconds exceeded time in php
//To support large images(code that takes more than 30 seconds to execute)
//EXPERIMENTAL FOR NOW
//ini_set('max_execution_time', 300); //300 seconds = 5 minutes



//main app gateway
if(isset($_POST['submit'])){//if form was submitted

	//Perform some sanitization(curata folderul de poze , curata fisierul text)
    //clean images folder
    helper_clean_images_folder();


    //start the application and away we go
    $app = new App();
    $interpret_result = $app->run( $_POST['text'] );
    //or something like $result = App::run($_POST['text']);	

    // if ($interpret_result) {
    //     echo "Interpretarea s-a terminat cu success.";
    // }

} else if(isset($_POST['submit-check-code'])){

    $app = new App();
    $check_result = $app->checkCode( $_POST['text'] );

    if ($check_result == true) {
        //echo "Nu exista nici o eroare in scrierea codului dumneavoastra.";
        $check_code_msj = "Nu exista nici o eroare in scrierea codului dumneavoastra.";
    } else {
        //echo "Avem erori de scriere. Verificati codul.";
        //dump Errors here
        $check_code_msj = "Avem erori de scriere. Verificati codul :";
    }

}



//Erori cu $_SESSION
// if (isset($_SESSION['have_error']) && $_SESSION['have_error'] == true ) {
//     echo "have_error";
//     echo $_SESSION['source_code'];
//     print_r($_SESSION['errors']);
// } else {
//     $_SESSION = array();
// }


//ERORI CU RETURN
//ACUM MERGE SI TESTUL ASTA
//VOI MERGE PE VARIANTA ASTA CU RETURN FALSE
// if (isset($interpret_result) && $interpret_result === false) {
//     echo "we got errors";
// }


//if we have any errors return back by interpretation(whole process:(lexer,parser,interpreter))
//Daca il pun deasupra codului de interpretare ,  nu va arata nimic.NU VA ARATA NIMIC PENTRU CA INCA NU AU FOST RIDICATE ERORI!!!
// if(Error::occur()){
//     Error::dumpErrors();
// }

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Image Interpreter</title>
        
        <!-- CSS -->
        <link href="public/assets/css/style.css" type="text/css" rel="stylesheet" />
        <link href="public/assets/css/style_reset.css" type="text/css" rel="stylesheet" />
        
        <!--responsive styles -->
        <link href="public/assets/css/responsive_author.css" type="text/css" rel="stylesheet" />

        <!-- JS Scripts -->
        <script type="text/javascript" src="public/assets/js/jquery-3.2.1.min.js" > </script>
        <script type="text/javascript" src="public/assets/js/app-scripts.js" > </script>

        <?php if(Error::occur()): ?>
            <!-- if any error occured, scroll to console(bottom of page) -->
            <script type="text/javascript">
                window.onload=toBottom;
                function toBottom()
                {
                //alert("Scrolling to bottom ...");
                window.scrollTo(0, document.body.scrollHeight);
                }
            </script>
        <?php endif;?>

    </head>

    <body>

<?php
/*
//building table of methods for using in bachelors degree work
$parser_methods = get_class_methods("Core\Interpretor");

echo "<table style=\"margin:20px; border:1px solid #000; font-size:16px; padding:10px; text-align:center; \">";
echo "
    <tr><td style=\"font-size:18px; padding:10px;\"> Interpretor </td></tr>
";
echo "<tr>";
foreach ($parser_methods as $method_name ) {
    echo "<td style=\" display:block; border-top:1px solid #000;\">";
    echo $method_name . "\n";
    echo "</td>";
}

echo "</tr>";

echo "</table>";

foreach ($parser_methods as $method_name ) {
    echo $method_name . "\n";
}

die;
*/
?>


    	<div class="wrapper-app "><!-- removed clearfix from class as it generated whitespace before </body> end -->
        	<!-- full screen overlay -displays on execution of app -->
            <div class="overlay-app-executing">
                <span class="status-this">
                    <span class="images-this">
                        <img src="public/assets/css/resources/icon-settings-flat-24px.png" class="img-settings1" />
                        <img src="public/assets/css/resources/icon-settings-flat-24px.png" class="img-settings2" />
                    </span>
                    <span class="text-this">Se executa...</span>
                </span>
            </div>


            <div class="aside-body clearfix">
                <!-- THinking about if i leave this space or not -->
                <div class="extra-space-top-aside"></div>

                <div class="nav-add-instructions-aside clearfix">
                    <ul>

                        <!--onclick direct pe li adauga text in textarea
                         <li class="instr-load-image" data-instruction="&#13;&#10;// imagine = incarca &quot;cale\catre\imagine.jpg&quot; " onclick="addInstructionInTextArea(this);">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune Incarca </span>
                            </span>
                        </li>
                         -->

                        <li class="instr-load-image" data-instruction="&#13;&#10;// imagine = incarca &quot;cale\catre\imagine.jpg&quot; " title="Adauga instructiune">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de incarcare imagine in aplicatie </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <li class="instr-define-array" data-instruction="&#13;&#10;// array_filtrare = [ 0,0,0;  0,0,0;  0,0,0; ] " onclick="" title="Adauga instructiune">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune Defineste Matrice </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <li class="instr-filter-image" data-instruction="&#13;&#10;// imagine_convolutata = filtreaza imagine array_filtrare"  title="Adauga instructiune">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de filtrare a unei imagini </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <li class="instr-convert-grayscale" data-instruction="&#13;&#10;// imagine_grayscale = luminanta imagine "  title="Adauga instructiune">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de convertire imagine in nuante alb-negru </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <li class="instr-repeat" data-instruction="&#13;&#10;/*&#13;&#10; repeta 3 { &#13;&#10;&#13;&#10;} &#13;&#10;*/&#13;&#10;"  title="Adauga instructiune">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune repetare/in bucla </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <li class="instr-extract-component" data-instruction="&#13;&#10;// imagine_rosie = extrage_componenta &quot;rosu&quot; imagine "  title="Adauga instructiune">
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de extragere a unei componente(R|G|B) dintr-o imagine </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <li class="instr-combine-images" data-instruction="&#13;&#10;// imagine_combinata = combina img1 img2 img3 " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this" >
                                <span class="title-instr-this"> Adauga instructiune de combinare a 3 imagini(fiecare pe cate un canal al imaginii finala(R|G|B) ) </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                                <span class="info-notes-this">
                                    <b style="color:#52abd9">Info:</b><br/>
                                    Versiuni Alternative:<br/>
                                      Combina 0 0 img1<br/>
                                      Combina 255 123 img1<br/>
                                      Combina img1 img3 7
                                </span>
                            </span>
                        </li>

                        <li class="instr-sum-images" data-instruction="&#13;&#10;// imagine_suma = imagine_1 + imagine_2 " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de insumare imagini </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                                <span class="info-notes-this">
                                    <b style="color:#52abd9">Info:</b><br/>
                                    Versiuni Alternative:
                                    imagine_suma = imagine_1 + 58
                                </span>
                            </span>
                        </li>

                        <li class="instr-blur-image" data-instruction="&#13;&#10;// imagine_blurata = blureaza imagine " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de blurare a unei imagini </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                                <span class="info-notes-this">
                                    <b style="color:#52abd9">Info:</b><br/>
                                    Constructie:
                                    imagine_blurata = blureaza imagine
                                </span>
                            </span>
                        </li>

                        <li class="instr-binarize-image" data-instruction="&#13;&#10;// imagine_binara = binarizeaza imagine 127 " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de binarizare imagine dupa un prag </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                                <span class="info-notes-this">
                                    <b style="color:#52abd9">Info:</b><br/>
                                    Constructie:
                                    imagine_binara = binarizeaza imagine PRAG (unde PRAG =0-255)
                                </span>
                            </span>
                        </li>

                        <li class="instr-erosion-image" data-instruction="&#13;&#10;// array_erodare = [  1,1,1; 1,1,1; 1,1,1;  ]
&#13;&#10;//imagine_erodata = erodeaza imagine_binara array_erodare " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de erodare(eroziune) a unei imagini binare dupa o matrice de erodare</span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                                <span class="info-notes-this">
                                    <b style="color:#52abd9">Info:</b><br/>
                                    Constructie:
                                    imagine_erodata = erodeaza imagine_binara array_erodare (unde array erodare a fost specificat anterior)
                                </span>
                            </span>
                        </li>

                        <li class="instr-dilate-image" data-instruction="&#13;&#10;// array_dilatare = [  1,1,1; 1,1,1; 1,1,1;  ]
&#13;&#10;//imagine_dilatata = dilateaza imagine_binara array_dilatare " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de dilatare(cresterea albului) a unei imagini binare dupa o matrice de dilatare</span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                                <span class="info-notes-this">
                                    <b style="color:#52abd9">Info:</b><br/>
                                    Constructie:
                                    imagine_dilatata = dilateaza imagine_binara array_dilatare (unde array_dilatare a fost specificat anterior in program)
                                </span>
                            </span>
                        </li>

                        <li class="instr-display-image" data-instruction="&#13;&#10;// afiseaza imagine " title="Adauga instructiune" >
                            <span class="instruction-wrapper-outer-this">
                                <span class="title-instr-this"> Adauga instructiune de afisare imagine </span>
                                <button class="button-add-instr-this" onclick="addInstructionInTextArea(this.parentNode.parentNode);"> Adauga </button>
                            </span>
                        </li>

                        <!--
                        <div>Icons made by <a href="http://www.freepik.com" title="Freepik">Freepik</a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a></div>
                        -->
                    </ul>    
                </div>
            </div>


            <div class="content"><!-- had clearfix -->
            <!-- START MAIN CONTENT -->

            	<div class="notification-area-top clearfix">
                    <!-- 
                    <a href="#" class="notif-button has-animation">Vizualizeaza aplicatia fullscreen(F11)</a>
                    <a href="#" class="notif-button ">Prima data in aplicatie ? Vezi manualul</a> 
                    -->
                </div>

                <div class="section-form-data clearfix">
                	<form method="POST" action="http://localhost/imageinterpreter/" class="form-this">
                    	<textarea id="main-textarea" class="input-this on-textarea" name="text" spellcheck="false" placeholder="Inserati Codul aici..."><?php echo (isset($_POST['text']) && !empty($_POST['text'])) ? $_POST['text'] : '' ; ?></textarea>
                        
                        <div>
                        	<input class="input-submit" type="submit" name="submit" value="Ruleaza Codul">
                            <button type="submit" name="submit-check-code" class="aut-ui-button form-button have-icon icon-check"> Verifica cod </button>
                            <a href="http://localhost/imageinterpreter/downloadSource.php?code=" id="save_code_button" class="aut-ui-button have-icon icon-save" onmouseenter="refreshHref();" onclick="goToDownloadCodePage();" target="_blank"> Salveaza cod <!-- la click , va salva codul si il va arata userului pus la dispozitie pentru download--></a>
                            <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . DS . CURRENT_APP_FOLDER_NAME . DS . "pages" . DS . "help.html"  ; ?>" class="aut-ui-button have-icon icon-book" target="blank"> Ajutor <!-- La click trimite pe pagina cu comenzi sau popu despre comenzi si functionare - had icon-info1 class before--></a>
                        </div>    
                    </form>
                </div>
                
                <!-- support for child class  on-full-screen -->
                <div class="section-display-results clearfix">
                	<div class="display-results clearfix">
                    	<div class="display-roll-this clearfix">

                            <?php if(empty(Interpretor::$display_images)) : ?>
    							<div class="image-bg-this"> <span>Rezultatele se vor afisa aici </span> </div>
                            <?php else: ?>    

                                <?php foreach(Interpretor::$display_images as $image):?>
                                    <figure class="img-this">
                                        <img src="<?php echo IMAGES_SHORTHAND_DISPLAY . $image['image'];?>" alt="" />
                                        <span class="name-of-image">
                                            <?php echo $image['var_name']; ?>
                                        </span>
                                    </figure>
                                <?php endforeach; ?>    

                            <?php endif; ?>    

                        </div>
                    </div>
                    
                    <!-- button parent is section-display-results -->
                    <a href="javascript:void(0)" class="button-full-screen-this" title="Minimizeaza/Maximizeaza Fereastra"></a>
                </div>
                
                <!-- section-errors had clearfix class -->
                <div class="section-errors">
                	<h2 class="title-this <?php if(Error::occur()): ?> have-errors <?php endif;?>">
                        Consola  
                        <?php if(Error::occur()): ?> - <span>Sunt prezente erori</span> <?php endif; ?>
                    </h2>
                    
                    <!-- here comes the errors -->
                    <div class="error-this">
                    	<!-- 
                        <span class="type-info">Info1 </span>
                        <span class="type-info">Info2 </span>
                        <span class="type-error">Eroare1 </span>
                        <span class="type-warning">Atentionare</span> 
                        -->

                        <?php if (isset($interpret_result) && $interpret_result): ?>
                            <!-- is interpret_result isset , show info in console -->
                            <span class="type-info">Interpretarea s-a terminat cu success.</span>
                        <?php endif; ?>


                        <?php if (isset($check_code_msj) && !empty($check_code_msj)): ?>
                            <!-- is check_code(parse) isset , show info in console -->
                            <span class="type-info"> <?php echo $check_code_msj; ?> </span>
                        <?php endif;?>


                        <?php if(Error::occur()): ?>
                            <!-- is errors occured , display them -->
                            <?php foreach(Error::getErrors() as $error): ?>
                                <span class="type-error"> <?php echo $error; ?> </span>
                            <?php endforeach; ?>    
                        <?php endif; ?>

                    </div>
                    <!-- end here comes the errors -->
                    
                </div>
                
            <!-- END MAIN CONTENT -->    
            </div>
            
        </div>

    </body>
</html>
