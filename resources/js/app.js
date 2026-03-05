import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Mask from '@alpinejs/mask';

// Swiper
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/free-mode';

window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination, Autoplay, FreeMode };

Alpine.plugin(Mask);

Livewire.start();
