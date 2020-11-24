<?php
  $apiKey = "789b43d8529fcd36ac585558ff9e50f1";
  $cityId = "5196220";
#  $cityId = "4560349";
  $googleApiUrl = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=en&units=imperial&APPID=" . $apiKey;

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_VERBOSE, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $response = curl_exec($ch);

  curl_close($ch);
  $data = json_decode($response);
  $currentTime = time();?>
        <h2><?php echo $data->name; ?> Weather Status</h2>
        <div class="time">
            <div><?php echo date("l g:i a", $currentTime); ?></div>
            <div><?php echo date("jS F, Y",$currentTime); ?></div>
            <div><?php echo ucwords($data->weather[0]->description); ?></div>
        </div>
        <div class="weather-forecast">
            <span><img
                src="http://openweathermap.org/img/w/<?php echo $data->weather[0]->icon; ?>.png"
                class="weather-icon" /></span>
        </div>
        <div class="time">
            <div>Temperature: <?php echo $data->main->temp; ?>°F</div>
            <div>Humidity: <?php echo $data->main->humidity; ?> %</div>
            <div>Feel Like: <?php echo $data->main->feels_like; ?>°F</div>
            <div>Wind: <?php echo $data->wind->speed; ?> km/h</div>
            <div>
           <?php
            $url = "https://bitpay.com/api/rates";
            $json = json_decode(file_get_contents($url));
            $dollar = $btc = 0;
            foreach($json as $obj){
              if ($obj->code == "USD") {
              echo 'Bitcoin: $'. $obj->rate .'<br>';
              }
            }
          ?>
           </div>
        </div>
