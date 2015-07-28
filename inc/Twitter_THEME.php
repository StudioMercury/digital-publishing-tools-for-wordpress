<?php
ini_set('display_errors', 1);
require_once('TwitterAPI.php');

define('TWEET_LIMIT', 'YOUR_DESIRED_LIMIT');
define('TWITTER_USERNAME', 'YOUR_DESIRED_USERNAME_TWEETS');

$settings = array(
    'consumer_key' => "YOUR_CONSUMER_KEY",
    'consumer_secret' => "YOUR_CONSUMER_SECRET",
    'oauth_access_token' => "YOUR_ACCESS_TOKEN",
    'oauth_access_token_secret' => "YOUR_ACCESS_TOKEN_SECRET"
);

$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name='.TWITTER_USERNAME.'&count='.TWEET_LIMIT;
$requestMethod = 'GET';
$twitter = new TwitterAPI($settings);
$tweets = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
echo $tweets;

?>
