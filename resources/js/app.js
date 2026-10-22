import './globals/theme.js'; /* By Sheaf.dev */
import './globals/modals.js';
import './language.js';
import './sam.js';
import './dock.js';
import './brand-mark.js';
import './brief.js';
import './os1-conversation.js';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// SheafUI components that register Alpine data (need window.Alpine from Livewire above)
import './components/calendar/index.js';
import './components/date-picker/index.js';

// now you can register
// components using Alpine.data(...) and
// plugins using Alpine.plugin(...)



Livewire.start()
