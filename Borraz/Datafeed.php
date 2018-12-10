<?php
/**
 * Including components.
 */

namespace Borraz;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class Datafeed{

	const TWEET_LIMIT = 40;
	const TWITTER_SRC = 'twitter';
	const TWITTER_SCREEN_NAME = 'emilioborraz';
	const TWITTER_TIMELINE_API = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	const TWITTER_API_TIMEOUT = 2.0;
	const DATA_FILENAME = 'datafeed.json';

	public function refresh(){
		$tweetsDownload = $this->getTweets(self::TWEET_LIMIT);
		$tweetsDownload->then(
			function($parsedTweets){
				$this->writeToFile(PUBLIC_PATH . '/' . constant(__CLASS__ . "::DATA_FILENAME"),
					json_encode($parsedTweets));
			}
		);
	}
	/**
	 * Gets the latest tweets
	 * @return GuzzleHttp\Promise\Promise
	 * @throws GuzzleHttp\Exception\RequestException
	 */
	private function getTweets($limit){
		$client = new Client([
		    'base_uri' => self::TWITTER_TIMELINE_API.'?screen_name='. self::TWITTER_SCREEN_NAME .'&count='.$limit,
		    'timeout'  => self::TWITTER_API_TIMEOUT,
		]);

		$twitterPromiseRequest = $client->requestAsync('GET', '', ['headers' => ['Authorization' => 'Bearer AAAAAAAAAAAAAAAAAAAAANCkzgAAAAAAXXb3mMwO4i7RYuBmeAUgtfD1J6A%3DtiBlI8lNG8BpDmsws9YpN0ynuhJaz20HUYfzcW0RTjAeqeoJ4c']]);
		
		return $twitterPromiseRequest->then(
		    function (ResponseInterface $res){
		    	return $this->parseTweets($res->getBody()->getContents());
		    },
		    function (RequestException $e) {
		        echo $e->getMessage() . "\n";
		        echo $e->getRequest()->getMethod();
		    }
		);
	}

	/**
	 * Parse the Twitter API response
	 * @param  String $tweets
	 * @return array
	 */
	private function parseTweets(String $tweets){
		$tweetsArraySimplified = [];
		if(!$tweetsArray = json_decode($tweets)){
			throw new Exception("Could not decode the Twitter response", 1);
		}

		foreach ($tweetsArray as $index => $tweet) {
			array_push($tweetsArraySimplified, [
				'text' => $tweet->text,
				'thumbnail' => $this->getTweetMedia($tweet),
				'created_at' => $tweet->created_at,
				'resourceLink' => 'https://twitter.com/'.self::TWITTER_SCREEN_NAME.'/status/'.$tweet->id,
				'id' => $tweet->id,
				'source' => self::TWITTER_SRC
				]);
		}
		return $tweetsArraySimplified;
	}

	private function getTweetMedia($tweet){
		
		return '';
	}

	/**
	 * Writes data to a file
	 * @param  string $filePath
	 * @param  string $data
	 * @return int | bool in case of failure
	 */
	private function writeToFile(string $filePath, string $data){
		$fp = fopen($filePath, 'wb');
		flock($fp, LOCK_EX);
		$bytesWritten = fwrite($fp, $data);
		fclose($fp);
		return $bytesWritten;
	}
}