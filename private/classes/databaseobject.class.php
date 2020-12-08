<?php

class DatabaseObject {

   static protected $database; 
   static protected $table_name = ""; 
   static protected $columns = [];
   public $errors = []; 

    static public function set_database($database) {
    self::$database = $database;
    }

   static public function find_by_sql($sql) {
       $result = self::$database->query($sql);
       if(!$result) {
           exit("<p>Database query failed</p>");
       }
       // Turn results into objects
       $object_array = [];
       while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
           $object_array[] = static::instantiate($record);
         }
       //  $result->free();
       return $object_array;
   }

   static public function find_all() {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        return static::find_by_sql($sql);
    }
 

    
    static public function find_by_id($id) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE id=" . self::$database->quote($id);
        $object_array = static::find_by_sql($sql);
        if(!empty($object_array)) {
            return array_shift($object_array);
        }   else    {
            return false;
        }
    }

    static public function instantiate($record) {
        $object = new static;
        foreach($record as $property => $value) {
            if(property_exists($object, $property)) {
                $object->$property = $value;
            }
        }
        return $object;
    }

    protected function validate() {
        $this->errors = [];
        //Add custom validations
        return $this->errors;
    }

    public function create_not_bound() {
        $sql = "INSERT INTO " . static::$table_name . " (common_name, habitat, food, conservation_id, backyard_tips)";
        $sql .= " VALUES (";
        $sql .= "'" . $this->common_name . "', ";
        $sql .= "'" . $this->habitat . "', ";
        $sql .= "'" . $this->food . "', ";
        $sql .= "'" . $this->conservation_id . "', ";
        $sql .= "'" . $this->backyard_tips . "'";
        $sql .= ")";
        $result = self::$database->exec($sql);

        if( $result ) {
            $this->id = self::$database->lastInsertID();
        } else echo "Insert query did not run";
        return $result;
    }

    protected function create() {
        $this->validate();
        if(!empty($this->errors)) { return false; }

        $attributes = $this->sanitized_attributes();
        $sql = "INSERT INTO " . static::$table_name . " (";
        $sql .= join(', ', array_keys($attributes));
        $sql .= ") VALUES (";
        $sql .= join(", ", array_values($attributes));
        $sql .= ");";
        $stmt = self::$database->prepare($sql);
        $stmt->bindValue(':common_name', $this->common_name );
        $stmt->bindValue(':habitat', $this->habitat );
        $stmt->bindValue(':food', $this->food );
        $stmt->bindValue(':conservation_id', $this->conservation_id );
        $stmt->bindValue('backyard_tips', $this->backyard_tips );
        
        //$result = self::$database->exec($sql);
        $result = $stmt->execute();

        if( $result ) {
                $this->id = self::$database->lastInsertID();
            } else echo "Insert query did not run";
            return $result; 
        }

    protected function update() {
        $this->validate();
        if(!empty($this->errors)) { return false; }

        $attributes = $this->sanitized_attributes();
        $attribute_pairs = [];
        foreach($attributes as $key => $value) {
            $attribute_pairs[] = "{$key}={$value}";
        }
        $sql = "UPDATE " . static::$table_name . " SET ";
        $sql .= join(', ', $attribute_pairs);
        $sql .= " WHERE id=" . self::$database->quote($this->id) . " ";
        $sql .= "LIMIT 1";
        $result = self::$database->query($sql);
        return $result;
    }

    public function save() {
    // A new record will not have an id yet
        if(isset($this->id)) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    public function merge_attributes($args=[]) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

//gets each of the columns and loops through, attributes = columns
//properties that have the db columns except id
    public function attributes() {
        $attributes = [];
        foreach(static::$db_columns as $column) {
            if($column == 'id') { continue; }
            $attributes[$column] = $this->$column;
        }  
        return $attributes;
    }

    //this will loop through the attributes and sanitize the values
    protected function sanitized_attributes() {
        $sanitized = [];
        foreach($this->attributes() as $key => $value) {
            $sanitized[$key] = self::$database->quote($value);
        }
        return $sanitized; 
    }


    public function delete() {
        $sql = "DELETE FROM " . static::$table_name . " ";
        $sql .= "WHERE id = " . self::$database->quote($this->id) . " ";
        $sql .= "LIMIT 1";

        $result = self::$database->query($sql);
        return $result;
        
        //after deleting, the instance of the object will still exist
        //even though the database record does not
        //this can be useful for echoing back the item that was deleted
        //you can't call $user->update() after $user->delete()
    }

}
?>
