import app from 'flarum/forum/app';
import extendReplyComposer from './extenders/extendReplyComposer';

app.initializers.add('fof-prevent-necrobumping', () => {
  extendReplyComposer();
});
