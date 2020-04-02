<?php
class Category{

    // соединение с БД и таблицей 'categories'
    private $conn;
    private $table_name = "categories";

    // свойства объекта
    public $id;
    public $name;
    public $description;
    public $created;

    public function __construct($db){
        $this->conn = $db;
    }

    // выбора продуктов в категории
    public function select(){
        // выборка всех данных
        $query = "SELECT 
                p.id as product_id, c.id as category_id, c.name as category_name, b.name as brand_name, p.name, p.description, p.price, p.image, p.top, p.sale, GROUP_CONCAT(col.name) as color
             FROM " .$this->table_name. "  c 
                   RIGHT JOIN
                        products p
                            ON p.category_id = c.id
                   LEFT JOIN
                        brand b
                            ON p.brand_id = b.id
                   LEFT JOIN
                        product_color p_c
                            ON p.id = p_c.product_id
                   LEFT JOIN
                        color col
                            ON col.id = p_c.color_id 
                   WHERE
                        (c.id =  ?) AND p.quantity > 0
                   GROUP BY p.id 
         ";

        $stmt = $this->conn->prepare( $query );

        // привязываем id категории, который будет обновлен
        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        return $stmt;
    }

    // используем раскрывающийся список выбора
    public function read(){

        // выбираем все данные
        $query = "SELECT DISTINCT
                c.id as category_id, c.name
            FROM
                " . $this->table_name . " c
            JOIN
                        products p
                            ON p.category_id = c.id
            WHERE
                        p.quantity > 0
            ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    // используем раскрывающийся список выбора
    public function sort(){
        // выборка всех продуктов для всех категорий
       /* $query = "SELECT
                c.id as category_id, c.name as category_name, b.name as brand_name, col.name as color, p.price
             FROM " .$this->table_name. "  c 
                   JOIN
                        products p
                            ON p.category_id = c.id
                   LEFT JOIN
                        brand b
                            ON p.brand_id = b.id
                   LEFT JOIN
                        product_color p_c
                            ON p.id = p_c.product_id
                   LEFT JOIN
                        color col
                            ON col.id = p_c.color_id  
                   WHERE
                        p.quantity > 0
                   ORDER BY c.id 
         ";*/
        // выборка всех продуктов для одной категории
         $query = "SELECT 
                c.id as category_id, c.name as category_name, GROUP_CONCAT(b.name) as brand_name, GROUP_CONCAT(col.name) as color, MIN(0+p.price) as min_price, MAX(0+p.price) as max_price
             FROM " .$this->table_name. "  c 
                   RIGHT JOIN
                        products p
                            ON p.category_id = c.id
                   LEFT JOIN
                        brand b
                            ON p.brand_id = b.id
                   LEFT JOIN
                        product_color p_c
                            ON p.id = p_c.product_id
                   LEFT JOIN
                        color col
                            ON col.id = p_c.color_id 
                   WHERE
                        (c.id =  ?) AND p.quantity > 0
         ";

        $stmt = $this->conn->prepare( $query );

        // привязываем id категории, который будет обновлен
        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        return $stmt;
    }
}
