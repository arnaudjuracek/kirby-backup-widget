<?php

  // create and register `content/$destination`
  $kirby = kirby();
  $kirby->roots->archives = $kirby->roots()->content() . DS . c::get('widget.backup.destination', 'backups');
  if (!is_dir($kirby->roots()->archives())) mkdir($kirby->roots()->archives(), 0777);

  return [
    'title' => [
      'text' => 'Backup your files',
      'compressed' => false,
    ],
    'options' => [
      [
        'text' => 'Backup now',
        'icon' => 'floppy-o',
        // TODO: proper routing
        'link' => '?action=archive',
      ]
    ],

    'html' => function() {
      return tpl::load(__DIR__ . DS . 'kirby-backup-widget.html.php');
    }
  ];
