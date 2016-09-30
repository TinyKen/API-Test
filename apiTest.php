<?php 
$url = $_POST["url"];

$params = getParams(isset($_POST["params"]) ? $_POST["params"] : array());
$method = isset($_POST["method"]) ? $_POST["method"] : 'GET';
$headers = getParams(isset($_POST["headers"]) ? $_POST["headers"] : array());


$result = array(
        'code' => 200,
        'header' => '',
        'message' => '',
        'success' => 1,
        'time' => date("Y-m-d H:i:s",time())
    );

try{
    $message = http($url, $params, $method, $headers, false);
    $result['message'] = $message;
}catch(Exception $e){
    $result['message'] = $e->getMessage();
}
 echo json_encode($result);

/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false){
    $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return  $data;
}

/**
 * 转换请求参数
 * 请求格式：
 * [
 *    {
 *     "name": "p1",
 *     "value": "a"
 *    },
 *    {
 *     "name": "p2",
 *     "value": "b"
 *    }
 * ]
 *
 * 转换之后的格式
 * {
 *     "p1": "a",
 *     "p2": "b"
 *  }
 *
 *
 * @param  [type] $params [description]
 * @return [type]         [description]
 */
function getParams($params){
    $param_tmp = array();
    if(!empty($params)){
        foreach ($params as $param){
            $param_tmp[$param['name']] = $param['value'];
        }
        
    }
    return $param_tmp;
}

?>