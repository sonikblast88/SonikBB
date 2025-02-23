<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo SITE_TITLE; ?></title>
    <link href="<?php echo WEBSITE; ?>/template/style.css" rel="stylesheet" type="text/css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?php echo WEBSITE_DESC; ?>">
	
	<script>
	function confirmDelete() {
		return confirm("Are you sure you want to delete?");
	}
	</script>
</head>
<body>
<div id="container">
    <div id="header">
        <img src="<?php echo WEBSITE; ?>/template/images/logo.png" alt="" />
    </div>
    <div id="navigation">
        <ul>
            <li><a href="<?php echo WEBSITE; ?>/index.php">Home</a></li> <li><a href="<?php echo WEBSITE; ?>/downloads.php">Downloads</a></li> <li><a target="_blank" rel="noopener noreferrer" href="https://sonikbb.eu/topic.php?topic_id=113&cat_id=47">About</a></li> </ul>
    </div>
    <div id="content-container">
