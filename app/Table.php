<?php

namespace Mestrona\Bank;

class Table
{
    const SQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \PDO
     */
    protected $databaseHandle;

    public function __construct(\PDO $databaseHandle, string $table)
    {
        $this->databaseHandle = $databaseHandle;
        $this->table = $table;
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

    public function idExists($id)
    {
        $sql = "SELECT 1 as count FROM `".$this->table."` WHERE id = ? LIMIT 1";
        $statement = $this->databaseHandle->prepare($sql);
        $statement->execute([$id]);
        return $statement->fetchColumn();
    }
}