<?php
  include __DIR__ . DS . 'kirby-backup-widget.helpers.php';

  $archives = site()->find(c::get('widget.backup.destination', 'backups'))->archives()->flip();

  if (get('action') == 'archive') {
    $filename = date(c::get('widget.backup.date_format', 'Y-m-d')) . '.zip';

    $collection = ($include = c::get('widget.backup.include', false))
              ? site()->find($include)
              : site()->children()->not(c::get('widget.backup.destination', 'backups'), c::get('widget.backup.exclude', ''));

    $result = archive($collection, $filename);
    if ($result) {
      notify($filename . ' created');
    }
  } else if (get('action') == 'delete') {
    $file = $archives->find(get('archive'));

    if ($file && $file->exists()) {
      $file->delete();
      alert($file->filename() . ' deleted');
    }
  }
?>

<?php if ($archives->count()) : ?>
  <div class="dashboard-box">
    <ul class="dashboard-items">
      <?php foreach ($archives as $archive) : ?>
        <li class="dashboard-item">
          <figure>
            <a title="download <?php echo $archive->filename() ?>" href="<?php echo $archive->url() ?>">
              <span class="dashboard-item-icon dashboard-item-icon-with-border"><i class="fa fa-download ?>"></i></span>
            </a>
            <a title="delete <?php echo $archive->filename() ?>" href="?action=delete&archive=<?php echo $archive->filename() ?>">
              <span class="dashboard-item-icon dashboard-item-icon-with-border"><i class="fa fa-trash ?>"></i></span>
            </a>
            <figcaption class="dashboard-item-text"><?php echo $archive->filename() ?> (<?php echo $archive->niceSize() ?>)</figcaption>
          </figure>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
<?php else : ?>
  No backup created yet.
  <a href="?action=archive"><b>Backup now</b></a>.
<?php endif ?>
