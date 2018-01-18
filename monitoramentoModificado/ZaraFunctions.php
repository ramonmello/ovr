<?php

/**
 * MONITORAMENTO OLIMPÍADAS EM 2017
 * Classe de apoio para o relatório
 * Última atualização: 26/10/2017
 *
 * PHP version 7.0
 *
 * @category  library
 * @access    public
 * @package   kaduvelasco
 * @author    Kadu Velasco
 * @licence   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Carlos Eduardo O. Velasco
 * @version   1.0.0
 *
 **/




class ZaraFunctions
{
    private $pdo;

    /**
     * Construtor da classe.
     */
    public function __construct()
    {
        require __DIR__ . '/ZaraPdo.php';
        $this->pdo = new ZaraPdo();

    }

    /**
     * Evita que a classe seja clonada.
     */
    public function __clone()
    {
    }

    /**
     * SELECIONA todos os usuários do curso
     */
    public function selectAllUsers($courseId, $banUser)
    {
        try {

            $sql = "SELECT ra.userid, u.department, u.lastaccess
                FROM mdl_role_assignments ra
                INNER JOIN mdl_user u ON u.id = ra.userid
                INNER JOIN mdl_context co ON ra.contextid = co.id
                WHERE co.contextlevel = 50
                    AND ra.roleid = 5
                    AND co.instanceid = {$courseId}
                    AND ra.userid NOT IN ({$banUser})";

            $sqlResult = $this->pdo->sqlQuery($sql);

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

            $sqlResult = $this->pdo->sqlQuery($sql);

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

            $sqlResult = $this->pdo->sqlQuery($sql);

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
     * SELECIONA os usuários que acessarm o curso, realizaram todas as atividades e estão APROVADOS
     */
    // public function selectApprovedUser($courseId, $accessUser)
    // {
    //     $arrUser = array();

    //     try {
    //         // 01 Seleciona todas as notas dos usuários
    //         $arrGrades = $this->selectUserGrades($courseId, $accessUser);

    //         // 02 Remove os usuários que não completaram todas as atividades
    //         foreach ($arrGrades as $key => $value) {
    //             if (in_array(NULL, $value)) {
    //                 unset($arrGrades[$key]);
    //             }
    //         }

    //         // 03 Gera a lista dos usuários que realizaram todas as atividades
    //         foreach ($arrGrades as $key => $value) {
    //             $arrUser[] = $key;
    //         }

    //         // 03 Seleciona todos os usuários que realizaram as atividades e possuem nota maior ou igual que 60
    //         $listUser  = implode(',',$arrUser);

    //         $sql = "SELECT g.userid, u.department, u.lastaccess
    //             FROM mdl_grade_grades g 
    //             INNER JOIN mdl_user u ON u.id = g.userid
    //             WHERE g.itemid = (SELECT id FROM mdl_grade_items WHERE itemtype = 'course' AND courseid = {$courseId})
    //                 AND g.finalgrade >= 60
    //                 AND g.userid IN ({$listUser})";

    //         $sqlResult = $this->pdo->sqlQuery($sql);

    //         if ($sqlResult === false) {
    //             throw new Exception('Erro selecionando os usuários aprovados no curso.');
    //         }

    //         return $sqlResult;

    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //         return false;
    //     }
    // }

    // /**
    //  * SELECIONA os usuários que acessarm o curso, realizaram todas as atividades e estão REPROVADOS
    //  */
    // public function selectRepprovedUser($courseId, $accessUser)
    // {
    //     $arrUser = array();

    //     try {
    //         // 01 Seleciona todas as notas dos usuários
    //         $arrGrades = $this->selectUserGrades($courseId, $accessUser);

    //         // 02 Remove os usuários que não completaram todas as atividades
    //         foreach ($arrGrades as $key => $value) {
    //             if (in_array(NULL, $value)) {
    //                 unset($arrGrades[$key]);
    //             }
    //         }

    //         // 03 Gera a lista dos usuários que realizaram todas as atividades
    //         foreach ($arrGrades as $key => $value) {
    //             $arrUser[] = $key;
    //         }

    //         // 03 Seleciona todos os usuários que realizaram as atividades e possuem nota maior ou igual que 60
    //         $listUser  = implode(',',$arrUser);

    //         $sql = "SELECT g.userid, u.department, u.lastaccess
    //             FROM mdl_grade_grades g 
    //             INNER JOIN mdl_user u ON u.id = g.userid
    //             WHERE g.itemid = (SELECT id FROM mdl_grade_items WHERE itemtype = 'course' AND courseid = {$courseId})
    //                 AND g.finalgrade < 60
    //                 AND g.userid IN ({$listUser})";

    //         $sqlResult = $this->pdo->sqlQuery($sql);

    //         if ($sqlResult === false) {
    //             throw new Exception('Erro selecionando os usuários reprovados no curso.');
    //         }

    //         return $sqlResult;

    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //         return false;
    //     }
    // }



    // /**
    //  * SELECIONA as atividades do curso
    //  */
    // public function selectCourseActivities($courseId)
    // {
    //     try {
    //         $arrItensNota = array();

    //         $sql = "SELECT id FROM mdl_grade_items WHERE itemname IN ('Atividade 01','Atividade 02','Atividade 03','Atividade 04','Atividade 05') and courseid = {$courseId}";

    //         $sqlResult = $this->pdo->sqlQuery($sql);

    //         if ($sqlResult === false) {
    //             throw new Exception('Erro selecionando as atividades do curso.');
    //         }

    //         foreach ($sqlResult as $result) {
    //             $arrItensNota[] = $result['id'];
    //         }

    //         return $arrItensNota;

    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //         return false;
    //     }
    // }

    // /**
    //  * SELECIONA as notas do usuário
    //  */
    // public function selectUserGrades($courseId, $accessUser)
    // {
    //     $itensNota = $this->selectCourseActivities($courseId);
    //     $arrUserGrades = array();

    //     $listNotas = implode(',',$itensNota);
    //     $listUser  = implode(',',$accessUser);

    //     try {
    //         $sql = "SELECT g.userid, g.itemid, g.finalgrade
    //             FROM mdl_grade_grades g
    //             WHERE g.itemid IN ({$listNotas})
    //                 AND g.userid IN ({$listUser})
    //             ORDER BY g.userid";

    //         $sqlResult = $this->pdo->sqlQuery($sql);

    //         if ($sqlResult === false) {
    //             throw new Exception('Erro selecionando as notas do usuário.');
    //         }

    //         foreach ($sqlResult as $result) {
    //             $arrUserGrades[$result['userid']][$result['itemid']] = $result['finalgrade'];
    //         }

    //         return $arrUserGrades;

    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //         return false;
    //     }
    // }

    /**
     * SELECIONA os dados dos usuários para o relatório
     */
    public function selectUserData($userIds)
    {
        try {

            $sql = "SELECT id, firstname, lastname, username, email, city, phone1, institution, department, lastaccess
                FROM mdl_user
                WHERE id IN ({$userIds})";

            $sqlResult = $this->pdo->sqlQuery($sql);

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
