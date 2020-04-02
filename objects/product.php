<?php
class Product {

    // подключение к базе данных и таблице 'products'
    private $conn;
    private $table_name = "products";

    // свойства объекта
    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;
    public $created;
    public $brand_name;
    public $image;
    public $color;
    public $top;
	public $sale;
	public $quantity;


    // конструктор для соединения с базой данных
    public function __construct($db){
        $this->conn = $db;
    }

    // метод read() - получение товаров
    function read(){

        // выбираем все записи
        $query = "SELECT 
             p.id as product_id, b.name as brand_name, p.name, p.price, p.image, p.top, p.sale, GROUP_CONCAT(col.name) as color
             FROM " .$this->table_name. "  p 
                   LEFT JOIN
                        categories c
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
                   WHERE (p.top IS NOT NULL OR p.sale IS NOT NULL) AND p.quantity > 0
                   GROUP BY p.id
         ";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    // используется при заполнении формы обновления товара
    function readOne() {

        // запрос для чтения одной записи (товара)
        /*$query = "SELECT 
             c.name as category_name, b.name as brand_name, p.id, p.name, p.description, p.price, p.image, p.top, p.sale, col.name as color,  p.quantity, aut.name as autor, rev.text as review, rev.rating as rating
             FROM " .$this->table_name. "  p 
                   LEFT JOIN
                        categories c
                            ON p.category_id = c.id
                   LEFT JOIN
                         brand b
                        ON p.brand_id = b.id
                   LEFT JOIN
                    product_color p_c
                        ON p.id = p_c.product_id
                   LEFT JOIN
                    color col
                        ON p_c.color_id = col.id
                   LEFT JOIN
                    product_review p_r
                        ON p.id = p_r.product_id
                   LEFT JOIN
                    review rev
                        ON p_r.review_id = rev.id
                   LEFT JOIN
                    autors aut
                        ON rev.autor_id = aut.id
            	   WHERE
               		p.id = ?
               	   GROUP BY 
                   		rev.id, col.id 	 
         ";*/
         $query = "SELECT 
             c.name as category_name, b.name as brand_name, p.id, p.name, p.description, p.price, p.image, p.top, p.sale, GROUP_CONCAT(col.name) as color,  p.quantity, aut.name as autor, rev.text as review, rev.rating as rating
             FROM " .$this->table_name. "  p 
                   LEFT JOIN
                        categories c
                            ON p.category_id = c.id
                   LEFT JOIN
                         brand b
                        ON p.brand_id = b.id
                   LEFT JOIN
                    product_color p_c
                        ON p.id = p_c.product_id
                   LEFT JOIN
                    color col
                        ON p_c.color_id = col.id
                   LEFT JOIN
                    product_review p_r
                        ON p.id = p_r.product_id
                   LEFT JOIN
                    review rev
                        ON p_r.review_id = rev.id
                   LEFT JOIN
                    autors aut
                        ON rev.autor_id = aut.id
            	   WHERE
               		p.id = ?
               	   GROUP BY 
                   		rev.id 
         ";

        // подготовка запроса
        $stmt = $this->conn->prepare( $query );

        // привязываем id товара, который будет обновлен
        $stmt->bindParam(1, $this->id);
		
	    // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    // метод search - поиск товаров
    function search($keywords){

        // выборка по всем записям
        $query = "SELECT DISTINCT
             b.name as brand_name, p.id, p.name, p.price, p.image
             FROM " .$this->table_name. " p 
                   LEFT JOIN
                         brand b
                        ON p.brand_id = b.id
                   WHERE
                		(p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ?) AND p.quantity > 0 
            ";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // очистка
        $keywords=htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // привязка
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

        // метод select - выбор товаров по параметрам
    function select($keywords){

       // выборка по всем записям
        $query = "SELECT DISTINCT
             b.name as brand_name, p.id as product_id, p.name, p.price, p.image, p.top, p.sale
             FROM " .$this->table_name. " p 
                   LEFT JOIN
                        categories c
                            ON p.category_id = c.id
                   LEFT JOIN
                         brand b
                        ON p.brand_id = b.id
                   LEFT JOIN
                    product_color p_c
                        ON p.id = p_c.product_id
                   LEFT JOIN
                    color col
                        ON p_c.color_id = col.id
                   WHERE
                		c.id = ? AND b.name LIKE ? AND p.price >= ? AND p.price <= ? AND col.name LIKE ? AND p.quantity > 0 
            ";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // разложим массив на переменные
        $color = "%%";
        $min = 0;
        $max = 100000000000000000;
        $brand_name = "%%";
        
        if (is_array($keywords)) {
            extract($keywords);
            $brand_name = "%{$brand_name}%";
            $color = "%{$color}%";
        }
        else die(json_encode(array("message" => "Товары не найдены."), JSON_UNESCAPED_UNICODE));


        // привязка
        $stmt->bindParam(5, $color);
        $stmt->bindParam(3, $min);
        $stmt->bindParam(4, $max);
        $stmt->bindParam(2, $brand_name);
        $stmt->bindParam(1, $category_id);

        // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

}