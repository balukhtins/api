<?php
// необходимые HTTP-заголовки
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// подключение файла для соединения с базой и файл с объектом
include_once '../config/database.php';
include_once '../objects/product.php';

// получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// подготовка объекта
$product = new Product($db);

// установим свойство ID записи для чтения
$product->id = isset($_GET['id']) ? $_GET['id'] : die();

// прочитаем детали товара для редактирования
$stmt = $product->readOne();
$num = $stmt->rowCount();

if ($num>0) {

    // массив товаров
    $product_arr=array();
    $product_arr_2=array();

     // получаем извлеченную строку
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                // извлекаем строку
        extract($row);

            $product_item=array(
                "id" => $id,
                "brand_name" => $brand_name,
                "name" => $name,
                "description" => html_entity_decode($description),
                "price" => $price,
                "image" => $_SERVER['DOCUMENT_ROOT'].$image,
                "top" => $top,
                "sale" => $sale,
                "color" => explode( ',', $color ),
                "quantity" => $quantity,
                "autor" => $autor,
                "review" => $review,
                "rating" => $rating,
            );
        array_push($product_arr, $product_item);
    }

    $c=0;
    $i=$product_arr[$c]['id'];

        foreach ($product_arr as $key => $v) {
            if ($i==$v['id']) {
                $v['reviews'][$c]['autor'] = $v['autor'];
                $v['reviews'][$c]['review'] = $v['review'];
                $b=$v['reviews'][$c]['rating'] = $v['rating'];
                $a=$v;
                unset($a['autor'], $a['review']); 
                $i++;
                $c++;
            }
            else {
                $a['reviews'][$c]['autor'] = $v['autor'];
                $a['reviews'][$c]['review'] = $v['review'];
                $b+=$a['reviews'][$c]['rating'] = $v['rating'];
                $c++;
            }
        }

    array_push($product_arr_2, $a);

    // код ответа - 200 OK
    http_response_code(200);

    // выводим данные о товаре в формате JSON
  
    $product_arr_2[0]['rating']=$b/$c; 
    echo json_encode($product_arr_2);

}

else {
    // код ответа - 404 Не найдено
    http_response_code(404);

    // сообщим пользователю, что товар не существует
    echo json_encode(array("message" => "Товар не существует."), JSON_UNESCAPED_UNICODE);
}