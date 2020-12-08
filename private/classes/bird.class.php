<?php

 class Bird extends DatabaseObject {

    public $id;
    public $common_name;
    public $habitat;
    public $food;
    public $nest_placement;
    public $behavior;
    public $backyard_tips;
    public $conservation_id=1;
    
    static protected $table_name = 'birds';

    static protected $db_columns = [
    'id', 
    'common_name', 
    'habitat', 
    'food', 
    'conservation_id', 
    'backyard_tips'
    ];

    public function name() {
    return "
    {$this->common_name} 
    {$this->habitat} 
    {$this->food} 
    {$this->conservation_id}
    {$this->backyard_tips}";
    }

    public function conservation() {
    if( $this->conservation_id > 0 ) {
        return self::CONSERVATION_OPTIONS[$this->conservation_id];
        } else {
          return "Unknown";
        }
    }

    public const CONSERVATION_OPTIONS = [ 
    1 => "Low concern",
    2 => "Medium concern",
    3 => "High concern",
    4 => "Extreme concern"
    ];

    public function __construct($args=[]) {
    $this->common_name = $args['common_name'] ?? '';
    $this->habitat = $args['habitat'] ?? '';
    $this->food = $args['food'] ?? '';
    $this->nest_placement = $args['nest_placement'] ?? '';
    $this->behavior = $args['behavior'] ?? '';
    $this->backyard_tips = $args['backyard_tips'] ?? '';
    $this->conservation_id = $args['conservation_id'] ?? '';
    }

    protected function validate() {
        $this->errors = [];
 
        if(is_blank($this->common_name)) {
            $this->errors[] = "Name cannot be blank.";
        }
        if(is_blank($this->habitat)) {
            $this->errors[] = "Habitat cannot be blank.";
        }
        if(is_blank($this->food)) {
            $this->errors[] = "Food cannot be blank.";
        }
        if(is_blank($this->conservation_id)) {
            $this->errors[] = "Conservation level cannot be blank.";
        }
        return $this->errors;
    }
}