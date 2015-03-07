<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <meta charset="utf-8">
</head>
<body>
<?php
    $results = array();             // Initialize an array to store list of files
    $dir = "./files/";              // Directory to look for files
    $thumbsdir = "./files/thumbs/"; // Directory to look for image thumbnails
    $tempdir = "./files/temp/";     // Temporary directory to hold extracted files/folders
    $handler = opendir($dir);       // Opens up a directory handle to be used in subsequent closedir(), readdir(), and rewinddir() calls.
    
    function create_thumbnail($file, $outputName){
    // Create thumbnails on the fly. 
    // args: (file to be processed, output file name)
        
        global $dir, $thumbsdir;    // Access global directory variables
        $thumbWidth = 150;
        $thumbHeight = 100;
        
        $img = imagecreatefromjpeg("{$file}");    // Load image for processing
        //echo "Loading image $file <br />";
        $imgWidth = imagesx($img);                // Get image width
        $imgHeight = imagesy($img);               // Get image height
        //echo "Image width = $imgWidth <br />";
        //echo "Image height = $imgHeight <br />";
        
        // Calculate thumbnail size based on long edge to keep same aspect ratio as original
        if ($imgWidth > $imgHeight) {
            //echo "Image width > height <br />";
            $thumbHeight = floor($imgHeight * ($thumbWidth / $imgWidth));
        } else {
            //echo "Image height >= width <br />";
            $thumbWidth = floor($imgWidth * ($thumbHeight / $imgHeight));
        }
        
        // Create a thumbnail template
        $thumbTemplate = imagecreatetruecolor($thumbWidth, $thumbHeight);
        //echo "Thumbnail template created. <br />";
        
        // Copy and resize original image into the thumbnail template
        imagecopyresized($thumbTemplate, $img, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
        //echo "Original image copied and resized. <br />";
        
        // Save thumbnail into a new directory at quality 100/100.
        if (!is_dir("{$thumbsdir}")) {
            mkdir("{$thumbsdir}");
        }
        imagejpeg($thumbTemplate, "{$thumbsdir}{$outputName}", 100);
        //echo "Thumbnail saved to {$thumbsdir}{$outputName} <br />";
        
        // Release thumbnail template from memory
        imagedestroy($thumbTemplate);
        //echo "Thumbnail template destroyed. <br />";
        
        // Verify
        if (file_exists("{$thumbsdir}{$outputName}")) {
            echo "Thumbnail created: {$outputName} <br />";
        } else {
            echo "Error creating thumbnail: {$outputName} <br />";
        }
    }
    
    // Recursive remove a directory and its contents
    function rrmdir($dir) { 
        foreach(glob($dir . '/*') as $file) { 
            if(is_dir($file)) rrmdir($file); else unlink($file); 
        }
        rmdir($dir); 
    }
    
    // Create a temporary working directory if one doesn't exist.
    if (!is_dir("{$tempdir}")) {
        mkdir("{$tempdir}", 0777, true);
    }
    
    // Loop over the open directory and add the entries to the results array
    // Create image thumbnails for jpgs and zip files to display on page
    while (false !== ($file = readdir($handler))) {
        // Ignore the ".", "..", any directories, and the "Thumbs.db" file
        if ($file != '.' && $file != '..' && !is_dir("$dir" . "$file") && $file != 'Thumbs.db') {
            $results[] = $file; // Append the entry to the results array
            
            if (stristr($file, ".zip")) {
                // Create a string of the file's name with extension changed from zip to jpg
                $newFileName = stristr($file, '.zip', true) . ".jpg";
                //echo "New file name string is $newFileName <br />";
            }
            
            // If the file is a ZIP and a thumbnail hasn't been created yet...
            if (stristr($file, ".zip") == true && !file_exists("{$thumbsdir}{$newFileName}")) {
                //echo "Opening zip file: $file <br />";
                $zip = new ZipArchive;  // Create a new ZipArchive object
                if ($zip->open("{$dir}{$file}") === true) {         // If the zip file is successfully opened
                    for($i = 0; $i < $zip->numFiles; $i++) {        // Iterate through the entire list of files
                        if (stristr($zip->getNameIndex($i), ".jpg") == true) {              // Only process the first JPG file
                            $zip->extractTo("{$tempdir}", array($zip->getNameIndex($i)));   // Extract file to the temp directory
                            $extractedName = $zip->getNameIndex($i);                        // Get the extracted file name
                            create_thumbnail("$tempdir$extractedName", $newFileName);  // Create 150px wide thumbnail of the extracted image file
                            break 1;    // Break out of -If- loop and -For- loop
                        }
                    }
                    $zip->close();
                } else {
                    echo "ERROR: File didn't zip open";
                }
            }
            
            // If the file is a JPG and a thumbnail hasn't been created yet...
            if (stristr($file, ".jpg") == true && !file_exists("{$thumbsdir}{$file}")) {
                //echo "File does not exist: {$thumbsdir}{$file} <br />";
                create_thumbnail("$dir$file", $file);    // Create 150px wide thumbnail of the JPG image
            }
        }
    }
    
    // Clean up
    closedir ($handler);                            // Close the directory
    rrmdir($tempdir);                               // Delete the temp directory and all its files
    sort($results, SORT_NATURAL | SORT_FLAG_CASE);  // Sort the array using case-insensitive natural ordering

    // Generate HTML
    echo "<table class=\"files\">";
    foreach ($results as $file) {
        if (stristr($file, ".zip") == true) {   // Display files with a .zip extension
            $newFileName = $thumbsdir . stristr($file, '.zip', true) . ".jpg";    //create a string of the file's name with extension changed from zip to jpg
            echo "<tr><td width=150px align=center><a href=$dir$file class=\"phone-numb\"><img src=\"$newFileName\"/></a></td>
                      <td width=100% align=left><a href=$dir$file class=\"phone-numb\"><span>$file</span></a></td></tr>\r";
        }
        if (stristr($file, ".jpg") == true) {   // Display files with a .jpg extension. Use a thumbnail created above, but link to the original file.
            echo "<tr><td width=150px align=center><a href=$dir$file class=\"phone-numb\"><img src=\"$thumbsdir$file\"/></a></td>
                      <td width=100% align=left><a href=$dir$file class=\"phone-numb\"><span>$file</span></a></td></tr>\r";
        }
    }
    echo "</table>";
?>
</body>
</html>