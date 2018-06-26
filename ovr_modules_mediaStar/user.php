<?php

/**
 * RELATÓRIO DE MONITORAMENTO SAEB 2017
 * Listagem de usuários
 * Última atualização: 26/10/2017
 */

require $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require __DIR__ . '/ZaraFunctions.php';

// RECEBE OS valores via POST
if((!isset($_POST['userids'])) || (!isset($_POST['cid'])) || (!isset($_POST['function']))) {
    echo '<h3>Ocorreu um erro inesperado. Tente novamente mais tarde.';
    exit;
}

// VARIÁVEIS LOCAIS
$userIds     = $_POST["userids"];
$courseId    = $_POST["cid"];
$function    = $_POST['function'];

$arrUser = array();
$arrAtividades = array();

$notaFinal = 50;

$arrCursos = array(
    2 => 'Olimpíadas do Ensino Médio'
    //3 => 'Sistema de Avaliação da Educação Básica 2017 - Aplicadores'
);

// Atividades do curso 2
$arrAtividades[2] = array(1 => 10, 2 => 11, 3 => 12, 4 => 13, 5 => 14);

// Inicia a classe
$fnc = new ZaraFunctions();


// SELECIONA os dados do usuário
$arrUser = $fnc->selectUserData($userIds);

// SELECIONA as notas dos usuários
$arrGrades = $fnc->selectUserGrades($courseId, explode(',',$userIds));

?>


<!-- HTML --------------------------------------------------------------------------------------------------------- !-->
<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' xmlns:og='http://ogp.me/ns#'>
<head>
    <meta charset="utf-8">
    <title>Colaboradores</title>
    <style>
        table{width:100%;}
        td{font-family:arial;font-size:13px;padding:7px;}
        table, td{border-collapse:collapse;border:1px solid #ddd;}
        a:link, a:visited{color:#222;font-size:13px;}
        a:hover, a:focus{color:#2170DC;font-size:13px;}
        .divPrincipal{width:100%;text-align:center;margin:0 auto;}
        .divCursos{width:100%;text-align:left;margin:0 auto;border-bottom:1px solid black;}
        .divCursos h4{text-align:center;}
        .divCursos a{font-weight:bold;}
    </style>
</head>
<body>

<div class="divPrincipal">
    <!-- Informações do Curso -->
    <div class="divCursos">
        <?php
        echo '<h4>Monitoramento Olimpíadas EM 2017</h4>';

        echo '<p><strong>Curso:</strong> ' . $arrCursos[$courseId] . '</p>';
        echo '<p><strong>Função:</strong> ' . $function . '</p>';

        echo '<p><form action="export.php" method="post" target="_blank">';
        echo '<input type="hidden" name="cid" value="' . $courseId . '">';
        echo '<input type="hidden" name="function" value="' . $function . '">';
        echo '<input type="hidden" name="userids" value="' . $userIds . '">';
        echo '<button type="submit">Exportar dados para Excel</button>';
        echo '</p>';
        ?>
    </div><br/><br/>

    <table>
        <tbody>
        <tr>
            <td><strong>Nome</strong></td>
            <td><strong>Sobrenome</strong></td>
            <td class="center"><strong>CPF</strong></td>
            <td class="center"><strong>Email</strong></td>
            <td class="center"><strong>Cidade</strong></td>
            <td class="center"><strong>UF</strong></td>
            <td class="center"><strong>Instituição</strong></td>
            <td class="center"><strong>Função</strong></td>
            <td class="center"><strong>Último acesso</strong></td>
            <td class="center"><strong>Atividade 01</strong></td>
            <td class="center"><strong>Atividade 02</strong></td>
            <td class="center"><strong>Atividade 03</strong></td>
            <td class="center"><strong>Atividade 04</strong></td>
            <td class="center"><strong>Atividade 05</strong></td>
            <td class="center"><strong>Nota Final</strong></td>
        </tr>

        <?php foreach ($arrUser as $user) { $notaFinal = 50; ?>
        <tr>
            <td><?php echo $user['firstname']; ?></td>
            <td><?php echo $user['lastname']; ?></td>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['city']; ?></td>
            <td><?php echo $user['phone1']; ?></td>
            <td><?php echo $user['institution']; ?></td>
            <td><?php echo $user['department']; ?></td>
            <td><?php echo $user['lastaccess'] ? date('d/m/Y H:i', $user['lastaccess']) : 'Nunca'; ?></td>
            <!-- Atividade 01 -->
            <td><?php
                if (($arrGrades[$user['id']][$arrAtividades[$courseId][1]] == 'NULL') ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][1]] == NULL) ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][1]] == '')) {
                    echo 'Não realizado';
                    $notaFinal = $notaFinal + 0;
                } else {
                    echo $arrGrades[$user['id']][$arrAtividades[$courseId][1]];
                    $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][1]];
                }
            ?></td>

            <!-- Atividade 02 -->
            <td><?php
                if (($arrGrades[$user['id']][$arrAtividades[$courseId][2]] == 'NULL') ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][2]] == NULL) ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][2]] == '')) {
                    echo 'Não realizado';
                    $notaFinal = $notaFinal + 0;
                } else {
                    echo $arrGrades[$user['id']][$arrAtividades[$courseId][2]];
                    $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][2]];
                }
            ?></td>

            <!-- Atividade 03 -->
            <td><?php
                if (($arrGrades[$user['id']][$arrAtividades[$courseId][3]] == 'NULL') ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][3]] == NULL) ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][3]] == '')) {
                    echo 'Não realizado';
                    $notaFinal = $notaFinal + 0;
                } else {
                    echo $arrGrades[$user['id']][$arrAtividades[$courseId][3]];
                    $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][3]];
                }
            ?></td>

            <!-- Atividade 04 -->
            <td><?php
                if (($arrGrades[$user['id']][$arrAtividades[$courseId][4]] == 'NULL') ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][4]] == NULL) ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][4]] == '')) {
                    echo 'Não realizado';
                    $notaFinal = $notaFinal + 0;
                } else {
                    echo $arrGrades[$user['id']][$arrAtividades[$courseId][4]];
                    $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][4]];
                }
            ?></td>

            <!-- Atividade 05 -->
            <td><?php
                if (($arrGrades[$user['id']][$arrAtividades[$courseId][5]] == 'NULL') ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][5]] == NULL) ||
                    ($arrGrades[$user['id']][$arrAtividades[$courseId][5]] == '')) {
                    echo 'Não realizado';
                    $notaFinal = $notaFinal + 0;
                } else {
                    echo $arrGrades[$user['id']][$arrAtividades[$courseId][5]];
                    $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][5]];
                }
            ?></td>

            <!-- Nota Final -->
            <td><?php echo $notaFinal; ?></td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</body>
</html>
