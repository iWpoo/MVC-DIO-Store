<?php

namespace App\Core;
use \PDO;	

class Model
{ 
	protected static $link;
	protected $table;
	
	public function __construct()
	{
		if (!self::$link) {
			self::$link = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS);
		}
	}
		
	// Code for reading data from the database
	public function find($value, $attribute = 'id')
	{
		$query = self::$link->prepare("SELECT * FROM ".$this->table." WHERE $attribute = :value");
        $query->execute([':value' => $value]);
        return $query->fetch(PDO::FETCH_ASSOC);
	}
    

    // CRUD
	// Code for inserting data into the database
	public function create(array $data)
	{
        $fields = implode(', ', array_keys($data));
        $values = implode(', :', array_keys($data));
        $query = "INSERT INTO ".$this->table." ($fields) VALUES (:$values)";
        $stmt = self::$link->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
        }
	}

	// Code for reading all data from the database
	public function findAll($by = 'id', $order = 'ASC') 
	{
        $query = self::$link->prepare("SELECT * FROM ". $this->table ." ORDER BY $by $order");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

	// Code for updating data in the database
	public function update($element, string $attribute, array $data) 
    {
        $query = "UPDATE ".$this->table." SET ";
        $update_fields = [];
        foreach ($data as $key => $value) {
            $update_fields[] = "$key = :$key";
        }
        $query .= implode(', ', $update_fields);
        $query .= " WHERE $attribute = :element";
        $stmt = self::$link->prepare($query);
        $stmt->bindValue(':element', $element);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        
        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
        }
    }

	// Code for deleting data from the database
	public function delete($element, $attribute) 
    {
        $query = self::$link->prepare("DELETE FROM ".$this->table." WHERE $attribute = :element");
        $query->execute([':element' => $element]);

        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
        }
    }
}