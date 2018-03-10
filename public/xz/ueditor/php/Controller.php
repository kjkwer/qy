<?php
//require  "public/xz/ueditor/config.json";


$filename ="./public/uploads/img/".time().$_FILES['upfile']['name'];
move_uploaded_file($_FILES["upfile"]["tmp_name"],$filename);//将临时地址移动到指定地址
echo json_encode([
    'imageActionName'=>'upload',
    'imageFieldName'=>"upfile",
    'imageAllowFiles'=>[
        ".png", ".jpg", ".jpeg", ".gif", ".bmp"
    ]
    ,
    'imageMaxSize'=>2048,
    'imageUrl'=>$filename,
    'imagePath'=>"public/uploads/img"
]);