<?php
/*
  Website de busca para o serviço BAVi.
  Código baseado na página inicial de 'RELATÓRIO DE MONITORAMENTO SAEB 2017', atualização '26/10/2017'
  @author Miguel Alvim
  @version ALPHA
  @date 19/08/2018
*/

//require $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once('../../config.php');
//require_once($CFG->dirroot . '/my/lib.php');
require __DIR__ . '/ZaraFunctions.php';
// requerimento de css
require_once ('styles.css');

// VARIÁVEIS GLOBAIS
global $PAGE;
global $OUTPUT;
global $DB;


// ARRAYs PRÉ-DEFINIDOS
$arrCursos = array(
    1 => 'bavi'
    //3 => 'Sistema de Avaliação da Educação Básica 2017 - Aplicadores'
);


// VARIÁVEIS LOCAIS
$urlCourse   = '/monitoramento/index.php?id=';
$courseData  = new stdClass();
$courseId    = 2;
$banUser     = '1, 2, 3, 4, 5, 6, 7, 930, 931';

$allUser   = array();
$allUserBF = array();

$accessUser   = array();
$accessUserBF = array();

$notAccessUser   = array();
$notAccessUserBF = array();

$approved   = array();
$approvedBF = array();

$repproved   = array();
$repprovedBF = array();

$arrResult = array();


// RECEBE os dados passados via $_GET
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];
}

// SELECIONA OS dados do curso e verifica se está logado
$courseData = $DB->get_record("course", array("id" => $courseId), "*", MUST_EXIST);
require_login($courseData);


// Inicia as classes de apoio
$fnc = new ZaraFunctions();


// SELECIONA OS DADOS --------------------------------------------------------------------------------------------------

// TODOS USUários do curso
$arrResult = $fnc->selectAllUsers($courseId, $banUser);

foreach ($arrResult as $result) {
    $allUserBF[$result['department']][] = $result['userid'];
    $allUser[] = $result['userid'];
}

// USUÁRIOS que ACESSARAM o curso
$arrResult = $fnc->selectUserAccess($courseId, $banUser);

foreach ($arrResult as $result) {
    $accessUserBF[$result['department']][] = $result['userid'];
    $accessUser[] = $result['userid'];
}

// USUÁRIOS que NÃO ACESSARAM o curso
$arrResult = $fnc->selectUserNotAccess($courseId, $banUser);

foreach ($arrResult as $result) {
    $notAccessUserBF[$result['department']][] = $result['userid'];
    $notAccessUser[] = $result['userid'];
}

// // USUÁRIOS que acessaram o curso, realizaram todas as atividades e estão APROVADOS
// $arrResult = $fnc->selectApprovedUser($courseId, $accessUser);

// foreach ($arrResult as $result) {
//     $approvedBF[$result['department']][] = $result['userid'];
//     $approved[] = $result['userid'];
// }

// // USUÁRIOS que acessaram o curso, realizaram todas as atividades e estão REPROVADOS
// $arrResult = $fnc->selectRepprovedUser($courseId, $accessUser);

// foreach ($arrResult as $result) {
//     $repprovedBF[$result['department']][] = $result['userid'];
//     $repproved[] = $result['userid'];
// }

?>

<!-- Configurações para exibição da página !-->
<?php
$courseContext = context_course::instance($courseData->id);

$PAGE->set_url('/report/csv/old.php', array('id' => $courseData->id));
$PAGE->set_title('Monitoramento');
$PAGE->set_heading($courseData->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading('Vídeo Aulas');


?>
  <!--script para a funcionalidade do botao de busca e submit-->
  <script type="text/javascript">
    
      function bttBusca(){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              var json = JSON.parse(this.responseText);
              var aux =""
              // Criação de um lupe para cada comandod e busca
              for(var i=0;i <json.length; ++i){
                 aux = aux+
                "<div class='col-md-4'><input class='box' type='checkbox' name='your-group' id='combo"+i+"'/>"+
                "<video class='center' width='50%' controls id='comboVideo"+i+"' src="+json[i]+">"+"</video> <br></div>";
              }
              document.getElementById('videos').innerHTML = aux+"<div class='col-md-12'><button class='btn1 btn-primary' type='submit' onclick='bttSubmit()'>Enviar</button></div> ";
           } 
        };
        //formade recebimento dos dados, arquivo que contem as funções, variavel que guarda os dados digitados pelo usuario
        xhttp.open("GET", "ajaxreceiver.php?keyword="+document.getElementById('textBusca').value, true);
        xhttp.send();
      }

    function bttSubmit(){
      var videos = {};
      var totVideos =0;
      for (var i =0; ;++i){//Getting the videos url
        var box = document.getElementById('combo'+i);
        if (box != null){
          if (box.checked ){
            videos[totVideos]=(document.getElementById('comboVideo'+i).src);
            ++totVideos;
          }
        }else{
          break;
        }
      }
      var videosJson = JSON.stringify(videos);
      var xhttp = new XMLHttpRequest();//sending AJAX request with the urls for database insertion
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
/*            if(this.responseText == 'true'){
              alert("URLs criadas com sucesso "+this.responseText);
            }else{
              alert("Erro ao criar 1 ou mais URLs");
            }*/
            alert(this.responseText);
            //Obtendo a URL do curso a partir da url atual; mais seguro do que utilizar window.location.referrer, já que evita o problema de acessar a pagina via link (se não usarmos um link a partir de outra página, referrer não funciona)
              //usando Regex
              var expression = /^.*\/(?=blocks)/;
              var currentURL = (window.location.href); 
              var courseURL = expression.exec(currentURL); 
              if(courseURL){
                var getParams = getGETParams();
                courseURL = courseURL+"course/view.php?id="+getParams.id;
                window.location.replace(courseURL);
              }
         }
      };
      //Parametros para o POST
      var getParams = getGETParams();
        //nomes
      var names = {};
      for(var i=0;i<totVideos;++i){
        names[i]=(videos[i]).substring(
          (videos[i]).lastIndexOf('/')+1,
          (videos[i].length-4));
      }
      var namesJson = JSON.stringify(names);
      var rotName = document.getElementById('textBusca').value;

      //formade recebimento dos dados, arquivo que contem as funções, variavel que guarda os dados digitados pelo usuario
      xhttp.open("POST","ajaxsubmit.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send("cid="+getParams.id+"&urls="+videosJson+"&names="+namesJson+"&section="+getParams.section+"&rotName="+rotName);
    }

    //Particiona os valores passados por GET em um array
    function getGETParams(){
      var vals = window.location.search.substr(1);
      if(vals == null || vals == ""){
        return {};
      }else
        return getToArray(vals);
    }

    function getToArray(vals){
      var params = {};
      var nval = vals.split("&");
      for ( var i = 0; i < nval.length; ++i) {
        var tempVals = nval[i].split("=");
        params[tempVals[0]] = tempVals[1];
      }
      return params;
    }

    function enter(evento){ 
           tecla = evento.keyCode;
           if(tecla == 0)
           {
                   tecla = evento.charCode;
           }
           if(tecla == 13)
           {
            bttBusca();
           }
    }
  </script>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">

  <!-- area de recebimento dados usuario --> 
    <div class="plugin" align="center">
            <br> <br>
      <h2><b><font color="##1E90FF"> Pesquisa: </b></h2><br> </font> 
      <input class="ls-field-sm" id="textBusca" size="80" name="pesquisa" title="Pesquisar" type="search" onkeypress="enter(event);" >
      <br> <br> 
      <button class="btn btn-primary"  type="button" onclick="bttBusca()"> Buscar</button> 
      <br> <br>
    </div>
  
   
  <div id="videos">
  </div>



<?= $OUTPUT->footer(); ?>

