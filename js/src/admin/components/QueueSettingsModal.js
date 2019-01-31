import SettingsModal from 'flarum/components/SettingsModal';

export default class QueueSettingsModal extends SettingsModal {
  className() {
    return 'QueueSettingsModal Modal--small';
  }

  title() {
    return app.translator.trans('zhishiq.queue.admin.setting_title');
  }

  form() {
    return [
      <div className="Form-group">
        <label>{app.translator.trans('zhishiq.queue.admin.queue_backend')}</label>
        <input className="FormControl" bidi={this.setting('zhishiq.queue.backend')}/>
      </div>,

    ];
  }
}
