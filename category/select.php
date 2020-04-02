<?php
// необходимые HTTP-заголовки
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// подключение файла для соединения с базой и файл с объектом
include_once '../config/database.php';
include_once '../objects/category.php';

// получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// подготовка объекта
$category = new Category($db);

// установим свойство ID записи для чтения
$category->id = isset($_GET['category_id']) ? $_GET['category_id'] : die();

// прочитаем детали категории для редактирования
$stmt = $category->select();
$num = $stmt->rowCount();

// проверяем, найдено ли больше 0 записей
if ($num>0) {

    // массив
    $categories_arr=array();
    
    // получим содержимое нашей таблицы
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        //print_r($row);
        // извлекаем строку
        extract($row);

            $product_item=array(
                "product_id" => $product_id,
                "category_id" => $category_id,
                "category_name" => $category_name,
                "brand_name" => $brand_name,
                "name" => $name,
                "price" => $price,
                "image" => $_SERVER['DOCUMENT_ROOT'].$image,
                "top" => $top,
                "sale" => $sale,
                "color" => explode( ',', $color ),
            );
        array_push($categories_arr, $product_item);
     }
   
    // код ответа - 200 OK
    http_response_code(200);

    // покажем данные категорий в формате json
        echo json_encode($categories_arr);
}
else {

    // код ответа - 404 Ничего не найдено
    http_response_code(404);

    // сообщим пользователю, что категории не найдены
    echo json_encode(array("message" => "Товары в данной категории не найдены."), JSON_UNESCAPED_UNICODE);
}