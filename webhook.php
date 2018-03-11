<?php
// Only react on a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('403 Forbidden', true, 403);
  header('Location: http://www.example.com');
  exit(0);
}

function writeToLog(&$fs, $msg) {
  fwrite($fs, date("Y-m-d H:i:s") . ': ' . $msg . PHP_EOL);
}

// Define the permitted IP address and secret key
$security = json_decode(file_get_contents('./webhook.json'), true);
$permittedIPAddresses =  $security['ip'];
$permittedSecurityToken = $security['token'];
unset($security);

// Get the security token and originating IP address
$securityToken = $_SERVER['HTTP_X_GITLAB_TOKEN'];
$originIPAddress = $_SERVER['REMOTE_ADDR'];

// Log the webhook request
$fs = fopen('./webhook.log', 'a');
writeToLog($fs, '=======================================================================');
writeToLog($fs, "Request from ${originIPAddress}");

// Confirm the request originated from a permitted IP
if (in_array($originIPAddress, $permittedIPAddresses) === false) {
  writeToLog($fs, "IP address ${originIPAddress} is not permitted to make a request!");
  header('403 Forbidden', true, 403);
  header('Location: http://www.example.com');
  exit(0);
}

// Confirm the security token is correct
if ($securityToken !== $permittedSecurityToken) {
  writeToLog($fs, "Security token '${securityToken}' is not a recognized token!");
  header('403 Forbidden', true, 403);
  header('Location: http://www.example.com');
  exit(0);
}

// Get the JSON payload
writeToLog($fs, 'Security token and origin IP validated, pull the webhook payload');
$payload = json_decode(file_get_contents('php://input'), true);

// Get the event type and originating branch
$eventType = $payload['object_kind'];
$eventRef = $payload['ref'];

// Development server
if ($eventType === 'push' && $eventRef === 'refs/heads/master') {
  writeToLog($fs, 'Push event identified, pushing changes to development');
  // TODO Trigger git request
  // exec("/home/deploy/dev_deploy.sh");

// Production server
} elseif (
  $eventType === 'tag_push' &&
  preg_match('/^refs\/tags\/v(?:\d{1,}[.]\d{1,}[.]\d{1,})$/', $eventRef) !== false
) {
  writeToLog($fs, 'Tag event identified, pushing changes to production');
  // TODO Trigger git request
  // exec("/home/deploy/prod_deploy.sh");
}

// Clean up after ourselves
writeToLog($fs, 'Webhook complete');
fclose($fs);
unset($securityToken, $permittedIPAddresses, $permittedSecurityToken);
exit(0);
