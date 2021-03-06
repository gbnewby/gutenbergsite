<?php

set_include_path(get_include_path() . PATH_SEPARATOR . "/public/vhost/g/gutenberg/dev/private/lib/php");
include_once ("pgcat.phh");
include_once ("sqlform.phh");
include_once ("mn_relation.phh");

$db = $config->db ();
$db->logger = new logger ();

getint ("fk_books");
getint ("fk_authors");
getstr ("fk_roles");
getint ("heading");
$sql_fk_roles = $db->f ($fk_roles, SQLCHAR);

$caption = MNCaption ("Author", "Book");

$f->KeySelect         ("fk_roles", "fk_roles", "Author Role", SQLCHAR, 40, 40, true);
$f->last->LoadSQL     ("select pk as key, role as caption from roles order by role");
$f->last->DefValue    ("cr");
$f->last->ToolTip     ("In which role did this author contribute to the book?");

$f->KeySelect         ("heading",  "heading",  "Heading", SQLINT,  10,  2, true);
$f->last->PushOptions ($titles_heading);
$f->last->DefValue    (1);
$f->last->ToolTip     ("Should this author generate a user-visible heading?");

$f->LoadData  ("select * from mn_books_authors " . 
               "where fk_books = $fk_books and fk_authors = $fk_authors and fk_roles = $sql_fk_roles");

if (ismode ("delete")) {
  $f->SubCaption ("You are about to unlink this book author. " . 
                  "Press the '$caption' button to continue or " . 
		  "hit the back button on your browser to dismiss.");
}

$f->Hidden ("fk_books");
$f->Hidden ("fk_authors");

if (isupdatemode ("add")) {
  if ($f->Check ()) {
    if ($db->Exec ("insert into mn_books_authors (fk_books, fk_authors, fk_roles, heading) " . 
		   "values ($fk_books, $fk_authors, $sql_fk_roles, $heading)")) {
      $msg = "msg=Book author linked !";
    } else {
      $msg = "errormsg=Could not link book author !";
    }
  }
}
if (isupdatemode ("edit")) {
  if ($f->Check ()) {
    if ($db->Exec ("update mn_books_authors " . 
		   "set fk_roles = $sql_fk_roles, heading = $heading " . 
		   "where fk_books = $fk_books and fk_authors = $fk_authors")) {
      $msg = "msg=Author link modified !";
    } else {
      $msg = "errormsg=Could not modify author link !";
    }
  }
}
if (isupdatemode ("delete")) {
  if ($db->Exec ("delete from mn_books_authors " . 
		 "where fk_books = $fk_books and fk_authors = $fk_authors and fk_roles = $sql_fk_roles")) {
    $msg = "msg=Book author unlinked !";
  } else {
    $msg = "errormsg=Could not unlink book author !";
  }
}

if (isupdate ()) {
  header ("Location: book?mode=edit&fk_books=$fk_books&$msg");
  return;
}

pageheader ($caption);
$f->Output ($caption, $caption);
pagefooter ();

?>
