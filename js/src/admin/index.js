import { extend } from 'flarum/extend';
import app from 'flarum/app';

import QueueSettingsModal from './components/QueueSettingsModal';

app.initializers.add('zhishiq-queue', app => {
  app.extensionSettings['zhishiq-queue'] = () => app.modal.show(new QueueSettingsModal());
});
