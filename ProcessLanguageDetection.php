<?php

set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

/**
 * Created by PhpStorm.
 * User: enteng
 * Date: 10/13/2018
 * Time: 7:14 PM
 */
class ProcessLanguageDetection
{
	public static function detectLanguage()
	{
		$execDirectory = 'text_files';
		if (is_dir($execDirectory)) {
			if ($dh = opendir($execDirectory)) {
				while (($execFile = readdir($dh)) !== false) {
					$fileType = substr(strrchr($execDirectory . "/" . $execFile, "."), 1);
					if ($fileType === "txt") {
						$lines = file($execDirectory . "/" . $execFile, FILE_IGNORE_NEW_LINES);
						$file = fopen("translated_csv/" . str_replace($fileType, 'csv', $execFile),"w");
						foreach ($lines as $line) {
							try {
								echo 'Word: ' . $line;
								$ch = curl_init("https://cxl-services.appspot.com/proxy?url=https://translation.googleapis.com/language/translate/v2/detect?" . http_build_query(array('q'=>$line)));
								if ($ch === false) {
									throw new Exception('failed to initialize');
								}

								curl_setopt($ch, CURLOPT_HEADER, 0);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								$output = curl_exec($ch);
								// Check the return value of curl_exec(), too
								if ($output === false) {
									throw new Exception(curl_error($ch), curl_errno($ch));
								} else {
									if ($output === 'Service Unavailable') {
										//do sleep here
										sleep(320);

										//do request again
										$output = curl_exec($ch);
									}
								}

								curl_close($ch);
								$decoded = json_decode($output, true);

								$language = $decoded['data']['detections'][0][0]['language'];

								echo '	Language: ' . $language . '<br>';

								$lineToWrite = '"' . $line . '","' . $language .'"' . PHP_EOL;
								fwrite($file,$lineToWrite);

								sleep(rand(.5,3));
							} catch (Exception $e) {
								echo 'Exception: ' . $e->getMessage();
							}
						}
						fclose($file);
					}
				}
			}
			closedir($dh);
		}
	}
}    //ProcessLanguageDetection

ProcessLanguageDetection::detectLanguage();