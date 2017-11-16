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
<script type="text/javascript">
  function bttBusca(){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          alert(this.responseText);
          var aux ="";
          for(var i=0;i <1; ++i){
             aux = aux+
            "<video width='400' controls>"+
            "<source src="+this.responseText+" type='video/mp4'></video>"+
            "<input type='checkbox' name='your-group' id='combo"+i+"'/> Selecionar";
          }
          document.getElementById('videos').innerHTML = aux;
       }
    };
    xhttp.open("GET", "ajaxreceiver.php?keyword="+document.getElementById('textBusca').value, true);
    xhttp.send();
  }
  
</script>

  Pesquisa:<br>
  <input type="text" name="pesquisa" id='textBusca'><br>
  <button type="button" onclick="bttBusca()">Buscar</button>

  <div id="videos">
    
  </div>



<?= $OUTPUT->footer(); ?>
