<?php
/*
 * This file is part of FEED ON FEEDS - http://feedonfeeds.com/
 *
 * shared.php - display shared items for a user
 *
 *
 * Copyright (C) 2004-2007 Stephen Minutillo
 * steve@minutillo.com - http://minutillo.com/steve/
 *
 * Distributed under the GPL - see LICENSE
 *
 */

$fof_no_login = true;
include_once("fof-main.php");
include_once("fof-render.php");

$user = $_GET['user'];
if(!isset($user)) die;

$format = $_GET['format'];

$prefs = new FoF_Prefs($user);
$sharing = $prefs->get("sharing");
if($sharing == "no") die;

$name = $prefs->get("sharedname");
$url = $prefs->get("sharedurl");

$which = ($sharing == "all") ? "all" : "shared";

if(isset($_GET['which']))
{
    $which = ($sharing == "all") ? $_GET['which'] : "shared " . $_GET['which'];
    $extratitle = " items tagged " . $_GET['which'];
}

$feed = NULL;
if(isset($_GET['feed']))
{
    $feed = $_GET['feed'];
    $r = fof_db_get_feed_by_id($feed);
    $extratitle .= " from <a href='" . $r['feed_link'] . "'>" . $r['feed_title'] . "</a>";
}

$result = fof_get_items($user, $feed, $which, NULL, 0, 100);


$shared_feed = htmlspecialchars("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?user=$user&format=atom");
$shared_link = htmlspecialchars("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?user=$user");

if(isset($_GET['which']))
{
    $shared_feed .= '&which=' . $_GET['which'];
    $shared_link .= '&which=' . $_GET['which'];
}

if(isset($_GET['feed']))
{
    $shared_feed .= '&feed=' . $_GET['feed'];
    $shared_link .= '&feed=' . $_GET['feed'];
}


if($format == "atom")
{
header("Content-Type: application/atom+xml; charset=utf-8");
echo '<?xml version="1.0"?>';
?>

<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Feed on Feeds - Shared Items<?php if($name) echo " from $name"; if($extratitle) echo " " . strip_tags($extratitle) ?></title>
  <updated><?php echo gmdate('Y-m-d\TH:i:s\Z')?></updated>
  <generator uri="http://feedonfeeds.com/">Feed on Feeds</generator>
  <?php if($name) echo "<author><name>$name</name></author>"; ?>
  <id><?php echo $shared_feed ?></id>
  <link href="<?php echo $shared_feed ?>" rel="self" type="application/atom+xml"/>
  <link href="<?php echo $shared_link ?>" rel="alternate"/>

<?php

foreach($result as $item)
{
    $feed_link = htmlspecialchars($item['feed_link']);
    $feed_url = htmlspecialchars($item['feed_url']);
	$feed_title = htmlspecialchars($item['feed_title']);

	$item_link = htmlspecialchars($item['item_link']);
    
    $item_guid = $item['item_guid'];
    if(!ereg("^[a-z0-9\.\+\-]+:", $item_guid))
    {
        $item_guid = $feed_link . '#' . $item_guid;
    }
    $item_guid = htmlspecialchars($item_guid);
    
	$item_title = htmlspecialchars($item['item_title']);
	$item_content = htmlspecialchars($item['item_content']);

	$item_published = gmdate('Y-m-d\TH:i:s\Z', $item['item_published']);
	$item_cached = gmdate('Y-m-d\TH:i:s\Z', $item['item_cached']);
	$item_updated = gmdate('Y-m-d\TH:i:s\Z', $item['item_updated']);

	if(!$item_title) $item_title = "[no title]";
	
?>

  <entry>
    <id><?php echo $item_guid ?></id>
    <link href="<?php echo $item_link ?>" rel="alternate" type="text/html"/>
    <title type="html"><?php echo $item_title ?></title>
    <summary type="html"><?php echo $item_content ?></summary>
    <updated><?php echo $item_updated ?></updated>
    <source>
      <id><?php echo $feed_link ?></id>
      <link href="<?php echo $feed_link ?>" rel="alternate" type="text/html"/>
      <link href="<?php echo $feed_url ?>" rel="self" type="application/atom+xml"/>
      <title><?php echo $feed_title ?></title>
    </source>
  </entry>    
<?php
}
echo '</feed>';
}
else
{
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>

   <head>
      <link rel="alternate" href="<?php echo $shared_feed?>" type="application/atom+xml"/>
      <title>Feed on Feeds - Shared Items<?php if($name) echo " from $name"; if($extratitle) echo " " . strip_tags($extratitle) ?></title>
      <link rel="stylesheet" href="fof.css" media="screen" />
      <style>
      .box
      {
          font-family: georgia;
          background: #eee;
          border: 1px solid black;
          width: 30em;
          margin: 10px auto 20px;
          padding: 1em;
          text-align: center;
      }
      </style>

   </head>
      
  <body>

  <h1 class="box"><a href="http://feedonfeeds.com/">Feed on Feeds</a> - Shared Items
  <?php if($name) echo " from ";
  if($url) echo "<a href='$url'>";
  if($name) echo "$name";
  if($url) echo "</a>";
  if($extratitle) echo "<br><i>$extratitle</i>" ?>
 </h1>
<div id="items">

<?php

$first = true;

foreach($result as $item)
{
	$item_id = $item['item_id'];
	print '<div class="item shown" id="i' . $item_id . '">';
    
    $feed_link = $item['feed_link'];
	$feed_title = $item['feed_title'];
	$feed_image = $item['feed_image'];
	$feed_description = $item['feed_description'];

	$item_link = $item['item_link'];
	$item_id = $item['item_id'];
	$item_title = $item['item_title'];
	$item_content = $item['item_content'];
	$item_read = $item['item_read'];

	$item_published = gmdate("Y-n-d g:ia", $item['item_published'] + $offset*60*60);
	$item_cached = gmdate("Y-n-d g:ia", $item['item_cached'] + $offset*60*60);
	$item_updated = gmdate("Y-n-d g:ia", $item['item_updated'] + $offset*60*60);

	if(!$item_title) $item_title = "[no title]";
	
?>

<div class="header">

    <h1>
        <a href="<?php echo $item_link ?>">
            <?php echo $item_title ?>
		</a>
	</h1>
	
    
    <span class='dash'> - </span>
    
    <h2>

    <a href="<?php echo $feed_link ?>" title='<?php echo $feed_description ?>'><img src="<?php echo $feed_image ?>" height="16" width="16" border="0" /></a>
    <a href="<?php echo $feed_link ?>" title='<?php echo $feed_description ?>'><?php echo $feed_title ?></a>

    </h2>

	<span class="meta">on <?php echo $item_published ?> GMT</span>

</div>


<div class="body"><?php echo $item_content ?></div>

<div class="clearer"></div>
</div>

<?php
}

if(count($result) == 0)
{
	echo "<p><i>No shared items.</i></p>";
}

?>

</div></body></html>

<?php } ?>
