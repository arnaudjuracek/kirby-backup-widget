<?

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
    $files = $collection->count() > 1 ? $collection->pluck('dirname') : [$collection->dirname()];
    if (c::get('widget.backup.include_site', false)) $files[] = 'site.txt';

    $files = array_map(function ($f) {
      return kirby()->roots()->content() . DS . $f;
    }, $files);

    if (c::get('widget.backup.debug', false)) {
      echo '<pre>';
      print_r($files);
      echo '</pre>';
    }

    return zip($files, kirby()->roots()->archives() . DS . $filename, true, c::get('widget.backup.overwrite', true));
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
      if ($errcode = $zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
        return alert('Could not create the archive: ZIPARCHIVE::' . $errcode);
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
