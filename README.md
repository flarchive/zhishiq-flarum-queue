This repo is used as a plugin of flarum. You can install it under flarum root directory with the command: 
```bash
composer require zhishiq/flarum-queue
```

This plugin is supposed to be used as a basis by other plugins, it register illuminate/queue service provider with settings which can be set in flarum plugin backend.

This plugin

![Screenshot](screenshot.jpg?raw=true "Title")

This plugin provides two commands: `queue:listen`, `queue:work`

You can run the commands through:
```bash
php flarum queue:listen
```

This repo reuses the code in illuminate/queue, so the command `flarum queue:listen` and `flarum queue:work` are the same with `artisan queue:listen` and `artisan queue:work` 
