<?php

  function notify ($message = '') {
    if (c::get('widget.backup.debug', false)) {
      echo '<hr><pre><h1>notify : </h1>' . $message . '</pre>';
    } else {
      panel()->notify($message);
      panel()->redirect('/');
    }
  }

  function alert ($message = '') {
    if (c::get('widget.backup.debug', false)) {
      echo '<hr><pre><h1>alert : </h1>' . $message . '</pre>';
    } else {
      panel()->alert($message);
      panel()->redirect('/');
    }
  }

  function archive ($collection, $filename = 'archive.zip') {
    $files = $collection->pluck('dirname');
    $roots = kirby()->roots();

    if (c::get('widget.backup.include_site', false)) {
      $files = array_merge($files, array_filter(
        scandir($roots->content()),
        function ($f) use ($roots) {
          return (!is_dir($roots->content() . DS . $f));
        })
      );
    }

    $files = array_map(function ($f) use ($roots) {
      return $roots->content() . DS . $f;
    }, $files);

    if (c::get('widget.backup.debug', false)) {
      echo '<pre>';
      print_r($files);
      echo '</pre>';
    }

    return zip($files, $roots->archives() . DS . $filename, true, c::get('widget.backup.overwrite', true));
  }

  // @SEE https://davidwalsh.name/create-zip-php
  function zip ($files = [], $destination = '', $recursive = true, $overwrite = false) {
    if (file_exists($destination) && !$overwrite) { return alert('could not create the archive: ' . basename($destination) . ' already exists'); }

    $files_to_zip = [];
    foreach ($files as $file) {
      if (is_file($file)) $files_to_zip[] = $file;
      else if (is_dir($file)) getDirContents($file, $files_to_zip);
    }

    if (count($files_to_zip)) {
      $zip = new ZipArchive();

      // avoid `ZIPARCHIVE::Multi-disk zip archives not supported` error
      // by forcing the $destination zip file to be on the same disk as
      // the $files to zip
      touch($destination);
      if ($errcode = $zip->open($destination, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE) !== true) {
        return alert('Could not create the archive: ZIPARCHIVE::' . zip_error_message($errcode));
      }

      foreach ($files_to_zip as $file) {
        if (is_file($file)) {
          $relative_path = str_replace(kirby()->roots()->index(), '', $file);
          $zip->addFile($file, $relative_path) or die ('ERROR: Could not add file: $file');
        }
      }

      $zip->close();

      return file_exists($destination);
    } else return alert('couldn\'t create the archive : source is empty');
  }

  function getDirContents ($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
      $path = $dir . DS . $value;
      if (!is_dir($path)) {
        $results[] = $path;
      } else if ($value != "." && $value != "..") {
        getDirContents($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }

  function zip_error_message ($code) {
    switch ($code) {
      case 0:  return 'No error';
      case 1:  return 'Multi-disk zip archives not supported';
      case 2:  return 'Renaming temporary file failed';
      case 3:  return 'Closing zip archive failed';
      case 4:  return 'Seek error';
      case 5:  return 'Read error';
      case 6:  return 'Write error';
      case 7:  return 'CRC error';
      case 8:  return 'Containing zip archive was closed';
      case 9:  return 'No such file';
      case 10: return 'File already exists';
      case 11: return 'Can\'t open file';
      case 12: return 'Failure to create temporary file';
      case 13: return 'Zlib error';
      case 14: return 'Malloc failure';
      case 15: return 'Entry has been changed';
      case 16: return 'Compression method not supported';
      case 17: return 'Premature EOF';
      case 18: return 'Invalid argument';
      case 19: return 'Not a zip archive';
      case 20: return 'Internal error';
      case 21: return 'Zip archive inconsistent';
      case 22: return 'Can\'t remove file';
      case 23: return 'Entry has been deleted';
      default: return 'An unknown error has occurred('.intval($code).')';
    }
  }
