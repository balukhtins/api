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
$category->id = isset($_GET['category_id']) ? $_GET['category_id'] : die(json_encode(array("message" => "Не корректно задан category_id"), JSON_UNESCAPED_UNICODE));

// прочитаем детали категории для редактирования
$stmt = $category->sort();
$num = $stmt->rowCount();

// проверяем, найдено ли больше 0 записей
if ($num>0) {

    // массив
    //$a=array();
    $categories_arr=array();
    //$categories_arr_2=array();

    // получим содержимое нашей таблицы
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

        if (is_null($row['category_id'])) {
          die(json_encode(array("message" => "Не корректно задан category_id"), JSON_UNESCAPED_UNICODE));
        }

        // извлекаем строку
        extract($row);

            $product_item=array(
                "category_id" => $category_id,
                "category_name" => $category_name,
                "brand_name" => explode( ',', $brand_name ),
                "color" => explode( ',', $color ),
                "min_price" => $min_price,
                "max_price" => $max_price,
            );
        array_push($categories_arr, $product_item);
     }

     $categories_arr[0]['brand_name'] = array_unique($categories_arr[0]['brand_name']);
     $categories_arr[0]['brand_name'] = array_values($categories_arr[0]['brand_name']);
     $categories_arr[0]['color'] = array_unique($categories_arr[0]['color']);
     $categories_arr[0]['color'] = array_values($categories_arr[0]['color']);

     //обработка результата для всех категорий
    /*$c=0;
    $i=$categories_arr[$c]['category_id'];

    foreach ($categories_arr as $key => $v) {

        if ($i == $v['category_id']) {
            if(!empty( $a )){
                $a['color'] = array_unique($a['color']);
                $a['max_price'] = max($a['price']);
                $a['min_price'] = min($a['price']);
                unset($a['price']);
                array_push($categories_arr_2, $a);
            }
            $b = $v['brand_name'];
            $v['brand_name'] = [$v['brand_name']];
            $v['color'] = [$v['color']];
            $v['price'] = [$v['price']];
            $a=$v;
            $i++;
        }
        else{
            if ($b == $v['brand_name']) {
            $a['color'][] = $v['color'];
            }
            else{
                $b = $v['brand_name'];
                $a['brand_name'][] = $v['brand_name'];
                $a['color'][] = $v['color'];
                $a['price'][] = $v['price'];
            }
        }            
    } 
    $a['color'] = array_unique($a['color']);
    $a['max_price'] = max($a['price']);
    $a['min_price'] = min($a['price']);
    unset($a['price']);
    array_push($categories_arr_2, $a);*/

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