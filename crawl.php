<?php
include("config.php");
include("classes/DomDocumentParser.php");

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();

function linkExists($url) {
   global $con;

   $query = $con->prepare("SELECT * FROM sites WHERE url=?");
	$query->execute([$url]);
	return $query->rowCount() != 0;
}

function insertLink($url, $title, $description, $keywords) {
   global $con;

   $query = $con->prepare("INSERT INTO sites SET url=?, title=?, description=?, keywords=?");
   return $query->execute([$url, $title, $description, $keywords]);
}

function insertImage($url, $src, $alt, $title) {
	global $con;
	
	$query2 = $con->prepare("SELECT * FROM images WHERE imageUrl=?");
	$query2->execute([$src]);
	$num2 = $query2->rowCount();

	if ($num2==0) {
		$query = $con->prepare("INSERT INTO images SET siteUrl=?, imageUrl=?, alt=?, title=?");
   	$query->execute([$url, $src, $alt, $title]);
	} else {
		$query = $con->prepare("UPDATE images SET alt=?, title=? WHERE imageUrl=?");
   	$query->execute([$alt, $title, $src]);
	}

   
}

function createLink($src, $url) {
	$scheme = parse_url($url)["scheme"]; // http
	$host = parse_url($url)["host"]; // www.reecekenney.com
	
	if(substr($src, 0, 2) == "//") {
		$src =  $scheme . ":" . $src;
	}
	else if(substr($src, 0, 1) == "/") {
		$src = $scheme . "://" . $host . $src;
	}
	else if(substr($src, 0, 2) == "./") {
		$src = $scheme . "://" . $host . dirname(parse_url($url)["path"]) . substr($src, 1);
	}
	else if(substr($src, 0, 3) == "../") {
		$src = $scheme . "://" . $host . "/" . $src;
	}
	else if(substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http") {
		$src = $scheme . "://" . $host . "/" . $src;
	}
	return $src;
}

function getDetails($url) {

	global $alreadyFoundImages;

	$parser = new DomDocumentParser($url);

	$titleArray = $parser->getTitleTags();

	if(sizeof($titleArray) == 0 || $titleArray->item(0) == NULL) {
		return;
	}

	$title = $titleArray->item(0)->nodeValue;
	$title = str_replace("\n", "", $title);

	if($title == "") {
		return;
   }
   
   $description = "";
   $keywords = "";

   $metasArray = $parser->getMetaTags();

   foreach ($metasArray as $meta) {
      if ($meta->getAttribute("name")=="description") {
         $description = $meta->getAttribute("content");
      }
      if ($meta->getAttribute("name")=="keywords") {
         $keywords = $meta->getAttribute("content");
      }
   }
   $description = str_replace("\n", "", $description);
   $keywords = str_replace("\n", "", $keywords);

	if (linkExists($url)) {
		//echo "$url already exist";
	} elseif (insertLink($url, $title, $description, $keywords)) {
		//echo "link eklendi";
	} else {
		//echo "error";
	}

	$imageArray = $parser->getImages();
	foreach ($imageArray as $image) {
		$src=$image->getAttribute("src");
		$title=$image->getAttribute("title");
		$alt=$image->getAttribute("alt");

		if (!$title && !$alt) {
			continue;
		}
		$src = createLink($src, $url);
		if (!in_array($src, $alreadyFoundImages)) {
			$alreadyFoundImages[] = $src;
			insertImage($url, $src, $alt, $title);
		}
	}

}

function followLinks($url) {

	global $alreadyCrawled;
	global $crawling;

	$parser = new DomDocumentParser($url);

	$linkList = $parser->getLinks();

	foreach($linkList as $link) {
		$href = $link->getAttribute("href");

		if(strpos($href, "#") !== false) {
			continue;
		}
		else if(substr($href, 0, 11) == "javascript:") {
			continue;
		}


		$href = createLink($href, $url);


		if(!in_array($href, $alreadyCrawled)) {
			$alreadyCrawled[] = $href;
			$crawling[] = $href;

			getDetails($href);
		}
		else return;


	}

	array_shift($crawling);

	foreach($crawling as $site) {
		followLinks($site);
	}

}

$startUrl = "https://tr.wikipedia.org/wiki/Azer_B%C3%BClb%C3%BCl";
followLinks($startUrl);
?>