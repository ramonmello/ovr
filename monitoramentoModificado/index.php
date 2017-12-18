<?php
/**
* RELATÓRIO DE MONITORAMENTO SAEB 2017
* Página Principal
* Última atualização: 26/10/2017
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

// USUÁRIOS que acessaram o curso, realizaram todas as atividades e estão APROVADOS
$arrResult = $fnc->selectApprovedUser($courseId, $accessUser);

foreach ($arrResult as $result) {
    $approvedBF[$result['department']][] = $result['userid'];
    $approved[] = $result['userid'];
}

// USUÁRIOS que acessaram o curso, realizaram todas as atividades e estão REPROVADOS
$arrResult = $fnc->selectRepprovedUser($courseId, $accessUser);

foreach ($arrResult as $result) {
    $repprovedBF[$result['department']][] = $result['userid'];
    $repproved[] = $result['userid'];
}

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
<!-- <video width="400" controls>
  <source src="http://138.121.71.4/material_1/aula001_testeapi.mp4" type="video/mp4">
  Your browser does not support HTML5 video.
</video> --> 


<!--script para a funcionalidade do botao de busca -->
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
            "<br><input type='checkbox' name='your-group' id='combo"+i+"' />"+
            "<video width='400' controls id='comboVideo"+i+"' src="+json[i]+">"+"</video>";
          }
          document.getElementById('videos').innerHTML = aux;
       }
    };
    //formade recebimento dos dados, arquivo que contem as funções, variavel que guarda os dados digitados pelo usuario
    xhttp.open("GET", "ajaxreceiver.php?keyword="+document.getElementById('textBusca').value, true);
    xhttp.send();
  }

  function bttSubmit(){
    var videos = new Array();
    for (var i =0; ;++i){
      var box = document.getElementById('combo'+i);
      if (box != null){
        if (box.checked ){
          videos.push(document.getElementById('comboVideo'+i));
        }
      }else{
        break;
      }
    }
    for( var i = 0; i<videos.length; ++i){
      alert (videos[i].src);
    }
  }
</script>

  <!-- area de recebimento dados usuario --> 
    <div class="plugin" align="center">
      <br> <br>
      <h2><b><font color="##1E90FF"> Pesquisa: </b></h2><br> </font> 
      <input class="pesquisas" id="textBusca" maxlength="2048" name="pesquisa" title="Pesquisar" type="search">
      <br> <br> 
      <button class="btn btn-primary"  type="button" onclick="bttBusca()"> Buscar</button> 
      <!-- Botão via JS que busca resultados pela API--> 
      <button class="btn btn-primary" type="submit" onclick="bttSubmit()">Submit</button> 
      <br> <br> <br>
    </div>
  
   
  <div id="videos">
    


  </div>



<?= $OUTPUT->footer(); ?>


