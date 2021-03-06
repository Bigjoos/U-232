<?php

$lang = array(

#takeupload errors
'takeupload_failed' => "Upload failed!",
'takeupload_no_formdata' => "missing form data",
'takeupload_no_filename' => "Empty filename!",
'takeupload_no_nfo' => "No NFO!",
'takeupload_0_byte' => "0-byte NFO",
'takeupload_nfo_big' => "NFO is too big! Max 65,535 bytes.",
'takeupload_nfo_failed' => "NFO upload failed",
'takeupload_no_descr' => "You must enter a description!",
'takeupload_no_cat' => "You must select a category to put the torrent in!",
'takeupload_invalid' => "Invalid filename!",
'takeupload_not_torrent' => "Invalid filename (not a .torrent).",
'takeupload_eek' => "eek",
'takeupload_no_file' => "Empty file!",
'takeupload_not_benc' => "What the hell did you upload? This is not a bencoded file!",
'takeupload_not_dict' => "not a dictionary",
'takeupload_no_keys' => "dictionary is missing key(s)",
'takeupload_invalid_entry' => "invalid entry in dictionary",
'takeupload_dict_type' => "invalid dictionary entry type",
'takeupload_unkown' => "Unknown",
'takeupload_pieces' => "invalid pieces",
'takeupload_url' => "invalid announce url! must be <b>%s</b>",
'takeupload_both' => "missing both length and files",
'takeupload_no_files' => "no files",
'takeupload_error' => "filename error",
'takeupload_already' => "torrent already uploaded!",
'takeupload_log' => "Torrent %s (%s) was uploaded by %s",
'takeupload_bucket_format' => "The image you are trying (%s) to upload is not allowed!",
'takeupload_bucket_size' => "The image is to big (%s)! max size can be ".mksize($INSTALLER09['bucket_maxsize']),
'takeupload_bucket_noimg' => "You forgot about the images!"
);

?>