<?
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

<? if ($archives->count()) : ?>
  <ul class="dashboard-items dashboard-box">
    <? foreach ($archives as $archive) : ?>
      <li class="dashboard-item">
        <figure>
          <a title="download <?= $archive->filename() ?>" href="<?= $archive->url() ?>">
            <span class="dashboard-item-icon dashboard-item-icon-with-border"><i class="fa fa-download ?>"></i></span>
          </a>
          <a title="delete <?= $archive->filename() ?>" href="?action=delete&archive=<?= $archive->filename() ?>">
            <span class="dashboard-item-icon dashboard-item-icon-with-border"><i class="fa fa-trash ?>"></i></span>
          </a>
          <figcaption class="dashboard-item-text"><?= $archive->filename() ?> (<?= $archive->niceSize() ?>)</figcaption>
        </figure>
      </li>
    <? endforeach ?>
  </ul>
<? else : ?>
  No backup created yet.
  <a href="?action=archive"><b>Backup now</b></a>.
<? endif ?>
