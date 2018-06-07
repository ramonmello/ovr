<?php

//use PDO;
//use PDOException;

/**
 * [pt-br] Classe PHP para utilização de Banco de Dados utilizando PDO
 * [en]    PHP class for use of Database using PDO
 *
 * PHP version 7.0
 *
 * Banco de Dados suportados | Database supported : SQl Server, MySQl, PostgreSQL, Firebird
 * Banco de Dados testados   | Database tested    : MySQL
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

class ZaraPdo
{

    private $dbAllowTypes = array('sqlsrv', 'mssql', 'mysql', 'pg', 'fbd');
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $dbPort;
    private $dbType;
    private $dbChar;

    private $dns;
    private $conn;
    private static $connection;

    private $errors    = array();
    private $messages  = array();


    /**
     * [pt-br] Construtor da classe.
     * [en]    Class constructor.
     *
     * @param string $lang
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbPort
     * @param string $dbType
     * @param string $dbChar
     */
    public function __construct()
    {
        $this->dbType = 'mysql';
        $this->dbHost = 'localhost';
        $this->dbName = 'moodle';
        $this->dbUser = 'root';
        $this->dbPass = 'root';
        $this->dbPort = '3306';
        $this->dbChar = 'UTF8';


        $this->setMessagesDefault();
        $this->conn = $this->createConnection();
    }

    /**
     * [pt-br] Evita que a classe seja clonada.
     * [en]    Prevents the class from being cloned.
     */
    public function __clone()
    {
    }

    /**
     * [pt-br] Destrutor da classe.
     * [en]    Class destructor.
     */
    public function __destruct()
    {
        $this->destroyConnection();
        foreach ($this as $key => $value) {
            unset($this->key);
        }
    }

    /**
     * [pt-br] Define as mensagens de erro padrão.
     * [en]    Sets the default error messages.
     */
    private function setMessagesDefault()
    {
        $this->messages = array(
            'error_type'        => "O Banco de Dados informado não é suportado.",
            'error_connection'  => "Erro estabelecendo a conexão com o Banco de Dados.",
            'error_evalquery'   => "O comando SQL passado não foi identificado.",
            'error_transaction' => "O parâmetro passado está errado! Só é aceito [B=begin, C=commit ou R=rollback]",
            'error_sql'         => "Ocorreu um erro executando a instrução SQL.",
            'error_delete'      => "Informe a condição where."
        );
    }

    /**
     * [pt-br] Define uma mensagem de erro.
     * [en]    Sets the error message.
     *
     * @param $error
     */
    private function setError(string $error)
    {
        array_push($this->errors, $error);
    }

    /**
     * [pt-br] Retorna a lista de todos os erros ocorridos durante a validação.
     * [en]    Returns the list of all errors that occurred during validation.
     *
     * @return array
     */
    public function getErrors() //: array
    {
        return $this->errors;
    }

    /**
     * [pt-br] Encerra a conexão com o Banco de Dados.
     * [en]    Terminates the database connection.
     *
     * @return null
     */
    public function destroyConnection()
    {
        $this->conn = null;
        self::$connection = null;
        return self::$connection;
    }

    /**
     * [pt-br] Cria a conexão com o Banco de Dados.
     * [en]    Create the connection to the Database.
     *
     * @return PDO
     */
    private function createConnection()
    {
        if(!isset(self::$connection)) {
            try {
                $this->setDns();

                $options = array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->dbChar,
                    PDO::ATTR_PERSISTENT => TRUE
                );

                self::$connection = new PDO($this->dns, $this->dbUser, $this->dbPass, $options);

                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $exc) {
                $this->setError($this->messages['error_connection'] . ' - ' . $exc->getMessage());
            }
        }
        return self::$connection;
    }

    /**
     * [pt-br] Define a string de conexão com o banco de dados de acordo com o tipo do banco de dados.
     * [en]    Defines the connection string for the database according to the type of the database.
     *
     * @return bool
     */
    private function setDns()
    {
        if (!in_array($this->dbType, $this->dbAllowTypes)) {
            $this->setError($this->messages['error_dbtype']);
            return false;
        }

        if ($this->dbType == 'mssql') {
            $this->dns = "mssql:host=" . $this->dbHost . ";dbname=" . $this->dbName;
        }

        if ($this->dbType == 'sqlsrv') {
            $this->dns = "sqlsrv:server=" . $this->dbHost . ";database=" . $this->dbName;
        }

        if ($this->dbType == 'fbd') {
            $this->dns = "firebird:dbname=" . $this->dbHost . ":" . $this->dbName;
        }

        if ($this->dbType == 'mysql') {
            $this->dns = "mysql:host=" . $this->dbHost . ";dbname=" . $this->dbName;
            if (is_numeric($this->dbPort)) {
                $this->dns = "mysql:host=" . $this->dbHost . ";port=" . $this->dbPort . ";dbname=" . $this->dbName;
            }
        }

        if ($this->dbType == 'pg') {
            $this->dns = "pgsql:dbname=" . $this->dbName . ";host=" . $this->dbHost;
            if (is_numeric($this->dbPort)) {
                $this->dns = "pgsql:dbname=" . $this->dbName . ";port=" . $this->dbPort . ";host=" . $this->dbHost;
            }
        }
        return true;
    }

    /**
     * [pt-br] Define um estado de transação (B = begin | C = commit | R = rollback).
     * [en]    Defines a transaction state (B = begin | C = commit | R = rollback0.
     *
     * @param $transaction
     */
    public function setTransaction($transaction)
    {
        switch (strtoupper($transaction)) {
            case 'B':
                $this->conn->beginTransaction();
                break;
            case 'C':
                $this->conn->commit();
                break;
            case 'R':
                $this->conn->rollBack();
                break;
            default:
                $this->setError($this->messages['error_transaction']);
                break;
        }
    }

    /**
     * [pt-br] Executa uma instrução SQL, com espaços reservados (nomeados) [Instruções aceitas : SELECT | INSERT | UPDATE | DELETE | CREATE | ALTER | EXEC | CALL | DROP]
     * [en]    Execute a SQL statement, with placeholders (named) [Accepted statements: SELECT | INSERT | UPDATE | DELETE | CREATE | ALTER | EXEC | CALL | DROP]
     *
     * @param $sql
     * @param array $bindvalues
     *
     * @return array|bool
     */
    public function sqlQuery($sql, $bindvalues = array())
    {
        $sqlCommand = $this->evalQuery($sql);

        if (!$sqlCommand) {
            $this->setError($this->messages['error_evalquery']);
            return false;
        }
        return $this->executeQuery($sqlCommand, $bindvalues, $sql);
    }

    /**
     * [pt-br] Analiza a query passada e retorna o tipo de instrução.
     * [en]    Parses the last query and returns the type of statement.
     *
     * @param $query
     *
     * @return bool|string
     */
    private function evalQuery($query)
    {
        $query = strtolower(trim($query));

        $instruction = substr($query,0,6);
        if($instruction == 'select') return 'select';
        if($instruction == 'delete') return 'delete';
        if($instruction == 'insert') return 'insert';
        if($instruction == 'update') return 'update';
        if($instruction == 'create') return 'create';

        $instruction = substr($query,0,5);
        if($instruction == 'alter') return 'alter';

        $instruction = substr($query,0,4);
        if($instruction == 'exec') return 'exec';
        if($instruction == 'call') return 'call';
        if($instruction == 'drop') return 'drop';

        return false;
    }


    /**
     * [pt-br] Executa uma instrução SELECT.
     * [en]    Executes a SELECT statement.
     *
     * @param string $table
     * @param string $colunms
     * @param string $where
     * @param array $bindvalues
     * @param string $order
     * @param string $limit
     * @param bool $distinct
     *
     * @return array|bool|int
     */
    public function select($table = '', $colunms = '*', $where = '', $bindvalues = array(), $order = '', $limit = '', $distinct = false)
    {
        // Monta o SQL
        $bSql = 'SELECT ';

        if ($distinct) {
            $bSql .= ' DISTINCT ';
        }

        $bSql .= $colunms . ' FROM ' . $table;

        if ('' !== $where) {
            $bSql .= ' WHERE ' . $where;
        }

        if ('' !== $order) {
            $bSql .= ' ORDER BY '. $order;
        }

        if ('' !== $limit) {
            $bSql .= ' LIMIT '. $limit;
        }

        return $this->executeQuery('select', $bindvalues, $bSql);
    }

    /**
     * [pt-br] Executa uma instrução INSERT.
     * [en]    Executes a INSERT statement.
     *
     * @param $table
     * @param array $bindvalues
     *
     * @return array|bool
     */
    public function insert($table, $bindvalues = array())
    {
        // Monta o SQL
        $txtColumns = '';
        $txtValues  = '';

        $bSql = 'INSERT INTO ' . $table;

        foreach ($bindvalues as $key => $value) {
            $txtColumns .= substr($key, 1) . ',';
            $txtValues  .= $key . ',';
        }

        $txtColumns = substr($txtColumns, 0, -1);
        $txtValues  = substr($txtValues,  0, -1);

        $bSql .= ' (' . $txtColumns . ') VALUES (' . $txtValues . ')';

        return $this->executeQuery('insert', $bindvalues, $bSql);
    }

    /**
     * [pt-br] Executa uma instrução UPDATE.
     * [en]    Executes a UPDATE statement.
     *
     * @param $table
     * @param array $bindvalues
     * @param bool $where
     * @param array $bindWhere
     *
     * @return array|bool
     */
    public function update($table, $bindvalues = array(), $where = false, $bindWhere = array())
    {
        $bSql = 'UPDATE ' . $table . ' SET ';

        foreach ($bindvalues as $key => $value) {
            $bSql .= substr($key, 1) . '=' . $key . ',';
        }

        $bSql = substr($bSql, 0, -1);

        if ($where) {
            $bSql .= ' WHERE ' . $where;
        }

        $bindvalues = array_merge($bindvalues, $bindWhere);

        return $this->executeQuery('update', $bindvalues, $bSql);
    }

    /**
     * [pt-br] Executa uma instrução DELETE.
     * [en]    Executes a DELETE statement.
     * @param $table
     * @param bool $where
     * @param array $bindvalues
     * @return array|bool
     */
    public function delete($table, $where = '', $bindvalues = array())
    {
        if('' == $where) {
            $this->setError($this->messages['error_delete']);
            return false;
        }
        $bSql = 'DELETE FROM ' . $table . ' WHERE ' . $where;

        return $this->executeQuery('delete', $bindvalues, $bSql);
    }

    /**
     * [pt-br] Executa a instrução sql.
     * [en]    Executes the sql statement.
     *
     * @param $sqlCommand
     * @param array $bindvalues
     * @param $sql
     *
     * @return array|bool|int
     */
    private function executeQuery($sqlCommand, $bindvalues = array(), $sql)
    {
        try {
            // Prepara a instrução SQl para execução
            $stm = $this->conn->prepare($sql);

            // Proteje os dados
            if(count($bindvalues) > 0) {
                foreach ($bindvalues as $key => $value) {
                    $bindType = gettype($value);

                    switch ($bindType) {
                        case 'boolean':
                            $stm->bindValue($key, $value, PDO::PARAM_BOOL);
                            break;
                        case 'integer':
                        case 'double' :
                            $stm->bindValue($key, $value, PDO::PARAM_INT);
                            break;
                        case 'NULL':
                        case 'null':
                            $stm->bindValue($key, $value, PDO::PARAM_NULL);
                            break;
                        case 'string':
                            $stm->bindValue($key, $value, PDO::PARAM_STR);
                            break;
                        case 'object':
                            $stm->bindValue($key, $value, PDO::PARAM_LOB);
                            break;
                        default:
                            var_dump($stm->bindValue($key, $value));
                            break;
                    }
                }
            }

            // Executa a instrução SQL
            $stm->execute();

            // Retorna os dados
            switch ($sqlCommand) {
                case 'insert':
                case 'update':
                case 'delete':
                    return $stm->rowCount();
                    break;
                case 'select':
                    return $stm->fetchAll(PDO::FETCH_ASSOC);
                    break;
                default:
                    if ($stm->errorCode() == '00000') return true;
                    break;
            }

        } catch (PDOException $exc) {
            $this->setError($this->messages['error_sql'] . ' - ' . $exc->getMessage());
            return false;
        }
        return false;
    }
}
