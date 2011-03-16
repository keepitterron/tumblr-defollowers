<?php
include 'htmlsql.class.php';
include 'snoopy.class.php';
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Tumblr defollowers</title>
<style type="text/css">
		body { color: #444; font: 14px Arial; margin: 0; padding: 0; }
		h1, h2, h3, h4, h5, p, ul, li, ol, div, img, button, blockquote { padding: 0; margin: 0; border: 0 none; }
		a { color: #FC746D; text-decoration: none; }
		a:hover { color: #FB9765; }
		#container { width: 900px; margin: 0 auto; }
		footer { width: 900px; float: left; padding: 10px 0; }
		#content { padding: 20px; width: 520px;\width: 560px;w\idth: 520px; float: left; background: #fff;border: 1px solid #f0f0f0;border-bottom: 2px solid #ccc; -webkit-border-radius: 8px; -moz-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; }

		footer a { color: #FC746D; font-size: 14px; font-weight: 700; text-transform: uppercase; text-shadow: 1px 1px 1px #333; }
		h1 { font-size: 48px; font-family: 'Lobster', arial, serif; text-shadow: 1px 1px 3px #999; color: #FC746D }
		h3 { color: #779D52; }
		
		form { width: 520px; padding: 20px 0; font-size: 26px; }
		form div { float: left; width: 520px; padding: 0 0 10px; }
		form label { float: left; width: 200px; height: 40px; line-height: 40px; }
		form input { float: right; width: 300px; height: 40px; font-size: 26px; border: 1px solid #999; -webkit-border-radius: 8px; -moz-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; }
		form button { float: right; font-size: 26px; border: 0 none; -webkit-border-radius: 15px; -moz-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; background: #333; color: #fff; font-size: 26px; padding: 5px 10px; cursor: pointer; }
		
		#content ul { list-style: none; float: left; width: 520px; padding: 0 0 20px; }
		#content ul li {font-size: 26px; background:#333;border: 1px solid #999; -webkit-border-radius: 8px; -moz-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 4px 8px;}
		#content ul.love li { background: #FC746D; }
		#content ul.love li a { color: #333; }
		#content ul.love li a:hover { color: #666; }
		p.x { font-size: 11px; color: #c00; }
		</style>
</head>
<body>
		<div id=container>
			<header>
				<h1>Tumblr defollowers</h1>
			</header>
			<div id=content>
<?php
if(!$_POST){
?>
	<p>Insert your tumblr's username and password to check who's not following you anymore.</p>
	<p class="x"><em>None of your data (except for your followers list) will be saved on this server</em></p>
    <form method="post" action="">
      <div>
      <label>email</label>
      <input type="input" name="email">
      </div>
      <div>
      <label>password</label>
      <input type="password" name="password">
      </div>
      <div><button type="submit" name="access">check</button></div>
    </form>
<?php
  
} else {

$f = array();

for($i=1;$i<200;$i++){
  $a = find_followers($i);
  $f = array_merge($f, $a);
  if(!$a) break;
}
$oald = (array)json_decode(get_oald());
save_data(json_encode($f));
$persi = array();
foreach( $oald as $x ){
  if( !in_array($x, $f) ) $persi[] = $x;
}
if( !$oald ) echo '<h2>'.count($f).' followers</h1>'.'<h3>Informations saved, come back later if you\'ve lost some followers.</h3>';
if( $oald && !$persi ) echo '<h2>'.count($f).' followers</h1>'.'<h3>No changes since your last check.</h3>';
if( $oald && $persi ) echo '<h2>lost follower(s)</h2><ul>';
foreach($persi as $p)
{
  echo '<li><a href="'.$p.'" target="_blank">'.$p.'</a></li>';
}
if( $oald && $persi ) echo '</ul>';

}

function get_oald()
{
  $f = $_POST['email'].'.txt';
  if( !file_exists($f) ) return false;
  $fp = fopen($f, 'r');
  $o = fread($fp, filesize($f));
  fclose($fp);
  return $o;
}

function save_data($f)
{
	if(strlen($f) < 10) return false;
  $fp = fopen($_POST['email'].'.txt', 'w');
  fwrite($fp, $f);
  fclose($fp);
}

function find_followers( $page )
{
  $folli = array();
    $wsql = new htmlsql();
    
    // connect to a URL
    if (!$wsql->connect('string', connect_to_url('http://www.tumblr.com/followers/page/'.$page))){
        print 'Error while connecting: ' . $wsql->error;
        exit;
    }
    if (!$wsql->query('SELECT text FROM div WHERE $class="name"')){
        print "Query error: " . $wsql->error; 
        exit;
    }
    foreach($wsql->fetch_array() as $row){
      $t = trim($row['text']);
      $cut_from = strpos($t, '<img class="avatar"');
      if( strpos($t, '<a href') == 0 && $cut_from )
      {
        $folli[] = substr($t, 9, $cut_from-11);
      }
    }
    
    return $folli;
    
}

function connect_to_url( $url )
{
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.tumblr.com/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
curl_setopt ($ch, CURLOPT_COOKIEJAR, $_POST['email'].'_cookie.txt');
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
$store = curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, $url);
$a = curl_exec($ch);
curl_close($ch);
return $a;
}

?>
			</div>
			<footer>
				<p><a href="http://oneblood.tumblr.com">a service by oneblood</a></p>
			</footer>
		</div>
</body>
</html>