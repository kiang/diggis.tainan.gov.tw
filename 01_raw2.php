<?php
$layers = array(
  'TNRoad/TN_3_1/MapServer/2' => '橋梁暫時完成版1050525',
  'TNRoad/TN_3_1/MapServer/3' => '99年建案明細表',
  'TNRoad/TN_3_1/MapServer/4' => '100年建案明細表',
  'TNRoad/TN_3_1/MapServer/5' => '101年建案明細表',
  'TNRoad/TN_3_1/MapServer/6' => '102年建案明細表',
  'TNRoad/TN_3_1/MapServer/7' => '103年建案明細表',
  'TNRoad/TN_3_1/MapServer/8' => '104年建案明細表',

);
foreach($layers AS $layerUrl => $layerName) {
  $layerId = str_replace('/', '_', $layerUrl);
  $idFile = __DIR__ . '/raw/' . $layerId . 'Id';
  $layerPath = __DIR__ . '/raw/' . $layerId;
  if(!file_exists($layerPath)) {
    mkdir($layerPath, 0777, true);
  }
  if(!file_exists($idFile)) {
    file_put_contents($idFile, '0');
  }
  $lastId = intval(file_get_contents($idFile));
  $objects = array();

  while(++$lastId) {
    $objects[] = $lastId;
    if($lastId % 200 === 0) {
      $targetFile = $layerPath . '/data_' . $lastId . '.json';
      if(!file_exists($targetFile)) {
        $q = implode(',', $objects);
        $json = gzdecode(shell_exec("curl -k 'http://59.125.203.147/arcgis/rest/services/{$layerUrl}/query?objectIds={$q}&outFields=*&returnGeometry=true&f=json' -H 'Host: 59.125.203.147' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:54.0) Gecko/20100101 Firefox/54.0' -H 'Accept: */*' -H 'Accept-Language: en-US,en;q=0.5' -H 'Accept-Encoding: gzip, deflate, br' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Referer: http://59.125.203.147/DIG_SYS/map.aspx?type=pipe' -H 'Connection: keep-alive'"));
        $obj = json_decode($json, true);
        if(!isset($obj['features'][0])) {
          file_put_contents($idFile, $lastId);
          echo "{$layerId} done";
          break;
        }
        echo "processing {$layerId}/{{$lastId}}\n";
        file_put_contents($targetFile, $json);
      }
      $objects = array();
      file_put_contents($idFile, $lastId);
    }
  }
}
