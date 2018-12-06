<?php

require_once 'vendor/autoload.php';
require_once 'weather_forecast.php';

try {
    //TODO: initiate Client() with real id
    $bot = new \TelegramBot\Api\Client('test');
    // start
    $bot->command('start', function ($message) use ($bot) {
    $answer = 'Welcome';
    $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    // help
    $bot->command('help', function ($message) use ($bot) {
    $answer = 'Commands:
    /help    - outputs help
    /loc     - retrieve current location
    /weather - display weather forecast for current location

    All other options will simply be returned))';
    $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $bot->command('loc', function ($message) use ($bot){
      // HACK: ReplyKeyboardMarkup plays a role of KeyboardButton, as the latter doesn`t exist in this lib
    $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
            [
                [
                    [
                      'text' => 'Test location',
                      'request_location' => true
                    ]
                ]
            ],
            true
    );
    $messageText = "location";
    $bot->sendMessage($message->getChat()->getId(), $messageText, null, false, null, $keyboard);
    });


    $bot->command('weather', function ($message) use ($bot) {

      if (file_exists("./weather_dat.json")) {
        $dat = file_get_contents("./weather_dat.json");
        $json_dat = json_decode($dat, true);
        $lat = $json_dat["latitude"];
        $lon = $json_dat["longitude"];

        $forecast = new weather_wrapper($lat, $lon);
        $result = $forecast->get_forecast();
        $answer = implode("\r\n",$result);
      } else {
        $answer = "Select /loc command before /weather, as you didn`t provide location data!";
      }

      $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $bot->command('teamnotify', function ($message) use ($bot) {
    $answer = 'Notification';
    //TODO: provide real char_id or user_id
    $bot->sendMessage($uid/*some user or group id*/, $answer);
    });


    $bot->on(function($Update) use($bot) {



      $message = $Update->getMessage();
      // HACK: Get message text.
      $text = $message->getText();
      $cid = $message->getChat()->getId();
      $data = $bot->getRawBody();
      $data_a = json_decode($data, true);
      $lat = $data_a["message"]["location"]["latitude"];
      $lon = $data_a["message"]["location"]["longitude"];

      $coord_arr = array("latitude" => $lat, "longitude" => $lon);
      $weather_dat_file = 'weather_dat.json';
      $handle = fopen($weather_dat_file, 'w') or die ('Can`t open file:  ' . $weather_dat_file);
      $weather_data = json_encode($coord_arr);
      fwrite($handle, $weather_data);

      if ($lat != null && $lon != null) {
        $text = "Your location was successfully detected!";
      }
      // HACK: To get current ChatID - use this!
      //$bot->sendMessage($cid, $cid);
      $bot->sendMessage($cid, $text);

    }, function ($message) use ($name) {
          return true;
    });

    $bot->run();
} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}

 ?>
