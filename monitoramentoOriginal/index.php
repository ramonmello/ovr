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

<!-- Configurações para exibição da página ------------------------------------------------------------------------ !-->
<?php
$courseContext = context_course::instance($courseData->id);

$PAGE->set_url('/report/csv/old.php', array('id' => $courseData->id));
$PAGE->set_title('Monitoramento');
$PAGE->set_heading($courseData->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading('Monitoramento Olimpíadas EM 2017');


?>


<!-- HTML --------------------------------------------------------------------------------------------------------- !-->
<link rel="stylesheet" type="text/css" href="styles.css">

<div class="divPrincipal">
    <!-- Informações do Curso -->
    <div class="divCursos">
        <?php
        echo '<h4 class="classe-exemplo">Você está vendo os dados referentes ao curso: ' . $arrCursos[$courseId] . '</h4>';

        $totCursos = count($arrCursos) + 1;

        for ($i = 0; $i <= $totCursos; $i++) {
            if ((isset($arrCursos[$i])) && ($i != $courseId)) {
                echo '<p>Ver os dados do curso: <strong><a href="' . $urlCourse . $i .'">' . $arrCursos[$i] . '</a></strong></p>';
            }
        }
        ?>
    </div>

    <!-- TABELA com os dados da instituição selecionada -->
    <h3 class="classe-exemplo">Monitoramento Olimpíadas EM 2017</h3>

    <table class="table" style="width:100%;">
        <thead>
        <tr>
            <td width="300"><strong>Função</strong></td>
            <td width="66" class="center"><strong>Colaboradores</strong></td>
            <td width="66" class="center"><strong>Acessou</strong></td>
            <td width="66" class="center"><strong>Nunca acessou</strong></td>
            <!--<td width="66" class="center"><strong>Em progresso</strong></td>-->
            <td width="66" class="center"><strong>Aprovado</strong></td>
            <td width="66" class="center"><strong>Reprovado</strong></td>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($allUserBF as $key => $value) { ?>
            <tr>
                <!-- Nome da Função -->
                <td><?= $key; ?></td>

                <!-- Total de usuários (por função) -->
                <td class="center">
                    <?php
                    if(count($value) == 0) { echo '<button>0</button>'; } else { ?>
                        <form action="user.php" method="post" target="_blank">
                            <input type="hidden" name="cid" value="<?= $courseData->id; ?>">
                            <input type="hidden" name="function" value="<?= $key; ?>">
                            <input type="hidden" name="userids" value="<?= implode(',',$value); ?>">
                            <button type="submit"><?= count($value); ?></button>
                        </form>
                    <?php } ?>
                </td>

                <!-- Usuários que acessaram o curso (por função) -->
                <td class="center">
                    <?php
                    if(count($accessUserBF[$key]) == 0) { echo '<button>0</button>'; } else { ?>
                        <form action="user.php" method="post" target="_blank">
                            <input type="hidden" name="cid" value="<?= $courseData->id; ?>">
                            <input type="hidden" name="function" value="<?= $key; ?>">
                            <input type="hidden" name="userids" value="<?= implode(',',$accessUserBF[$key]); ?>">
                            <button type="submit"><?= count($accessUserBF[$key]); ?></button>
                        </form>
                    <?php } ?>
                </td>

                <!-- Usuários que não acessaram o site (por função) -->
                <td class="center">
                    <?php
                    if(count($notAccessUserBF[$key]) == 0) { echo '<button>0</button>'; } else { ?>
                        <form action="user.php" method="post" target="_blank">
                            <input type="hidden" name="cid" value="<?= $courseData->id; ?>">
                            <input type="hidden" name="function" value="<?= $key; ?>">
                            <input type="hidden" name="userids" value="<?= implode(',',$notAccessUserBF[$key]); ?>">
                            <button type="submit"><?= count($notAccessUserBF[$key]); ?></button>
                        </form>
                    <?php } ?>
                </td>

                <!-- Usuários que estão em andamento no questionário -->
                <!--<td class="center"></td>-->

                <!-- Usuários Aprovados (por função) -->
                <td class="center">
                    <?php
                    if(count($approvedBF[$key]) == 0) { echo '<button>0</button>'; } else { ?>
                        <form action="user.php" method="post" target="_blank">
                            <input type="hidden" name="cid" value="<?= $courseData->id; ?>">
                            <input type="hidden" name="function" value="<?= $key; ?>">
                            <input type="hidden" name="userids" value="<?= implode(',',$approvedBF[$key]); ?>">
                            <button type="submit"><?= count($approvedBF[$key]); ?></button>
                        </form>
                    <?php } ?>
                </td>

                <!-- Usuários reprovados (por função) -->
                <td class="center">
                    <?php
                    if(count($repprovedBF[$key]) == 0) { echo '<button>0</button>'; } else { ?>
                        <form action="user.php" method="post" target="_blank">
                            <input type="hidden" name="cid" value="<?= $courseData->id; ?>">
                            <input type="hidden" name="function" value="<?= $key; ?>">
                            <input type="hidden" name="userids" value="<?= implode(',',$repprovedBF[$key]); ?>">
                            <button type="submit"><?= count($repprovedBF[$key]); ?></button>
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>


        <!-- TOTAIS -->
        <tr style="background: #eee;">
            <!-- Nome da Função -->
            <td><strong>TOTAIS</strong></td>

            <!-- Número total de usuários cadastrados no curso -->
            <td class="center sua">
                <?php
                if(count($allUser) == 0) { echo '<button>0</button>'; } else { ?>
                <form action="user.php" method="post" target="_blank">
                    <input type="hidden" name="cid" value="<?= $courseData->id ?>">
                    <input type="hidden" name="function" value="Todas">
                    <input type="hidden" name="userids" value="<?= implode(',',$allUser); ?>">
                    <button type="submit"><?= count($allUser); ?></button>
                </form>
                <?php } ?>
            </td>

            <!-- Total de usuários que acessaram o curso -->
            <td class="center sua">
                <?php
                if(count($accessUser) == 0) { echo '<button>0</button>'; } else { ?>
                <form action="user.php" method="post" target="_blank">
                    <input type="hidden" name="cid" value="<?= $courseData->id ?>">
                    <input type="hidden" name="function" value="Todas">
                    <input type="hidden" name="userids" value="<?= implode(',',$accessUser); ?>">
                    <button type="submit"><?= count($accessUser); ?></button>
                </form>
                <?php } ?>
            </td>

            <!-- Total de usuários que não acessaram o curso -->
            <td class="center sua">
                <?php
                if(count($notAccessUser) == 0) { echo '<button>0</button>'; } else { ?>
                <form action="user.php" method="post" target="_blank">
                    <input type="hidden" name="cid" value="<?= $courseData->id ?>">
                    <input type="hidden" name="function" value="Todas">
                    <input type="hidden" name="userids" value="<?= implode(',',$notAccessUser); ?>">
                    <button type="submit"><?= count($notAccessUser); ?></button>
                </form>
                <?php } ?>
            </td>

            <!-- Total de usuários que estão em progresso no questionário -->
            <!--<td class="center sua"></td>-->

            <!-- Total de usuários aprovados -->
            <td class="center sua">
                <?php
                if(count($approved) == 0) { echo '<button>0</button>'; } else { ?>
                <form action="user.php" method="post" target="_blank">
                    <input type="hidden" name="cid" value="<?= $courseData->id ?>">
                    <input type="hidden" name="function" value="Todas">
                    <input type="hidden" name="userids" value="<?= implode(',',$approved); ?>">
                    <button type="submit"><?= count($approved); ?></button>
                </form>
                <?php } ?>
            </td>

            <!-- Total de usuários reprovados -->
            <td class="center sua">
                <?php
                if(count($repproved) == 0) { echo '<button>0</button>'; } else { ?>
                <form action="user.php" method="post" target="_blank">
                    <input type="hidden" name="cid" value="<?= $courseData->id ?>">
                    <input type="hidden" name="function" value="Todas">
                    <input type="hidden" name="userids" value="<?= implode(',',$repproved); ?>">
                    <button type="submit"><?= count($repproved); ?></button>
                </form>
                <?php } ?>
            </td>
        </tr>
        </tbody>
    </table>

</div>

<?= $OUTPUT->footer(); ?>
