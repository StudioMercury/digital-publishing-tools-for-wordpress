<?php
ini_set('display_errors', 1);
require_once('TwitterAPI.php');

/** Define constants here **/
define('TWEET_LIMIT', 5);
define('TWITTER_USERNAME', 'digitalcookers');

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'consumer_key' => "ZyRhOAGn5wLfR2ATQZgMaNgcw",
    'consumer_secret' => "FLAZo2H7nCOgZIPq6pBqZw1F70BPletdo8B9NyjUPTIqPHrzkm",
    'oauth_access_token' => "1522887050-EcTGQy4q6JBNlLgGaDd1iYm321cu1pwDcj9FQVG",
    'oauth_access_token_secret' => "WfJ2ZqL5iDyzMXSFcfjZ97cgtMyXCPA6EZyih6nYe0B1L"
);

/** Perform a GET request and echo the response **/
$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name='.TWITTER_USERNAME.'&count='.TWEET_LIMIT;
$requestMethod = 'GET';
$twitter = new TwitterAPI($settings);
$tweets = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
echo $tweets;

?>
