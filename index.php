<?php

/**
 * Stupidly simple 5 minute creation using PHP & cURL based on
 * https://blog.0day.rocks/abusing-gmail-to-get-previously-unlisted-e-mail-addresses-41544b62b2
 * exploiting a non rate-limited Google URL.
 */

$config = [
  'namesFile' => 'small.txt', // name of file containing names
  'optionalName' => 'surname', // additional name (middle/surname)
];

$namesList = file($config['namesFile']);

foreach ($namesList as $name) {
  $name = trim($name);
  $url = "https://mail.google.com/mail/gxlu?email=$name{$config['optionalName']}@gmail.com";

  $timeout = 10;
  $headers = [];
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

  curl_setopt($ch, CURLOPT_HEADERFUNCTION,
      function ($curl, $header) use (&$headers) {
          $len = strlen($header);
          $header = explode(':', $header, 2);
          if (count($header) < 2) { // ignore invalid headers
              return $len;
          }

          $headers[strtolower(trim($header[0]))][] = trim($header[1]);

          return $len;
      }
  ); // cURL header solution: https://stackoverflow.com/a/41135574/13392491

  curl_exec($ch);

  if (array_key_exists('set-cookie', $headers) && $headers['set-cookie'] !== '') {
      echo nl2br("<span style='color: red'>$name{$config['optionalName']}@gmail.com is taken :(</span>\n");
  } else {
      echo nl2br("<span style='color: green'>$name{$config['optionalName']}@gmail.com is available!</span>\n");
  }

  curl_close($ch);
}
