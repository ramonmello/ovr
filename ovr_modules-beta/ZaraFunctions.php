<?php

/**
 *
 * PHP version 7.0
 *
 * @category  library
 * @access    public
 * @author    Marluce Vitor
 * @licence   https://opensource.org/licenses/MIT MIT License
 * @copyright 2018 Carlos Eduardo O. Velasco  && Marluce Vitor
 * @version   1.0.0
 *
 **/


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();

class ZaraFunctions
{
    
    public function __clone()
    {
    }

    /**
     * SELECIONA todos os usuários do curso
     */
    public function selectAllUsers($courseId, $banUser)
    {
         global $DB;
        try {

            $sql = "SELECT ra.userid, u.department, u.lastaccess
                FROM mdl_role_assignments ra
                INNER JOIN mdl_user u ON u.id = ra.userid
                INNER JOIN mdl_context co ON ra.contextid = co.id
                WHERE co.contextlevel = 50
                    AND ra.roleid = 5
                    AND co.instanceid = {$courseId}
                    AND ra.userid NOT IN ({$banUser})";

            $sqlResult = $DB->execute($sql);

            if ($sqlResult === false) {
                throw new Exception('Erro selecionando todos os usuários.');
            }

            return $sqlResult;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * SELECIONA todos os usuários que ACESSARAM o curso
     */
    public function selectUserAccess($courseId, $banUser)
    {
        global $DB;
        try {

            $sql = "SELECT ra.userid, u.department, u.lastaccess
                FROM mdl_role_assignments ra
                INNER JOIN mdl_user u ON u.id = ra.userid
                INNER JOIN mdl_context co ON ra.contextid = co.id
                WHERE co.contextlevel = 50
                    AND ra.roleid = 5
                    AND co.instanceid = {$courseId}
                    AND ra.userid NOT IN ($banUser)
                    AND u.lastaccess > 0";

            $sqlResult = $DB->execute($sql);

            if ($sqlResult === false) {
                throw new Exception('Erro selecionando os usuários que acessarm o curso.');
            }

            return $sqlResult;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * SELECIONA todos os usuários que NÃO ACESSARAM o curso
     */
    public function selectUserNotAccess($courseId, $banUser)
    {
        global $DB;
        try {

            $sql = "SELECT ra.userid, u.department, u.lastaccess
                FROM mdl_role_assignments ra
                INNER JOIN mdl_user u ON u.id = ra.userid
                INNER JOIN mdl_context co ON ra.contextid = co.id
                WHERE co.contextlevel = 50
                    AND ra.roleid = 5
                    AND co.instanceid = {$courseId}
                    AND ra.userid NOT IN ($banUser)
                    AND u.lastaccess = 0";

            $sqlResult = $DB->execute($sql);

            if ($sqlResult === false) {
                throw new Exception('Erro selecionando os usuários que não acessarm o curso.');
            }

            return $sqlResult;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

   
    /**
     * SELECIONA os dados dos usuários para o relatório,será usado futuramente
     */
    public function selectUserData($userIds)
    {
        global $DB;
        try {

            $sql = "SELECT id, firstname, lastname, username, email, city, phone1, institution, department, lastaccess
                FROM mdl_user
                WHERE id IN ({$userIds})";

            $sqlResult = $DB->execute($sql);

            if ($sqlResult === false) {
                throw new Exception('Erro selecionando os usuários que não acessarm o curso.');
            }

            return $sqlResult;

        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
