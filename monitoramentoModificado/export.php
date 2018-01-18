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


// ARQUIVO
$file = 'usuarios_saeb_' . date('d/m/Y-H:i') . '.xls';

// MONTA RELATÓRIO para impressão

$html .= '<table>';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td colspan="11">' . iconv('utf-8', 'iso-8859-1', 'Monitoramento Olimpíadas EM 2017') . '';
$html .= '</tr><tr>';
$html .= '<td colspan="11"><strong>Curso:</strong> ' . iconv('utf-8', 'iso-8859-1', $arrCursos[$courseId]) . '</p>';
$html .= '</tr><tr>';
$html .= '<td colspan="11"><strong>' . iconv('utf-8', 'iso-8859-1', 'Função:') . '</strong> ' . iconv('utf-8', 'iso-8859-1', $function) . '</p>';
$html .= '</tr><tr><td colspan="11"></td></tr>';
$html .= '</tbody>';
$html .= '</table>';

$html .= '<table>';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td><strong>Nome</strong></td>';
$html .= '<td><strong>Sobrenome</strong></td>';
$html .= '<td class="center"><strong>CPF</strong></td>';
$html .= '<td class="center"><strong>Email</strong></td>';
$html .= '<td class="center"><strong>Cidade</strong></td>';
$html .= '<td class="center"><strong>UF</strong></td>';
$html .= '<td class="center"><strong>' . iconv('utf-8', 'iso-8859-1', 'Instituição') . '</strong></td>';
$html .= '<td class="center"><strong>' . iconv('utf-8', 'iso-8859-1', 'Função') . '</strong></td>';
$html .= '<td class="center"><strong>' . iconv('utf-8', 'iso-8859-1', 'Último Acesso') . '</strong></td>';
$html .= '<td class="center"><strong>Atividade 01</strong></td>';
$html .= '<td class="center"><strong>Atividade 02</strong></td>';
$html .= '<td class="center"><strong>Atividade 03</strong></td>';
$html .= '<td class="center"><strong>Atividade 04</strong></td>';
$html .= '<td class="center"><strong>Atividade 05</strong></td>';
$html .= '<td class="center"><strong>Nota Final</strong></td>';
$html .= '</tr>';

foreach ($arrUser as $user) {
    $notaFinal = 50;

    $html .= '<tr>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['firstname'])   . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['lastname'])    . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['username'])    . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['email'])       . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['city'])        . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['phone1'])      . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['institution']) . '</td>';
    $html .= '<td>' . iconv('utf-8', 'iso-8859-1', $user['department'])  . '</td>';

    // Verifica o último acesso
    if($user['lastaccess']) {
        $html .= '<td>' . date("d/m/Y H:i", $user["lastaccess"]) . '</td>';
    } else {
        $html .= '<td>Nunca</td>';
    }

    // Atividade 01
    if (($arrGrades[$user['id']][$arrAtividades[$courseId][1]] == 'NULL') ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][1]] == NULL) ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][1]] == '')) {
            $html .= '<td>' . iconv('utf-8', 'iso-8859-1', 'Não realizado') . '</td>';
            $notaFinal = $notaFinal + 0;
    } else {
        $html .= '<td>' . $arrGrades[$user['id']][$arrAtividades[$courseId][1]] . '</td>';
        $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][1]];
    }

    // Atividade 02
    if (($arrGrades[$user['id']][$arrAtividades[$courseId][2]] == 'NULL') ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][2]] == NULL) ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][2]] == '')) {
        $html .= '<td>' . iconv('utf-8', 'iso-8859-1', 'Não realizado') . '</td>';
        $notaFinal = $notaFinal + 0;
    } else {
        $html .= '<td>' . $arrGrades[$user['id']][$arrAtividades[$courseId][2]] . '</td>';
        $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][2]];
    }

    // Atividade 03
    if (($arrGrades[$user['id']][$arrAtividades[$courseId][3]] == 'NULL') ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][3]] == NULL) ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][3]] == '')) {
        $html .= '<td>' . iconv('utf-8', 'iso-8859-1', 'Não realizado') . '</td>';
        $notaFinal = $notaFinal + 0;
    } else {
        $html .= '<td>' . $arrGrades[$user['id']][$arrAtividades[$courseId][3]] . '</td>';
        $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][3]];
    }

    // Atividade 04
    if (($arrGrades[$user['id']][$arrAtividades[$courseId][4]] == 'NULL') ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][4]] == NULL) ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][4]] == '')) {
        $html .= '<td>' . iconv('utf-8', 'iso-8859-1', 'Não realizado') . '</td>';
        $notaFinal = $notaFinal + 0;
    } else {
        $html .= '<td>' . $arrGrades[$user['id']][$arrAtividades[$courseId][4]] . '</td>';
        $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][4]];
    }

    // Atividade 05
    if (($arrGrades[$user['id']][$arrAtividades[$courseId][5]] == 'NULL') ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][5]] == NULL) ||
        ($arrGrades[$user['id']][$arrAtividades[$courseId][5]] == '')) {
        $html .= '<td>' . iconv('utf-8', 'iso-8859-1', 'Não realizado') . '</td>';
        $notaFinal = $notaFinal + 0;
    } else {
        $html .= '<td>' . $arrGrades[$user['id']][$arrAtividades[$courseId][5]] . '</td>';
        $notaFinal = $notaFinal + $arrGrades[$user['id']][$arrAtividades[$courseId][5]];
    }

    // Nota Final
    $html .= '<td>' . $notaFinal . '</td>';

    $html .= '</tr>';
}

$html .= '</tbody>';
$html .= '</table>';


// Força o Download do Arquivo Gerado
header ('Cache-Control: no-cache, must-revalidate');
header ('Pragma: no-cache');
header('Content-Type: application/x-msexcel');
header ("Content-Disposition: attachment; filename=\"{$file}\"");

echo $html;