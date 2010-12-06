<?php

//Convert Zoints Thread Tags to vB 3.7 tagging system - by zappsan (http://piforums.info)

require_once('./global.php');

$i=1;

echo '<strong>Converting...</strong><br /><br />';

$query = $db->query_read("SELECT * FROM `" . TABLE_PREFIX . "zoints_tag`") or die(mysql_error());
$total=$db->num_rows($query);

while ($tags=$db->fetch_array($query)) 
{
  $tagtext=$db->escape_string($tags['tag']);
  $threadid=$tags['threadid'];

  $compare=$db->query_read("SELECT * FROM `" . TABLE_PREFIX . "tag` WHERE tagtext='$tagtext'");
  $tags=$db->fetch_array($compare);
  $number=$db->num_rows($compare);
  $dateline=time();

  if ($number!=1)
  {
    $db->query_write("INSERT INTO `" . TABLE_PREFIX . "tag` (`tagtext`, `dateline`) VALUES ('$tagtext','$dateline')");
    $tagid=$db->insert_id();
    $db->query_write("INSERT INTO `" . TABLE_PREFIX . "tagthread` (`tagid`, `threadid`, `userid`, `dateline` ) VALUES ('$tagid','$threadid','1','$dateline')");
  }
  else
  {
    $tagid=$tags['tagid'];
    $compare=$db->query_read("SELECT * FROM `" . TABLE_PREFIX . "tagthread` WHERE tagid='$tagid' AND threadid='$threadid'");
    $number=$db->num_rows($compare);
    if ($number!=1)
    $db->query_write("INSERT INTO `" . TABLE_PREFIX . "tagthread` (`tagid`, `threadid`, `userid`, `dateline` ) VALUES ('$tagid','$threadid','1','$dateline')");
  }

  echo 'Tag '.$i.' of '.$total.'<br />';
  $i++;
}

$i=1;

echo '<br /><br /><strong>Updating threads...</strong><br /><br />';

$query = $db->query_read("SELECT threadid FROM `" . TABLE_PREFIX . "thread`") or die(mysql_error());
$total=$db->num_rows($query);
while ($threads=$db->fetch_array($query)) 
{
  $thread=$threads['threadid'];

  $tag=$db->query_read("SELECT * FROM `" . TABLE_PREFIX . "tagthread` WHERE threadid='$thread'");
  $tagarray=array();

  while ($tags=$db->fetch_array($tag))
  {
    $tagid=$tags['tagid'];
    $tagtext=$db->query_read("SELECT * FROM `" . TABLE_PREFIX . "tag` WHERE tagid='$tagid'");

    while ($taglist=$db->fetch_array($tagtext))
    {
	  $taglisttext=$db->escape_string($taglist['tagtext']);
      array_push($tagarray,$taglisttext);
    }
  }

  $list_of_tags = implode(", ", $tagarray);
  $db->query_write("UPDATE `" . TABLE_PREFIX . "thread` SET taglist='$list_of_tags' WHERE threadid=$thread");
  $tagslist='';
  echo 'Thread '.$i.' of '.$total.'<br />';
  $i++;
}

echo '<br /><br /><strong>Done!</strong>';

?>
