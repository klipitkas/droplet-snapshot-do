#!/usr/bin/php
<?php

define('DROPLET', '');
define('TOKEN', '');
define('MAX_SNAPSHOTS', '4');

if (empty(DROPLET)) {
  die('Invalid droplet ID.');
}

if (empty(TOKEN)) {
  die('Invalid token, cannot authenticate!');
}

if(empty(MAX_SNAPSHOTS)) {
  die('Please enter a valid amount of snapshots.');
}

$headers = [
  "Accept: application/json",
  "Content-type: application/json",
  "Authorization: Bearer " . TOKEN
];

echo "Fetching the total amount of snapshots";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://api.digitalocean.com/v2/droplets/". DROPLET . "/snapshots?page=1&per_page=100");
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$r = json_decode(curl_exec($curl), true);

$count = count($r["snapshots"]);

if ($count > MAX_SNAPSHOTS - 1) {
  $remaining = $count;
  foreach($r["snapshots"] as $snapshot) {
    if ($remaining > MAX_SNAPSHOTS - 1) {
      $id = $snapshot["id"];

      // Delete the snapshot
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "https://api.digitalocean.com/v2/snapshots/$id");
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
      $d = curl_exec($curl);

      $remaining--;
    }
  }
}

echo "Creating a new snapshot.";

$curl = curl_init();

$snapshot = [
  'type' => 'snapshot',
  'name' => bin2hex(random_bytes(24))
];

curl_setopt($curl, CURLOPT_URL, "https://api.digitalocean.com/v2/droplets/" . DROPLET . "/actions");
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($snapshot));
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$r = curl_exec($curl);
