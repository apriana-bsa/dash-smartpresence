<?php
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	header('Access-Control-Allow-Methods: GET, POST');
  
  function getCurlValue($filename, $contentType, $postname)
  {
      // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
      // See: https://wiki.php.net/rfc/curl-file-upload
      if (function_exists('curl_file_create')) {
          return curl_file_create($filename, $contentType, $postname);
      }
      
      // Use the old style if using an older version of PHP
      $value = "@{$this->filename};filename=" . $postname;
      if ($contentType) {
          $value .= ';type=' . $contentType;
      }
      
      return $value;
  }
  
  if (isset($_POST['namapelapor']) &&
      isset($_POST['emailpelapor']) &&
      isset($_POST['nohppelapor']) &&
      isset($_POST['namaclient']) &&
      isset($_POST['namabug']) &&
      isset($_POST['aplikasi']) &&
      isset($_POST['versi']) &&
      isset($_POST['tingkatbug']) &&
      isset($_POST['detailbug'])
     )
  {
    $trello_key          = '5f79ecc03e54df8fedd44cd151d1b30f';
    $trello_api_endpoint = 'https://api.trello.com/1';
    $trello_list_id      = '57a59c3ae70e76ea78ba85aa';
    $trello_member_token = 'f7c545f09137eb6ad4bc83f8c78629256eb96fa0662b85ffa77742d52ebc570d'; // Guard this well
     
    $label="";
    if (strcasecmp($_POST['tingkatbug'],"biasa")==true || strcasecmp($_POST['tingkatbug'],"rendah")==true)
    {
      $label="green";
    }
    else
    if (strcasecmp($_POST['tingkatbug'],"sedang")==true)
    {
      $label="yellow";
    }
    else
    if (strcasecmp($_POST['tingkatbug'],"penting")==true || strcasecmp($_POST['tingkatbug'],"tinggi")==true )
    {
      $label="red";
    }
    $ch = curl_init("$trello_api_endpoint/cards");
    curl_setopt_array($ch, array(
        CURLOPT_SSL_VERIFYPEER => false, // Probably won't work otherwise
        CURLOPT_RETURNTRANSFER => true, // So we can get the URL of the newly-created card
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS => http_build_query(array( // if you use an array without being wrapped in http_build_query, the Trello API server won't recognize your POST variables
            'key'    => $trello_key,
            'token'  => $trello_member_token,
            'idList' => $trello_list_id,
            'name'   => "///".htmlspecialchars($_POST['namabug'])."/// ".htmlspecialchars($_POST['detailbug']),
            'labels' => $label,
            'desc'   => "**PELAPOR:**\n".
                        htmlspecialchars($_POST['namapelapor'])."\n".
                        htmlspecialchars($_POST['emailpelapor'])."\n".
                        htmlspecialchars($_POST['nohppelapor'])."\n".
                        "\n\n**CLIENT:**\n".
                        htmlspecialchars($_POST['namaclient'])."\n".
                        htmlspecialchars($_POST['aplikasi'])." - ".htmlspecialchars($_POST['versi'])."\n".
                        $_SERVER['REMOTE_ADDR']
        )),
    ));
    $result = curl_exec($ch);
    curl_close ($ch);
    // echo (isset($_FILES['foto']) && $_FILES['foto']['name']!='') == true ? 'ada' : 'tidak';
    // return;
    $trello_card = json_decode($result);
    if ($trello_card->id)
    {
			if (isset($_FILES['foto']) && $_FILES['foto']['name']!='')
			{ 
				$cfile = getCurlValue($_FILES["foto"]["tmp_name"],$_FILES["foto"]["type"],$_FILES["foto"]["name"]);
				$fields = array(
									"file"           => $cfile,
									"key"            => $trello_key,
									"token"          => $trello_member_token
								);		
					
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.trello.com/1/cards/".$trello_card->id."/attachments");
				curl_setopt($ch, CURLOPT_POST,count($fields));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$result=curl_exec ($ch);
				curl_close ($ch);
        // echo $result;return;
				$trello_attachments = json_decode($result);
        if ($trello_attachments->id)
				{
					echo "1";
					return;
				}
			}
			else
			{
				echo "1";
				return;
			}
    }
  }
  echo "0";
?>