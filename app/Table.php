<?php

namespace Mestrona\Bank;

class Table
{
    const SQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \PDO
     */
    protected $databaseHandle;
    /**
     * @var string
     */
    protected $table;
    /**
     * @var string
     */
    protected $primaryKey;

    public function __construct(\PDO $databaseHandle, string $table, string $primaryKey = 'id')
    {
        $this->databaseHandle = $databaseHandle;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    public function insertArray($associativeArray)
    {
        $fields = array_keys($associativeArray);

        $fieldList = implode(',', $fields);

        $values = array_values($associativeArray);
        $placeHolders = str_repeat("?,",count($fields)-1) . '?';

        $sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $this->table, $fieldList, $placeHolders);

        $statement = $this->databaseHandle->prepare($sql);
        return $statement->execute($values);
    }

    public function idExists($id, $configCode = 'default')
    {
        $sql = "SELECT 1 as count FROM `".$this->table."` WHERE `".$this->primaryKey."` = ? AND config_code = ? LIMIT 1";
        $statement = $this->databaseHandle->prepare($sql);
        $statement->execute([$id, $configCode]);
        return $statement->fetchColumn();
    }
}