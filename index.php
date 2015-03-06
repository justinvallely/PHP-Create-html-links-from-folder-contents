<?php
        $results = array();
        $dir = "./files";
        $handler = opendir($dir);

        while ($file = readdir($handler))
        {
                if ($file != '.' && $file != '..' && !is_dir($file))
                $results[] = $file;
        }

        closedir ($handler);
        $columns=1;
        $curCol=-1;
        echo "<table align=center cellpadding=0 cellspacing=0 border=0><tr>";
        foreach ($results as $file)
        {
                if (++$curCol == $columns) {
                        echo "</tr><tr>\r";
                        $curCol = 0;
                }
                if (stristr($file, "zzz")==true) {
                        echo "<td width=500 align=center></td></tr><tr>\r";
                }
                else if (stristr($file, "jpg")==false) {
                        echo "<td width=500 align=center><a href=$dir/$file>$file</a></td></tr><tr>\r";
                }
                if (stristr($file, "jpg")==true) {
                        echo "<td width=500 align=center><a href=$dir/$file><img src=$dir/$file width=100></a></td>\r";
                }
                else {
                        $curCol--;
                }
        }

        echo "<tr></table>";
?>